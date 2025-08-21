<?php

namespace app\modules\equeue\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use app\modules\equeue\models\Ticket;

/**
 * Display controller for the `equeue` module
 */
class DisplayController extends Controller
{
    public $layout = false; // Full screen display, no layout needed

    /**
     * Renders the main display board.
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Gets the list of recently called tickets.
     * @return array
     */
    public function actionGetCalledTickets()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $called_tickets = Ticket::find()
            ->with('service') // Eager load the service details
            ->where(['status' => Ticket::STATUS_CALLED])
            ->orderBy(['updated_at' => SORT_DESC])
            ->limit(10) // Get the last 10 called tickets
            ->all();

        $last_called_id = (int)Yii::$app->request->get('last_called_id', 0);
        $new_call = false;

        $tickets_data = array_map(function($ticket) {
            // In a real app, you would associate operators with windows/desks
            // For now, we'll generate a random-ish window number
            $window_number = ($ticket->id % 5) + 1;
            return [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'service_name' => $ticket->service ? $ticket->service->name : 'N/A',
                'window_number' => $window_number,
            ];
        }, $called_tickets);

        if (!empty($tickets_data) && $tickets_data[0]['id'] != $last_called_id) {
            $new_call = true;
        }

        return [
            'success' => true,
            'tickets' => $tickets_data,
            'new_call' => $new_call // Flag to indicate if a sound should be played
        ];
    }
}
