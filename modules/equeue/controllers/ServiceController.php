<?php

namespace app\modules\equeue\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use app\modules\equeue\models\Service;
use app\modules\equeue\models\Ticket;

/**
 * Service controller for the `equeue` module
 */
class ServiceController extends Controller
{
    public $layout = false; // Kiosk uchun layout kerak emas

    /**
     * Lists all active Service models.
     * @return string
     */
    public function actionIndex()
    {
        $services = Service::find()->where(['status' => 1])->all();
        return $this->render('index', [
            'services' => $services,
        ]);
    }

    /**
     * Generates a new ticket for a service.
     * @param integer $id the ID of the service
     * @return array
     */
    public function actionGenerateTicket($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $service = Service::findOne($id);
        if ($service === null) {
            return ['success' => false, 'message' => 'Xizmat topilmadi.'];
        }

        // Chipta raqamini generatsiya qilish
        $ticket_number_str = Ticket::generateNextTicketNumber($id);
        if ($ticket_number_str === null) {
            return ['success' => false, 'message' => 'Chipta raqamini yaratishda xatolik.'];
        }

        $ticket = new Ticket();
        $ticket->service_id = $id;
        $ticket->ticket_number = $ticket_number_str;
        $ticket->status = Ticket::STATUS_NEW;

        if ($ticket->save()) {
            return [
                'success' => true,
                'ticket_number' => $ticket->ticket_number,
                'service_name' => $service->name,
                'created_at' => date('Y-m-d H:i:s', $ticket->created_at),
            ];
        } else {
            return ['success' => false, 'message' => 'Chipta yaratishda xatolik.', 'errors' => $ticket->errors];
        }
    }
}
