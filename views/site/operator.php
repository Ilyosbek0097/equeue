<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Operator Oynasi';
$this->params['breadcrumbs'][] = $this->title;

// Defensive check to prevent "Undefined variable" error
if (!isset($activeCall)) { $activeCall = null; }
if (!isset($allServices)) { $allServices = []; }

?>

<div class="queue-operator-pwa">
    <div class="row">
        <!-- Left Column: Info and Service List -->
        <div class="col-lg-4 col-md-5">
            <div class="card mb-4 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center bg-light">
                    <h5 class="mb-0"><i class="bx bx-user-circle me-2"></i>Operator</h5>
                    <span class="badge bg-success rounded-pill">Faol</span>
                </div>
                <div class="card-body">
                     <ul class="list-unstyled">
                        <li class="d-flex justify-content-between mb-2"><span class="text-muted">Xodim:</span><span class="fw-semibold"><?= Html::encode($counterOne->user->username ?? 'N/A') ?></span></li>
                        <li class="d-flex justify-content-between mb-2"><span class="text-muted">Filial:</span><span class="fw-semibold"><?= Html::encode($counterOne->branch->name ?? 'N/A') ?></span></li>
                        <li class="d-flex justify-content-between"><span class="text-muted">Oyna:</span><span class="fw-semibold"><?= Html::encode($counterOne->name ?? 'N/A') ?></span></li>
                    </ul>
                    <hr>
                    <div class="d-grid gap-2">
                        <button id="history-btn" class="btn btn-outline-secondary"><i class="bx bx-history me-1"></i> Bugungi Tarix</button>
                        <button id="waiting-list-btn" class="btn btn-outline-info"><i class="bx bx-list-ul me-1"></i> Kutayotganlar Ro'yxati</button>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-light"><h5 class="mb-0"><i class="bx bx-list-ul me-2"></i>Biriktirilgan Xizmatlar</h5></div>
                <div class="card-body">
                    <div id="service-list" class="list-group">
                        <?php if (empty($services)): ?>
                            <p class="text-center text-muted">Sizga biriktirilgan xizmatlar mavjud emas.</p>
                        <?php else: ?>
                            <?php foreach($services as $service):
                                $count = $queueCounts[$service->id]['count'] ?? 0;
                            ?>
                                <div id="service-item-<?= $service->id ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" data-name="<?= Html::encode($service->name) ?>" data-priority="<?= (int)($service->priority ?? 0) ?>">
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
                    <div id="message-area" style="min-height: 50px;"></div>

                    <div id="idle-view">
                        <h2 class="text-muted fw-light">Navbat kutilmoqda...</h2>
                        <p class="text-muted mb-4">Yangi mijozni chaqirish uchun tugmani bosing.</p>
                        <button id="call-next-btn" class="btn btn-primary btn-lg" type="button">
                            <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                            <i class="bx bx-play-circle me-1"></i>
                            <span class="label">Keyingi Navbat</span>
                        </button>
                    </div>

                    <div id="active-view" class="d-none">
                        <div class="current-number-wrapper bg-light rounded p-4 mb-4">
                            <div class="text-muted mb-2">Hozirgi navbat raqami</div>
                            <div id="current-number" class="display-1 fw-bold text-primary">---</div>
                            <div class="mt-2"><span id="current-service" class="h5 text-muted">---</span></div>
                        </div>
                        <div class="action-buttons">
                            <p class="text-muted">Mijozga xizmat ko'rsatib bo'lgach, holatni belgilang:</p>
                            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                                <button class="btn btn-success" data-status="served"><i class="bx bx-check-circle me-2"></i>Xizmat Ko'rsatildi</button>
                                <button class="btn btn-warning" data-status="redirect"><i class="bx bx-subdirectory-right me-2"></i>Yo'naltirish</button>
                                <button class="btn btn-danger" data-status="cancelled"><i class="bx bx-x-circle me-2"></i>Bekor Qilish</button>
                                <button class="btn btn-outline-secondary" data-status="recall"><i class="bx bx-bell me-2"></i>Qayta Chaqirish</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODALS -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">...</div>
<div class="modal fade" id="waitingListModal" tabindex="-1" aria-hidden="true">...</div>

<!-- Redirect Modal -->
<div class="modal fade" id="redirectModal" tabindex="-1" aria-labelledby="redirectModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="redirectModalLabel"><i class="bx bx-subdirectory-right me-2"></i>Navbatni Yo'naltirish</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Mijozni qaysi xizmatga yo'naltirish kerak?</p>
        <select class="form-select" id="redirect-service-select">
            <option selected disabled value="">Xizmatni tanlang...</option>
            <?php foreach($allServices as $service): ?>
                <option value="<?= $service->id ?>"><?= Html::encode($service->name) ?></option>
            <?php endforeach; ?>
        </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bekor</button>
        <button type="button" class="btn btn-primary" id="confirm-redirect-btn" disabled>Tasdiqlash</button>
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
        'waitingQueues' => Url::to(['/equeue/next/waiting-queues']),
        'redirectQueue' => Url::to(['/equeue/next/redirect-queue']), // New URL
    ],
    'csrf' => Yii::$app->request->getCsrfToken(),
    'counterId' => $counterOne->id ?? 0,
    'initialState' => ($activeCall && $activeCall->queue) ? ['queue_id' => $activeCall->queue_id, 'service_id' => $activeCall->queue->service_id, 'nextNumber' => $activeCall->queue->queue_number] : null,
];

$this->registerJs('window.QueueConfig = ' . yii\helpers\Json::htmlEncode($config) . ';', \yii\web\View::POS_HEAD);
$js = <<<JS
const QueueOperator = {
    config: window.QueueConfig,
    elements: {
        // ... (all previous elements)
        historyBtn: $('#history-btn'),
        waitingListBtn: $('#waiting-list-btn'),
        redirectModal: new bootstrap.Modal(document.getElementById('redirectModal')),
        redirectServiceSelect: $('#redirect-service-select'),
        confirmRedirectBtn: $('#confirm-redirect-btn'),
    },
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
        // ... (previous binds)
        this.elements.actionButtons.on('click', (e) => this.handleUpdateStatus($(e.currentTarget).data('status')));
        this.elements.redirectServiceSelect.on('change', () => this.elements.confirmRedirectBtn.prop('disabled', !this.elements.redirectServiceSelect.val()));
        this.elements.confirmRedirectBtn.on('click', () => this.handleRedirectConfirm());
    },

    // ... (setState, updateDisplay, showMessage, etc. are the same)

    handleUpdateStatus: function(status) {
        if (status === 'redirect') {
            this.elements.redirectServiceSelect.val('');
            this.elements.confirmRedirectBtn.prop('disabled', true);
            this.elements.redirectModal.show();
            return;
        }
        // ... (rest of the function is the same)
    },

    handleRedirectConfirm: function() {
        const targetServiceId = this.elements.redirectServiceSelect.val();
        if (!targetServiceId) return;

        this.elements.redirectModal.hide();
        this.clearMessage();

        $.ajax({
            url: this.config.urls.redirectQueue,
            method: 'POST',
            data: {
                _csrf: this.config.csrf,
                queue_id: this.state.lastQueueId,
                target_service_id: targetServiceId
            },
            success: (response) => {
                if (response && response.success) {
                    this.showMessage('success', response.message);
                    this.setState('idle');
                } else {
                    this.showMessage('danger', response.message || 'Navbatni yo\'naltirishda xatolik.');
                }
            },
            error: () => this.showMessage('danger', 'Server bilan bog‘lanishda xatolik yuz berdi.')
        });
    },

    // ... (handleShowHistory and handleShowWaitingList are the same)
};

$(function() {
    QueueOperator.init();
});
JS;

// To keep the message short, I've omitted the full JS code here, but it's in the file.
// The ellipses (...) represent unchanged code.
$this->registerJs($js);
?>
