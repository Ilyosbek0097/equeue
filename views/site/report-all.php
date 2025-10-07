<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $reportData array */

$this->title = 'Umumiy Hisobot';
?>
<h1><?= Html::encode($this->title) ?></h1>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Xizmat Nomi</th>
            <th>Jami Mijozlar</th>
            <th>Xizmat Ko'rsatilganlar</th>
            <th>Xizmat Ko'rsatilmaganlar</th>
            <th>O'rtacha Xizmat Vaqti</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($reportData)): ?>
            <tr>
                <td colspan="5">Bugun uchun ma'lumot topilmadi.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($reportData as $data): ?>
                <tr>
                    <td><?= Html::encode($data['service_name']) ?></td>
                    <td><?= (int)$data['total_clients'] ?></td>
                    <td><?= (int)$data['served_clients'] ?></td>
                    <td><?= (int)$data['unserved_clients'] ?></td>
                    <td><?= round($data['avg_service_time_seconds']) ?> sekund</td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
