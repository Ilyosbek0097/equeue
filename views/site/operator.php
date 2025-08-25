<?php

use app\modules\equeue\models\Users;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Operator Oynasi';
$this->params['breadcrumbs'][] = $this->title;

// Defensive check to prevent "Undefined variable" error if controller doesn't pass $activeCall.
if (!isset($activeCall)) {
    $activeCall = null;
}

?>

<div class="queue-operator-pwa">
    <div class="row">
        <!-- Left Column: Info and Service List -->
        <div class="col-lg-4 col-md-5">
            <!-- Operator Info Card -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center bg-light">
                    <h5 class="mb-0"><i class="bx bx-user-circle me-2"></i>Operator</h5>
                    <span class="badge bg-success rounded-pill">Faol</span>
                </div>
                <div class="card-body">
                     <ul class="list-unstyled">
                        <li class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Xodim:</span>
                            <span class="fw-semibold"><?= Html::encode($counterOne->user->username ?? 'N/A') ?></span>
                        </li>
                        <li class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Filial:</span>
                            <span class="fw-semibold"><?= Html::encode($counterOne->branch->name ?? 'N/A') ?></span>
                        </li>
                        <li class="d-flex justify-content-between">
                            <span class="text-muted">Oyna:</span>
                            <span class="fw-semibold"><?= Html::encode($counterOne->name ?? 'N/A') ?></span>
                        </li>
                    </ul>
                    <hr>
                    <div class="d-grid gap-2">
                        <button id="history-btn" class="btn btn-outline-secondary">
                            <i class="bx bx-history me-1"></i> Bugungi Tarix
                        </button>
                        <button id="waiting-list-btn" class="btn btn-outline-info">
                            <i class="bx bx-list-ul me-1"></i> Kutayotganlar Ro'yxati
                        </button>
                    </div>
                </div>
            </div>

            <!-- Service List Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bx bx-list-ul me-2"></i>Biriktirilgan Xizmatlar</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Siz xizmat ko'rsatadigan navbatlar ro'yxati.</p>
                    <div id="service-list" class="list-group">
                        <?php if (empty($services)): ?>
                            <p class="text-center text-muted">Sizga biriktirilgan xizmatlar mavjud emas.</p>
                        <?php else: ?>
                            <?php foreach($services as $service):
                                $count = $queueCounts[$service->id]['count'] ?? 0;
                            ?>
                                <div id="service-item-<?= $service->id ?>"
                                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                    data-name="<?= Html::encode($service->name) ?>"
                                    data-priority="<?= (int)($service->priority ?? 0) ?>">
                                    <?= Html::encode($service->name) ?>
                                    <span class="badge bg-primary rounded-pill" id="count-service-<?= $service->id ?>"><?= $count ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Action Panel -->
        <div class="col-lg-8 col-md-7">
            <div class="card shadow-sm">
                <div class="card-body text-center p-4">
                    <!-- Message Area -->
                    <div id="message-area" style="min-height: 50px;"></div>

                    <!-- Idle State View -->
                    <div id="idle-view">
                        <h2 class="text-muted fw-light">Navbat kutilmoqda...</h2>
                        <p class="text-muted mb-4">Yangi mijozni chaqirish uchun tugmani bosing.</p>
                        <button id="call-next-btn" class="btn btn-primary btn-lg" type="button">
                            <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                            <i class="bx bx-play-circle me-1"></i>
                            <span class="label">Keyingi Navbat</span>
                        </button>
                    </div>

                    <!-- Active State View -->
                    <div id="active-view" class="d-none">
                        <div class="current-number-wrapper bg-light rounded p-4 mb-4">
                            <div class="text-muted mb-2">Hozirgi navbat raqami</div>
                            <div id="current-number" class="display-1 fw-bold text-primary">---</div>
                            <div class="mt-2">
                                <span id="current-service" class="h5 text-muted">---</span>
                            </div>
                        </div>

                        <div class="action-buttons">
                            <p class="text-muted">Mijozga xizmat ko'rsatib bo'lgach, holatni belgilang:</p>
                            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                                <button class="btn btn-success btn-lg" data-status="served">
                                    <i class="bx bx-check-circle me-2"></i>Xizmat Ko'rsatildi
                                </button>
                                <button class="btn btn-danger btn-lg" data-status="cancelled">
                                    <i class="bx bx-x-circle me-2"></i>Bekor Qilindi
                                </button>
                                <button class="btn btn-outline-secondary btn-lg" data-status="recall">
                                    <i class="bx bx-bell me-2"></i>Qayta Chaqirish
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="historyModalLabel"><i class="bx bx-history me-2"></i>Bugungi Xizmat Ko'rsatilgan Navbatlar</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="text-center my-5 d-none" id="history-loader">
            <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Yuklanmoqda...</span></div>
        </div>
        <table class="table table-striped d-none" id="history-table">
          <thead><tr><th>Navbat №</th><th>Xizmat Nomi</th><th>Chaqirildi</th><th>Yakunlandi</th></tr></thead>
          <tbody id="history-table-body"></tbody>
        </table>
        <p class="text-center text-muted my-5 d-none" id="no-history-message">Bugun hali hech kimga xizmat ko'rsatilmagan.</p>
      </div>
    </div>
  </div>
</div>

<!-- Waiting List Modal -->
<div class="modal fade" id="waitingListModal" tabindex="-1" aria-labelledby="waitingListModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="waitingListModalLabel"><i class="bx bx-time-five me-2"></i>Hozir Kutayotgan Navbatlar</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="text-center my-5 d-none" id="waiting-list-loader">
            <div class="spinner-border text-info" role="status"><span class="visually-hidden">Yuklanmoqda...</span></div>
        </div>
        <table class="table table-hover d-none" id="waiting-list-table">
          <thead><tr><th>Navbat №</th><th>Xizmat Nomi</th><th>Kelgan Vaqti</th></tr></thead>
          <tbody id="waiting-list-table-body"></tbody>
        </table>
        <p class="text-center text-muted my-5 d-none" id="no-waiting-list-message">Hozirda kutayotgan navbatlar mavjud emas.</p>
      </div>
    </div>
  </div>
</div>


<?php
$config = [
    'urls' => [
        'callNext' => Url::to(['/equeue/next/call-next']),
        'updateStatus' => Url::to(['/equeue/next/update-status']),
        'todaysHistory' => Url::to(['/equeue/next/todays-history']),
        'waitingQueues' => Url::to(['/equeue/next/waiting-queues']), // New URL
    ],
    'csrf' => Yii::$app->request->getCsrfToken(),
    'counterId' => $counterOne->id ?? 0,
    'initialState' => ($activeCall && $activeCall->queue) ? [
        'queue_id'   => $activeCall->queue_id,
        'service_id' => $activeCall->queue->service_id,
        'nextNumber' => $activeCall->queue->queue_number,
    ] : null,
];

$this->registerJs('window.QueueConfig = ' . yii\helpers\Json::htmlEncode($config) . ';', \yii\web\View::POS_HEAD);
$js = <<<JS
const QueueOperator = {
    // Configuration
    config: window.QueueConfig,

    // UI Elements
    elements: {
        idleView: $('#idle-view'),
        activeView: $('#active-view'),
        callNextBtn: $('#call-next-btn'),
        messageArea: $('#message-area'),
        currentNumber: $('#current-number'),
        currentService: $('#current-service'),
        actionButtons: $('#active-view .action-buttons button'),
        historyBtn: $('#history-btn'),
        historyModal: new bootstrap.Modal(document.getElementById('historyModal')),
        historyTable: $('#history-table'),
        historyTableBody: $('#history-table-body'),
        historyLoader: $('#history-loader'),
        noHistoryMessage: $('#no-history-message'),
        waitingListBtn: $('#waiting-list-btn'),
        waitingListModal: new bootstrap.Modal(document.getElementById('waitingListModal')),
        waitingListTable: $('#waiting-list-table'),
        waitingListTableBody: $('#waiting-list-table-body'),
        waitingListLoader: $('#waiting-list-loader'),
        noWaitingListMessage: $('#no-waiting-list-message'),
    },

    // ... (State and Init are the same)
    state: { lastQueueId: null, lastServiceId: null, isBusy: false },

    init: function() {
        this.bindEvents();
        if (this.config.initialState) {
            this.updateDisplay(this.config.initialState);
            this.setState('active');
            this.showMessage('info', 'Sizda yakunlanmagan faol navbat mavjud.');
        } else {
            this.setState('idle');
        }
    },

    bindEvents: function() {
        this.elements.callNextBtn.on('click', () => this.handleCallNext());
        this.elements.actionButtons.on('click', (e) => this.handleUpdateStatus($(e.currentTarget).data('status')));
        this.elements.historyBtn.on('click', () => this.handleShowHistory());
        this.elements.waitingListBtn.on('click', () => this.handleShowWaitingList());
    },

    // ... (setState, updateDisplay, showMessage, clearMessage, handleCallNext, handleUpdateStatus are the same)
    setState: function(newState) {
        const { idleView, activeView, callNextBtn } = this.elements;
        const callNextBtnSpinner = callNextBtn.find('.spinner-border');
        const callNextBtnLabel = callNextBtn.find('.label');
        this.state.isBusy = (newState === 'loading');
        callNextBtn.prop('disabled', this.state.isBusy || newState === 'active');
        callNextBtnSpinner.toggleClass('d-none', !this.state.isBusy);
        callNextBtnLabel.text(this.state.isBusy ? 'Kuting...' : 'Keyingi Navbat');
        if (newState === 'idle') {
            idleView.removeClass('d-none');
            activeView.addClass('d-none');
            this.state.lastQueueId = null;
            this.state.lastServiceId = null;
        } else if (newState === 'active') {
            idleView.addClass('d-none');
            activeView.removeClass('d-none');
        }
    },
    updateDisplay: function(data) {
        this.state.lastQueueId = data.queue_id || null;
        this.state.lastServiceId = data.service_id || null;
        this.elements.currentNumber.text(data.nextNumber || '---');
        if (this.state.lastServiceId) {
            const serviceItem = $('#service-item-' + this.state.lastServiceId);
            this.elements.currentService.text('Xizmat: ' + (serviceItem.data('name') || 'Noma\'lum'));
            const badge = $('#count-service-' + this.state.lastServiceId);
            const currentCount = parseInt(badge.text() || '0', 10);
            if (currentCount > 0) badge.text(currentCount - 1);
        } else {
            this.elements.currentService.text('---');
        }
    },
    showMessage: function(type, text) {
        const alertClass = type === 'success' ? 'alert-success' : (type === 'danger' ? 'alert-danger' : 'alert-info');
        const icon = type === 'success' ? 'bx-check-circle' : (type === 'danger' ? 'bx-error-circle' : 'bx-info-circle');
        var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' + '<i class="bx ' + icon + ' me-2"></i>' + text + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' + '</div>';
        this.elements.messageArea.html(alertHtml);
    },
    clearMessage: function() { this.elements.messageArea.html(''); },
    handleCallNext: function() {
        if (this.state.isBusy) return;
        this.setState('loading');
        this.clearMessage();
        $.ajax({
            url: this.config.urls.callNext,
            method: 'POST',
            data: { _csrf: this.config.csrf, counter_id: this.config.counterId },
            success: (response) => {
                if (response && response.success) {
                    this.updateDisplay(response);
                    this.setState('active');
                    this.showMessage('success', response.message);
                } else {
                    this.showMessage('danger', response.message || 'Navbat chaqirishda xatolik.');
                    this.setState('idle');
                }
            },
            error: () => {
                this.showMessage('danger', 'Server bilan bog‘lanishda xatolik yuz berdi.');
                this.setState('idle');
            }
        });
    },
    handleUpdateStatus: function(status) {
        if (status === 'recall') {
            this.showMessage('info', 'Navbat #' + this.elements.currentNumber.text() + ' qayta chaqirildi.');
            return;
        }
        this.clearMessage();
        $.ajax({
            url: this.config.urls.updateStatus,
            method: 'POST',
            data: { _csrf: this.config.csrf, queue_id: this.state.lastQueueId, status: status },
            success: (response) => {
                if (response && response.success) {
                    this.showMessage('success', response.message);
                    this.setState('idle');
                } else {
                    this.showMessage('danger', response.message || 'Statusni yangilashda xatolik.');
                }
            },
            error: () => { this.showMessage('danger', 'Server bilan bog‘lanishda xatolik yuz berdi.'); }
        });
    },

    handleShowHistory: function() {
        this.elements.historyLoader.removeClass('d-none');
        this.elements.historyTable.addClass('d-none');
        this.elements.historyTableBody.empty();
        this.elements.noHistoryMessage.addClass('d-none');
        this.elements.historyModal.show();
        $.ajax({
            url: this.config.urls.todaysHistory,
            method: 'GET',
            success: function(response) {
                this.elements.historyLoader.addClass('d-none');
                if (response && response.success && response.history.length > 0) {
                    this.elements.historyTable.removeClass('d-none');
                    response.history.forEach(function(item) {
                        var row = '<tr>' + '<td><strong>' + item.queue_number + '</strong></td>' + '<td>' + item.service_name + '</td>' + '<td>' + item.called_at + '</td>' + '<td>' + item.served_at + '</td>' + '</tr>';
                        this.elements.historyTableBody.append(row);
                    }.bind(this));
                } else {
                    this.elements.noHistoryMessage.removeClass('d-none');
                }
            }.bind(this),
            error: function() {
                this.elements.historyLoader.addClass('d-none');
                this.elements.noHistoryMessage.text('Tarixni yuklashda xatolik yuz berdi.').removeClass('d-none');
            }.bind(this)
        });
    },

    // New function for waiting list
    handleShowWaitingList: function() {
        this.elements.waitingListLoader.removeClass('d-none');
        this.elements.waitingListTable.addClass('d-none');
        this.elements.waitingListTableBody.empty();
        this.elements.noWaitingListMessage.addClass('d-none');
        this.elements.waitingListModal.show();
        $.ajax({
            url: this.config.urls.waitingQueues,
            method: 'GET',
            success: function(response) {
                this.elements.waitingListLoader.addClass('d-none');
                if (response && response.success && response.list.length > 0) {
                    this.elements.waitingListTable.removeClass('d-none');
                    response.list.forEach(function(item) {
                        var row = '<tr>' + '<td><strong>' + item.queue_number + '</strong></td>' + '<td>' + item.service_name + '</td>' + '<td>' + item.created_at + '</td>' + '</tr>';
                        this.elements.waitingListTableBody.append(row);
                    }.bind(this));
                } else {
                    this.elements.noWaitingListMessage.removeClass('d-none');
                }
            }.bind(this),
            error: function() {
                this.elements.waitingListLoader.addClass('d-none');
                this.elements.noWaitingListMessage.text('Kutayotganlar ro\'yxatini yuklashda xatolik yuz berdi.').removeClass('d-none');
            }.bind(this)
        });
    }
};

$(function() {
    QueueOperator.init();
});
JS;

$this->registerJs($js);
?>
