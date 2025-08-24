<?php

namespace app\modules\equeue\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\db\Transaction;
use app\models\Users;
use app\models\Counters;
use app\models\ServiceUser;
use app\models\Queues;
use app\modules\equeue\models\Service;

class NextController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        // Disable CSRF validation for this controller's actions
        // as it's likely called via AJAX/API.
        return [
            'csrf' => [
                'class' => \yii\web\Request::class,
                'enableCsrfValidation' => false,
            ],
        ];
    }

    /**
     * Calls the next person in the queue.
     * This is an API endpoint for bank operators.
     * @return array JSON response
     */
    public function actionCallNext()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $userId = Yii::$app->user->id;
            $user = Users::getUserData($userId);

            if (!$user) {
                // Return an error if the user is not found
                return ['success' => false, 'message' => 'Foydalanuvchi topilmadi'];
            }

            // 1. More efficient and cleaner counter selection
            $requestedCounterId = Yii::$app->request->post('counter_id', Yii::$app->request->get('counter_id'));

            $counterQuery = Counters::find()
                ->where([
                    'branch_id' => $user->branch_id,
                    'user_id'   => $user->id,
                    'status'    => 'active',
                ]);

            if ($requestedCounterId) {
                $counterQuery->andWhere(['id' => $requestedCounterId]);
            }

            $chosenCounter = $counterQuery->one(); // Fetching one record is more efficient

            if (!$chosenCounter) {
                $message = $requestedCounterId
                    ? 'Ushbu counter sizga tegishli emas yoki faol emas' // This counter does not belong to you or is not active
                    : 'Faol counterlar topilmadi'; // Active counters not found
                return ['success' => false, 'message' => $message];
            }

            // 2. Get user's services
            $serviceIds = ServiceUser::find()
                ->select('service_id')
                ->where(['user_id' => $user->id])
                ->column();

            if (empty($serviceIds)) {
                return ['success' => false, 'message' => 'Foydalanuvchiga biriktirilgan xizmatlar topilmadi'];
            }

            // 3. Find and update the next queue within a transaction
            $nextQueueData = null;

            // Using REPEATABLE READ is often a better balance of performance and safety for this use case.
            $transaction = Yii::$app->db->beginTransaction(Transaction::REPEATABLE_READ);

            try {
                $supportsForUpdate = in_array(Yii::$app->db->driverName, ['mysql', 'pgsql']);

                // The main query to find the next person in the queue
                $query = Queues::find()
                    ->alias('q')
                    // Note: The original code had a comment here. Please verify this model's namespace and table name.
                    ->innerJoin(['s' => \app\modules\equeue\models\Service::tableName()], 's.id = q.service_id')
                    ->where([
                        'q.branch_id' => $user->branch_id,
                        'q.status'    => Queues::STATUS_WAITING,
                    ])
                    ->andWhere(['q.counter_id' => null])
                    ->andWhere(['in', 'q.service_id', $serviceIds]) // Using 'in' is more explicit and readable
                    ->orderBy([
                        's.priority'   => SORT_DESC,
                        'q.created_at' => SORT_ASC,
                        'q.id'         => SORT_ASC,
                    ]);

                // 4. Use pessimistic locking ('FOR UPDATE') to prevent race conditions
                if ($supportsForUpdate) {
                    $query->limit(1)->forUpdate();
                }

                $nextQueue = $query->one();

                if ($nextQueue) {
                    // 5. Update the queue record
                    $nextQueue->status     = Queues::STATUS_CALLED;
                    // Use a DB expression for the current time to avoid server timezone issues
                    $nextQueue->called_at  = new \yii\db\Expression('NOW()');
                    $nextQueue->counter_id = $chosenCounter->id;

                    if ($nextQueue->save()) {
                        $transaction->commit();

                        // 6. Prepare the successful response
                        $nextQueueData = [
                            'success'     => true,
                            'nextNumber'  => $nextQueue->queue_number,
                            'message'     => "Navbat raqami chaqirildi: {$nextQueue->queue_number}",
                            'counter_id'  => $chosenCounter->id,
                            'service_id'  => $nextQueue->service_id,
                            'queue_id'    => $nextQueue->id,
                        ];
                    } else {
                        $transaction->rollBack();
                        // Return validation errors for easier debugging
                        return [
                            'success' => false,
                            'message' => 'Maʼlumotni yangilashda xatolik yuz berdi',
                            'errors'  => $nextQueue->errors,
                        ];
                    }
                } else {
                    // No one is waiting in the queue
                    $transaction->rollBack();
                }

            } catch (\Throwable $e) {
                if ($transaction->isActive) {
                    $transaction->rollBack();
                }
                // Re-throw the exception to be caught by the outer catch block for logging
                throw $e;
            }

            if ($nextQueueData) {
                return $nextQueueData;
            } else {
                return ['success' => false, 'message' => 'Mos kutayotgan navbat topilmadi'];
            }

        } catch (\Throwable $e) {
            Yii::error([
                'msg'   => 'actionCallNext failed',
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ], __METHOD__);

            return [
                'success' => false,
                'message' => YII_DEBUG ? 'Xatolik: ' . $e->getMessage() : 'Kutilmagan xatolik yuz berdi',
            ];
        }
    }
}
