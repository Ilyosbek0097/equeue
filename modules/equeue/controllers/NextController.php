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
use app\models\CounterCalls; // Added this model

class NextController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'csrf' => [
                'class' => \yii\web\Request::class,
                'enableCsrfValidation' => false,
            ],
        ];
    }

    /**
     * Calls the next person in the queue based on service priority.
     * This is an API endpoint for bank operators.
     * @return array JSON response
     */
    public function actionCallNext()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $transaction = Yii::$app->db->beginTransaction(Transaction::REPEATABLE_READ);

        try {
            $userId = Yii::$app->user->id;
            $user = Users::getUserData($userId);
            if (!$user) {
                throw new \yii\web\NotFoundHttpException('Foydalanuvchi topilmadi.');
            }

            // 1. Determine the counter for this user
            $requestedCounterId = Yii::$app->request->post('counter_id', Yii::$app->request->get('counter_id'));
            $counterQuery = Counters::find()->where([
                'branch_id' => $user->branch_id,
                'user_id'   => $user->id,
                'status'    => 'active',
            ]);
            if ($requestedCounterId) {
                $counterQuery->andWhere(['id' => $requestedCounterId]);
            }
            $chosenCounter = $counterQuery->one();

            if (!$chosenCounter) {
                $message = $requestedCounterId ? 'Ushbu counter sizga tegishli emas yoki faol emas' : 'Faol counterlar topilmadi';
                return ['success' => false, 'message' => $message];
            }

            // 2. Get services this user can perform
            $serviceIds = ServiceUser::find()
                ->select('service_id')
                ->where(['user_id' => $user->id])
                ->column();

            if (empty($serviceIds)) {
                return ['success' => false, 'message' => 'Foydalanuvchiga biriktirilgan xizmatlar topilmadi'];
            }

            // 3. Find the highest priority person in the queue
            $query = Queues::find()
                ->alias('q')
                ->innerJoin(['s' => Service::tableName()], 's.id = q.service_id')
                ->where([
                    'q.branch_id' => $user->branch_id,
                    'q.status'    => Queues::STATUS_WAITING,
                ])
                ->andWhere(['in', 'q.service_id', $serviceIds])
                ->orderBy([
                    's.priority'   => SORT_DESC,
                    'q.created_at' => SORT_ASC,
                    'q.id'         => SORT_ASC,
                ]);

            // 4. Lock the row to prevent race conditions (backward-compatible)
            $nextQueue = null;
            $supportsForUpdate = in_array(Yii::$app->db->driverName, ['mysql', 'pgsql']);
            if ($supportsForUpdate) {
                $rawSql = $query->limit(1)->createCommand()->getRawSql();
                $nextQueue = Queues::findBySql($rawSql . ' FOR UPDATE')->one();
            } else {
                $nextQueue = $query->limit(1)->one();
            }

            if (!$nextQueue) {
                $transaction->rollBack();
                return ['success' => false, 'message' => 'Mos kutayotgan navbat topilmadi'];
            }

            // 5. Update queue status and create a counter_calls record
            $now = new \yii\db\Expression('NOW()');

            $nextQueue->status = Queues::STATUS_CALLED;
            $nextQueue->called_at = $now;

            if (!$nextQueue->save()) {
                throw new \yii\base\Exception('Navbat holatini yangilab bo‘lmadi.');
            }

            $counterCall = new CounterCalls();
            $counterCall->counter_id = $chosenCounter->id;
            $counterCall->queue_id = $nextQueue->id;
            $counterCall->branch_id = $user->branch_id;
            $counterCall->called_at = $now;

            if (!$counterCall->save()) {
                throw new \yii\base\Exception('Chaqiruvni qayd etib bo‘lmadi.');
            }

            $transaction->commit();

            return [
                'success'     => true,
                'nextNumber'  => $nextQueue->queue_number,
                'message'     => "Navbat raqami chaqirildi: {$nextQueue->queue_number}",
                'counter_id'  => $chosenCounter->id,
                'queue_id'    => $nextQueue->id,
            ];

        } catch (\Throwable $e) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            Yii::error($e->getMessage(), __METHOD__);
            return [
                'success' => false,
                'message' => YII_DEBUG ? $e->getMessage() : 'Kutilmagan xatolik yuz berdi.',
            ];
        }
    }
}
