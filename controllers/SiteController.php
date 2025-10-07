<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\modules\equeue\models\Service;
use app\modules\equeue\models\Ticket;
use yii\db\Expression;

class SiteController extends Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionReportAll()
    {
        $today_start = strtotime('today midnight');
        $today_end = strtotime('tomorrow midnight') - 1;

        $reportData = Ticket::find()
            ->select([
                's.name as service_name',
                'COUNT(t.id) as total_clients',
                new Expression('SUM(CASE WHEN t.status = :served_status THEN 1 ELSE 0 END) as served_clients'),
                new Expression('AVG(CASE WHEN t.status = :served_status THEN t.updated_at - t.created_at ELSE NULL END) as avg_service_time_seconds')
            ])
            ->from(['t' => Ticket::tableName()])
            ->join('INNER JOIN', ['s' => Service::tableName()], 't.service_id = s.id')
            ->where(['s.status' => 1])
            ->andWhere(['between', 't.created_at', $today_start, $today_end])
            ->groupBy(['s.id', 's.name'])
            ->addParams([':served_status' => Ticket::STATUS_SERVED])
            ->asArray()
            ->all();

        // Calculate unserved clients in PHP to keep the query cleaner
        foreach ($reportData as &$data) {
            $data['unserved_clients'] = $data['total_clients'] - $data['served_clients'];
            // Ensure avg_service_time_seconds is not null
            $data['avg_service_time_seconds'] = $data['avg_service_time_seconds'] ?? 0;
        }

        return $this->render('report-all', [
            'reportData' => $reportData,
        ]);
    }
}
