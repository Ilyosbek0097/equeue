<?php

namespace app\modules\equeue\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\db\Transaction;
use app\modules\equeue\models\Users;
use app\modules\equeue\models\Counters;
use app\modules\equeue\models\ServiceUser;
use app\modules\equeue\models\Queues;
use app\modules\equeue\models\Service;
use app\modules\equeue\models\CounterCalls;

class NextController extends Controller
{
    public $enableCsrfValidation = false; // Simple way to disable for all actions in this API controller

    public function behaviors()
    {
        return [
            'response' => [
                'class' => 'yii\filters\ContentNegotiator',
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ];
    }

    /**
     * Calls the next person in the queue or returns the current active queue.
     */
    public function actionCallNext()
    {
        $userId = Yii::$app->user->id;
        $user = Users::getUserData($userId);
        if (!$user) {
            throw new \yii\web\NotFoundHttpException('Foydalanuvchi topilmadi.');
        }

        // First, check if this operator already has an active, unfinished queue
        $activeCall = CounterCalls::find()
            ->alias('cc')
            ->innerJoinWith('queue q', false) // Use `false` for lazy load, relation is enough
            ->where(['cc.user_id' => $userId, 'q.status' => Queues::STATUS_CALLED])
            ->one();

        if ($activeCall) {
            return [
                'success' => true,
                'already_active' => true,
                'message' => 'Sizda allaqachon faol navbat mavjud: ' . $activeCall->queue->queue_number,
                'nextNumber' => $activeCall->queue->queue_number,
                'queue_id' => $activeCall->queue_id,
                'service_id' => $activeCall->queue->service_id,
            ];
        }

        $transaction = Yii::$app->db->beginTransaction(Transaction::REPEATABLE_READ);
        try {
            // Determine the counter
            $chosenCounter = Counters::find()->where(['user_id' => $userId, 'status' => 'active'])->one();
            if (!$chosenCounter) {
                throw new \yii\web\HttpException(403, 'Sizga biriktirilgan faol oyna topilmadi.');
            }

            // Get services this user can perform
            $serviceIds = ServiceUser::find()->select('service_id')->where(['user_id' => $user->id])->column();
            if (empty($serviceIds)) {
                throw new \yii\web\HttpException(403, 'Foydalanuvchiga biriktirilgan xizmatlar topilmadi.');
            }

            // Find the highest priority person in the queue
            $query = Queues::find()
                ->alias('q')
                ->innerJoinWith('service s', false)
                ->where(['q.branch_id' => $user->branch_id, 'q.status' => Queues::STATUS_WAITING])
                ->andWhere(['in', 'q.service_id', $serviceIds])
                ->orderBy(['s.priority' => SORT_DESC, 'q.created_at' => SORT_ASC, 'q.id' => SORT_ASC]);

            // Lock the row to prevent race conditions
            $rawSql = $query->limit(1)->createCommand()->getRawSql();
            $nextQueue = Queues::findBySql($rawSql . ' FOR UPDATE')->one();

            if (!$nextQueue) {
                $transaction->rollBack();
                return ['success' => false, 'message' => 'Mos kutayotgan navbat topilmadi'];
            }

            // Update queue status and create a counter_calls record
            $now = new \yii\db\Expression('NOW()');
            $nextQueue->status = Queues::STATUS_CALLED;
            $nextQueue->called_at = $now;
            if (!$nextQueue->save()) {
                throw new \yii\base\Exception('Navbat holatini yangilab bo‘lmadi: ' . json_encode($nextQueue->errors));
            }

            $counterCall = new CounterCalls();
            $counterCall->user_id = $userId; // Save the user_id
            $counterCall->counter_id = $chosenCounter->id;
            $counterCall->queue_id = $nextQueue->id;
            $counterCall->branch_id = $user->branch_id;
            $counterCall->called_at = $now;
            if (!$counterCall->save()) {
                throw new \yii\base\Exception('Chaqiruvni qayd etib bo‘lmadi: ' . json_encode($counterCall->errors));
            }

            $transaction->commit();

            return [
                'success' => true,
                'already_active' => false,
                'nextNumber' => $nextQueue->queue_number,
                'message' => "Navbat raqami chaqirildi: {$nextQueue->queue_number}",
                'queue_id' => $nextQueue->id,
                'service_id' => $nextQueue->service_id,
            ];

        } catch (\Throwable $e) {
            if ($transaction->isActive) $transaction->rollBack();
            Yii::error($e->getMessage(), __METHOD__);
            return ['success' => false, 'message' => YII_DEBUG ? $e->getMessage() : 'Kutilmagan xatolik yuz berdi.'];
        }
    }

    /**
     * Updates the status of an active queue (e.g., to 'served' or 'cancelled').
     */
    public function actionUpdateStatus()
    {
        $userId = Yii::$app->user->id;
        $queue_id = (int)Yii::$app->request->post('queue_id');
        $status = Yii::$app->request->post('status');

        if (!$queue_id || !in_array($status, ['served', 'cancelled'])) {
            throw new \yii\web\BadRequestHttpException('Kerakli parametrlar noto‘g‘ri yoki mavjud emas.');
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Find the call record assigned to this user
            $call = CounterCalls::find()->where(['queue_id' => $queue_id, 'user_id' => $userId])->one();
            if (!$call) {
                throw new \yii\web\NotFoundHttpException('Bu navbat sizga biriktirilmagan yoki topilmadi.');
            }

            $queue = $call->getQueue()->one(); // Use relation to get queue
            if ($queue->status !== Queues::STATUS_CALLED) {
                 throw new \yii\web\ConflictHttpException('Ushbu navbatning holatini o\'zgartirib bo\'lmaydi.');
            }

            if ($status === 'served') {
                $queue->status = Queues::STATUS_SERVED;
                $call->served_at = new \yii\db\Expression('NOW()');
            } elseif ($status === 'cancelled') {
                $queue->status = Queues::STATUS_CANCELLED;
            }

            if (!$queue->save()) {
                 throw new \yii\base\Exception('Navbat holatini saqlashda xatolik: ' . json_encode($queue->errors));
            }
            if (!$call->save()) {
                 throw new \yii\base\Exception('Chaqiruv holatini saqlashda xatolik: ' . json_encode($call->errors));
            }

            $transaction->commit();
            return ['success' => true, 'message' => 'Status muvaffaqiyatli yangilandi.'];

        } catch (\Throwable $e) {
            if ($transaction->isActive) $transaction->rollBack();
            Yii::error($e->getMessage(), __METHOD__);
            return ['success' => false, 'message' => YII_DEBUG ? $e->getMessage() : 'Statusni yangilashda xatolik yuz berdi.'];
        }
    }
}
