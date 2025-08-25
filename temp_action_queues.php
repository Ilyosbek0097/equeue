<?php

// This is the updated version of the actionQueues function.
// It now includes a list of all services for the redirect feature.
// Please copy the code of this function and replace your existing actionQueues function with it.

public function actionQueues()
{
    $userId = Yii::$app->user->id;
    $user = \app\modules\equeue\models\Users::getUserData($userId);

    $counterOne = \app\modules\equeue\models\Counters::find()->where(['user_id' => $user->id, 'status' => 'active'])->one();
    if (!$counterOne) {
        throw new \yii\web\NotFoundHttpException('Sizga biriktirilgan faol oyna topilmadi.');
    }

    // Find services specifically assigned to THIS operator
    $services = \app\modules\equeue\models\Service::find()
        ->alias('s')
        ->innerJoin('service_user su', 'su.service_id = s.id')
        ->where(['s.status' => 1, 'su.user_id' => $user->id])
        ->all();

    // Correctly count ONLY queues with 'waiting' status
    $queueCounts = \app\modules\equeue\models\Queues::find()
        ->select(['service_id', 'COUNT(*) AS count'])
        ->where([
            'branch_id' => $user->branch_id,
            'status' => \app\modules\equeue\models\Queues::STATUS_WAITING,
        ])
        ->groupBy('service_id')
        ->indexBy('service_id')
        ->asArray()
        ->all();

    // Find the operator's currently active queue
    $activeCall = \app\modules\equeue\models\CounterCalls::find()
        ->alias('cc')
        ->innerJoinWith('queue q', false)
        ->where(['cc.user_id' => $userId, 'q.status' => \app\modules\equeue\models\Queues::STATUS_CALLED])
        ->one();

    // NEW: Fetch all active services in the branch for the redirect modal
    $allServices = \app\modules\equeue\models\Service::find()
        ->where(['status' => 1, 'branch_id' => $user->branch_id])
        ->orderBy('name ASC')
        ->all();

    return $this->render('operator', [
        'counterOne' => $counterOne,
        'services' => $services, // Operator's assigned services
        'queueCounts' => $queueCounts,
        'activeCall' => $activeCall,
        'allServices' => $allServices, // All services for the redirect modal
    ]);
}
