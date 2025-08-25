<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\modules\equeue\models\Users;
use app\modules\equeue\models\AuthItem;
use app\modules\equeue\models\Branch;
use kartik\grid\GridView;

/** @var yii\web\View $this */
/** @var array $users */
/** @var array $branches */
/** @var array $roles */
/** @var app\models\Employee $model */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var app\models\EmployeeSearch $searchModel */

$this->title = 'Xodimlar ro‘yxati';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="container-fluid">
    <h1><?= Html::encode($this->title) ?></h1>

    <!-- Flash Messages -->
    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible m-2" role="alert">
            <?= Yii::$app->session->getFlash('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible m-2" role="alert">
            <?= Yii::$app->session->getFlash('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Employee Grid -->
    <div class="card card-primary">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Xodimlar ro‘yxati</h3>
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                <i class="bx bx-user-plus"></i> Yangi xodim qo‘shish
            </button>
        </div>
        <div class="card-body">
           <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'pjax' => true,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    'id',
                    ['attribute' => 'cb_id', 'value' => function($model) { return Users::getBittasi($model->cb_id) ?? ''; }],
                    ['attribute' => 'role_id', 'value' => function($model) { return AuthItem::getItemName($model->role_id) ?? ''; }],
                    ['attribute' => 'branch_id', 'value' => function($model) { return Branch::getBranchName($model->branch_id) ?? ''; }],
                    [
                        'label' => 'Xizmatlar',
                        'format' => 'raw',
                        'value' => function($model) {
                            $badges = '';
                            if ($model->services) {
                                foreach ($model->services as $service) {
                                    $badges .= '<span class="mb-1 badge border border-primary text-primary me-1"><i class="bx bx-check-circle me-1"></i>' . Html::encode($service->name) . '</span>';
                                }
                            }
                            return $badges ?: '<span class="text-muted">mavjud emas</span>';
                        },
                    ],
                    [
                        'class' => 'kartik\grid\ActionColumn',
                        'template' => '{update} {setting}',
                        'buttons' => [
                            'update' => function ($url, $model, $key) {
                                return Html::button('<i class="bx bx-edit"></i>', [
                                    'value' => Url::to(['site/get-employee', 'id' => $key]),
                                    'class' => 'btn btn-sm btn-outline-warning edit-employee-btn',
                                    'title' => 'Xodimni tahrirlash',
                                ]);
                            },
                            'setting' => function ($url, $model, $key) {
                                if( AuthItem::getItemName($model->role_id) === 'equeue_xodim') {
                                    return Html::a('<i class="bx bx-wrench"></i>', ['site/setting-user', 'id' => $key], [
                                        'title' => "Navbatni Sozlash",
                                        'class' => 'btn btn-sm btn-outline-primary'
                                    ]);
                                }
                                return '';
                            }
                        ]
                    ]
                ]
           ])?>
        </div>
    </div>
</div>

<!-- Add Employee Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header bg-success text-white">
            <h5 class="modal-title">Yangi xodim qo‘shish</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Yopish"></button>
        </div>
        <?php $form = ActiveForm::begin(['action' => ['site/save-employes'], 'method' => 'post']); ?>
        <div class="modal-body">
            <?= $form->field($model, 'cb_id')->dropDownList($users, ['prompt' => 'Xodimni tanlang', 'class' => 'form-select mb-3 select2-add'])->label('Xodimni Tanlang') ?>
            <?= $form->field($model, 'role_id')->dropDownList($roles, ['prompt' => 'Rolni tanlang', 'class' => 'form-select mb-3'])->label('Roli') ?>
            <?= $form->field($model, 'branch_id')->dropDownList(ArrayHelper::map($branches, 'id', 'name'), ['prompt' => 'Filialni tanlang', 'class' => 'form-select mb-3 select2-add'])->label('Filiali') ?>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bekor qilish</button>
            <?= Html::submitButton('Qo‘shish', ['class' => 'btn btn-success']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
  </div>
</div>

<!-- Edit Employee Modal -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header bg-warning text-dark">
            <h5 class="modal-title">Xodimni tahrirlash</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Yopish"></button>
        </div>
        <!-- The form tag is still here, but the content will be generated by JS -->
        <?php $editForm = ActiveForm::begin(['id' => 'edit-employee-form', 'action' => ['site/update-employee'], 'method' => 'post']); ?>
        <div class="modal-body" id="edit-modal-body-content">
            <p class="text-center">Yuklanmoqda...</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bekor qilish</button>
            <?= Html::submitButton('Saqlash', ['class' => 'btn btn-warning']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
  </div>
</div>

<?php
// We pass the PHP data to JavaScript as JSON objects
$jsData = [
    'users' => $users,
    'roles' => $roles,
    'branches' => ArrayHelper::map($branches, 'id', 'name')
];
$this->registerJs('window.employeeFormData = ' . json_encode($jsData) . ';', \yii\web\View::POS_HEAD);

$js = <<<JS
$(function() {
    // --- Logic for Add Modal ---
    $('#addEmployeeModal').on('shown.bs.modal', function () {
        if ($.fn.select2) {
            $(this).find('.select2-add').select2({
                dropdownParent: $('#addEmployeeModal'),
                placeholder: 'Tanlang...'
            });
        }
    });

    // --- Logic for Edit Modal ---
    var editModal = new bootstrap.Modal(document.getElementById('editEmployeeModal'));

    $('.edit-employee-btn').on('click', function() {
        var url = $(this).attr('value');

        $('#edit-modal-body-content').html('<p class="text-center">Yuklanmoqda...</p>');
        editModal.show();

        $.ajax({
            url: url,
            type: 'get',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var data = response.data;

                    // Update form action URL to include the employee ID
                    var form = $('#edit-employee-form');
                    var newAction = form.attr('action').split('?')[0] + '?id=' + data.id;
                    form.attr('action', newAction);

                    // --- Build form content with JavaScript ---

                    // Helper function to create a dropdown
                    function createDropdown(name, label, dataArray, selectedValue) {
                        var options = '<option value="">Tanlang</option>';
                        $.each(dataArray, function(key, value) {
                            var selected = (key == selectedValue) ? ' selected' : '';
                            options += '<option value="' + key + '"' + selected + '>' + value + '</option>';
                        });
                        return '<div class="mb-3">' +
                               '<label class="form-label">' + label + '</label>' +
                               '<select name="' + name + '" class="form-select select2-edit">' + options + '</select>' +
                               '</div>';
                    }

                    var formContent = createDropdown('Employee[cb_id]', 'Xodim', window.employeeFormData.users, data.cb_id) +
                                      createDropdown('Employee[role_id]', 'Rol', window.employeeFormData.roles, data.role_id) +
                                      createDropdown('Employee[branch_id]', 'Filial', window.employeeFormData.branches, data.branch_id);

                    $('#edit-modal-body-content').html(formContent);

                    // Initialize Select2 for the new dropdowns
                    if ($.fn.select2) {
                        $('#edit-modal-body-content .select2-edit').select2({
                            dropdownParent: $('#editEmployeeModal'),
                            placeholder: 'Tanlang...'
                        });
                    }

                } else {
                    $('#edit-modal-body-content').html('<p class="text-center text-danger">' + response.message + '</p>');
                }
            },
            error: function() {
                $('#edit-modal-body-content').html('<p class="text-center text-danger">Ma\'lumotlarni yuklashda xatolik.</p>');
            }
        });
    });
});
JS;
$this->registerJs($js);
?>
