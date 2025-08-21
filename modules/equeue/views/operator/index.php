<?php
/** @var yii\web\View $this */
/** @var \app\modules\equeue\models\Service[] $services */

use yii\helpers\Url;

$this->title = 'Operator Paneli';
// Assuming the main layout already includes Bootstrap
?>
<div class="equeue-operator">
    <h1><?= $this->title ?></h1>

    <div class="row">
        <?php foreach ($services as $service): ?>
            <div class="col-md-4">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?= $service->name ?></h3>
                    </div>
                    <div class="panel-body" id="service-panel-<?= $service->id ?>" data-service-id="<?= $service->id ?>">
                        <div class="text-center">
                            <button class="btn btn-success btn-lg call-next-btn">Keyingisini chaqirish</button>
                        </div>

                        <hr>

                        <h4>Hozir chaqirildi:</h4>
                        <div class="called-ticket text-center" style="font-size: 2.5rem; font-weight: bold; margin: 10px 0;">
                            <span class="called-ticket-number">-</span>
                        </div>

                        <hr>

                        <h4>Navbatdagilar:</h4>
                        <ul class="list-group waiting-list">
                            <li class="list-group-item">Yuklanmoqda...</li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
// We'll assume jQuery is loaded globally by the main application layout.
// If not, you should register it:
// $this->registerJsFile('https://code.jquery.com/jquery-3.6.0.min.js', ['position' => \yii\web\View::POS_HEAD]);

$updateQueueUrl = Url::to(['/equeue/operator/get-queue']);
$callNextUrl = Url::to(['/equeue/operator/call-next']);

$js = <<<JS
$(document).ready(function() {

    function updateQueue(panel) {
        const serviceId = panel.data('service-id');
        const waitingList = panel.find('.waiting-list');
        const calledTicketNumber = panel.find('.called-ticket-number');

        $.ajax({
            url: '{$updateQueueUrl}',
            data: { service_id: serviceId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    waitingList.empty();
                    if (response.waiting.length > 0) {
                        response.waiting.forEach(function(ticket) {
                            waitingList.append('<li class="list-group-item">' + ticket.number + '</li>');
                        });
                    } else {
                        waitingList.append('<li class="list-group-item">Navbatda mijoz yo\'q</li>');
                    }

                    if (response.called) {
                        calledTicketNumber.text(response.called.number);
                    } else {
                        calledTicketNumber.text('-');
                    }
                }
            },
            error: function() {
                waitingList.empty().append('<li class="list-group-item text-danger">Xatolik</li>');
            }
        });
    }

    // Update all panels initially
    $('.panel-body').each(function() {
        updateQueue($(this));
    });

    // Set interval to update queues periodically
    setInterval(function() {
        $('.panel-body').each(function() {
            updateQueue($(this));
        });
    }, 5000); // every 5 seconds

    // Handle "Call Next" button click
    $('.call-next-btn').on('click', function() {
        const panel = $(this).closest('.panel-body');
        const serviceId = panel.data('service-id');

        $.ajax({
            url: '{$callNextUrl}',
            data: { service_id: serviceId },
            dataType: 'json',
            method: 'POST', // Use POST for actions that change state
            success: function(response) {
                if (response.success) {
                    // The main display board should be updated with this info
                    console.log('Called:', response.ticket_number);
                    // Immediately update this panel's queue
                    updateQueue(panel);
                } else {
                    alert('Xatolik: ' + response.message);
                }
            },
            error: function() {
                alert('Server bilan bog`lanishda xatolik.');
            }
        });
    });

});
JS;
$this->registerJs($js);
?>
