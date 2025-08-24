<?php

use app\modules\equeue\models\Users;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Operator Oynasi';
$this->params['breadcrumbs'][] = $this->title;
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

<?php
$callNextUrl = Url::to(['/equeue/api/call-next']); // Assuming a dedicated API controller
$updateStatusUrl = Url::to(['/equeue/api/update-status']); // Assuming a dedicated API controller

$js = <<<JS
const QueueOperator = {
    // Configuration
    config: {
        urls: {
            callNext: '$callNextUrl',
            updateStatus: '$updateStatusUrl'
        },
        csrf: $('meta[name="csrf-token"]').attr('content'),
        counterId: '$counterOne->id',
        initialState: <?= $activeCall ? json_encode([
            'queue_id' => $activeCall->queue_id,
            'service_id' => $activeCall->queue->service_id,
            'nextNumber' => $activeCall->queue->queue_number,
        ]) : 'null' ?>
    },

    // UI Elements
    elements: {
        idleView: $('#idle-view'),
        activeView: $('#active-view'),
        callNextBtn: $('#call-next-btn'),
        messageArea: $('#message-area'),
        currentNumber: $('#current-number'),
        currentService: $('#current-service'),
        actionButtons: $('#active-view .action-buttons button')
    },

    // State
    state: {
        lastQueueId: null,
        lastServiceId: null,
        isBusy: false
    },

    // Initialization
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
        this.elements.actionButtons.on('click', (e) => {
            const status = $(e.currentTarget).data('status');
            this.handleUpdateStatus(status);
        });
    },

    // State Machine
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

    // UI Updates
    updateDisplay: function(data) {
        this.state.lastQueueId = data.queue_id || null;
        this.state.lastServiceId = data.service_id || null;

        this.elements.currentNumber.text(data.nextNumber || '---');

        if (this.state.lastServiceId) {
            const serviceItem = $('#service-item-' + this.state.lastServiceId);
            this.elements.currentService.text('Xizmat: ' + (serviceItem.data('name') || 'Noma\'lum'));

            const badge = $('#count-service-' + this.state.lastServiceId);
            const currentCount = parseInt(badge.text() || '0', 10);
            if (currentCount > 0) {
                badge.text(currentCount - 1);
            }
        } else {
            this.elements.currentService.text('---');
        }
    },

    showMessage: function(type, text) {
        const alertClass = type === 'success' ? 'alert-success' : (type === 'danger' ? 'alert-danger' : 'alert-info');
        const icon = type === 'success' ? 'bx-check-circle' : (type === 'danger' ? 'bx-error-circle' : 'bx-info-circle');

        // Using string concatenation to avoid PHP parsing issues with `${...}` syntax in heredoc strings.
        const alertHtml =
            '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
                '<i class="bx ' + icon + ' me-2"></i>' +
                text +
                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
            '</div>';

        this.elements.messageArea.html(alertHtml);
    },

    clearMessage: function() {
        this.elements.messageArea.html('');
    },

    // Event Handlers
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
            this.showMessage('info', `Navbat #${this.elements.currentNumber.text()} qayta chaqirildi.`);
            // In a real app, you would trigger a sound or a visual flash here.
            return;
        }

        this.clearMessage();

        $.ajax({
            url: this.config.urls.updateStatus,
            method: 'POST',
            data: {
                _csrf: this.config.csrf,
                queue_id: this.state.lastQueueId,
                status: status
            },
            success: (response) => {
                if (response && response.success) {
                    this.showMessage('success', response.message);
                    this.setState('idle');
                } else {
                    this.showMessage('danger', response.message || 'Statusni yangilashda xatolik.');
                }
            },
            error: () => {
                this.showMessage('danger', 'Server bilan bog‘lanishda xatolik yuz berdi.');
            }
        });
    }
};

$(function() {
    QueueOperator.init();
});
JS;

$this->registerJs($js);
?>
