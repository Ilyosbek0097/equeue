<?php

namespace app\modules\equeue\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use app\modules\equeue\models\Service;
use app\modules\equeue\models\Ticket;

/**
 * Operator controller for the `equeue` module
 */
class OperatorController extends Controller
{
    /**
     * Displays the operator interface.
     * @return string
     */
    public function actionIndex()
    {
        // For simplicity, we'll fetch all services.
        // In a real app, you might filter by operator's assigned services.
        $services = Service::find()->where(['status' => 1])->all();
        return $this->render('index', [
            'services' => $services,
        ]);
    }

    /**
     * Gets the current queue for a service.
     * @param int $service_id
     * @return array
     */
    public function actionGetQueue($service_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $waiting_tickets = Ticket::find()
            ->where(['service_id' => $service_id, 'status' => Ticket::STATUS_NEW])
            ->orderBy(['created_at' => SORT_ASC])
            ->all();

        $called_ticket = Ticket::find()
            ->where(['service_id' => $service_id, 'status' => Ticket::STATUS_CALLED])
            ->orderBy(['updated_at' => SORT_DESC])
            ->one();

        return [
            'success' => true,
            'waiting' => array_map(function($ticket) {
                return [
                    'id' => $ticket->id,
                    'number' => $ticket->ticket_number,
                ];
            }, $waiting_tickets),
            'called' => $called_ticket ? [
                'id' => $called_ticket->id,
                'number' => $called_ticket->ticket_number,
            ] : null,
        ];
    }


    /**
     * Calls the next customer for a service.
     * @param int $service_id
     * @return array
     */
    public function actionCallNext($service_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // First, find the next ticket in the 'new' state.
        $next_ticket = Ticket::find()
            ->where(['service_id' => $service_id, 'status' => Ticket::STATUS_NEW])
            ->orderBy(['created_at' => SORT_ASC])
            ->one();

        if ($next_ticket === null) {
            return ['success' => false, 'message' => 'Navbatda mijozlar yo\'q.'];
        }

        // Optional: Set any previously 'called' ticket for this service to 'served' or another status
        Ticket::updateAll(['status' => Ticket::STATUS_SERVED], ['service_id' => $service_id, 'status' => Ticket::STATUS_CALLED]);

        $next_ticket->status = Ticket::STATUS_CALLED;
        if ($next_ticket->save()) {
            // This ticket data will be sent to the main display board
            return [
                'success' => true,
                'ticket_id' => $next_ticket->id,
                'ticket_number' => $next_ticket->ticket_number,
                'service_name' => $next_ticket->service->name,
            ];
        } else {
            return ['success' => false, 'message' => 'Mijozni chaqirishda xatolik.', 'errors' => $next_ticket->errors];
        }
    }

    /**
     * Updates the status of a ticket.
     * @param int $ticket_id
     * @param int $status
     * @return array
     */
    public function actionUpdateStatus($ticket_id, $status)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $ticket = Ticket::findOne($ticket_id);
        if ($ticket === null) {
            return ['success' => false, 'message' => 'Chipta topilmadi.'];
        }

        // Add validation for status if needed
        $ticket->status = $status;

        if ($ticket->save()) {
            return ['success' => true];
        } else {
            return ['success' => false, 'message' => 'Holatni yangilashda xatolik.', 'errors' => $ticket->errors];
        }
    }
}
