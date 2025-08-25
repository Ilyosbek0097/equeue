<?php

// This is the corrected and final version of the actionQueues function.
// Please copy the code of this function and replace your existing actionQueues function with it.

public function actionQueues()
{
    // Get the current user ID and user data
    $userId = Yii::$app->user->id;
    $user = \app\modules\equeue\models\Users::getUserData($userId);

    // Find the operator's single active counter
    $counterOne = \app\modules\equeue\models\Counters::find()->where(['user_id' => $user->id, 'status' => 'active'])->one();
    if (!$counterOne) {
        throw new \yii\web\NotFoundHttpException('Sizga biriktirilgan faol oyna topilmadi.');
    }

    // Find all services assigned to this operator
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

    // Find the operator's currently active queue to restore state on page load
    $activeCall = \app\modules\equeue\models\CounterCalls::find()
        ->alias('cc')
        ->innerJoinWith('queue q', false) // To check the queue's status
        ->where([
            'cc.user_id' => $userId,
            'q.status' => \app\modules\equeue\models\Queues::STATUS_CALLED
        ])
        ->one();

    // The view file I created was 'operator.php'.
    // If your file is named 'queues.php', change 'operator' to 'queues' in the line below.
    return $this->render('operator', [
        'counterOne' => $counterOne,
        'services' => $services,
        'queueCounts' => $queueCounts,
        'activeCall' => $activeCall, // Pass the active call object to the view
    ]);
}
