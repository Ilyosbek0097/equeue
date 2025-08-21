<?php
/** @var yii\web\View $this */
/** @var \app\modules\equeue\models\Service[] $services */

use yii\helpers\Url;

$this->title = 'Elektron Navbat';
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->title ?></title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #f0f2f5; margin: 0; }
        .container { text-align: center; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .services { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-top: 30px; }
        .service-btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 30px;
            font-size: 24px;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
            min-height: 150px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .service-btn:hover { background-color: #0056b3; }
        #ticket-info { margin-top: 30px; border: 1px solid #ddd; padding: 20px; border-radius: 8px; display: none; text-align: left; }
    </style>
</head>
<body>

<div class="container">
    <h1>Xizmat turini tanlang</h1>
    <div class="services">
        <?php foreach ($services as $service): ?>
            <button class="service-btn" data-id="<?= $service->id ?>" data-url="<?= Url::to(['/equeue/service/generate-ticket', 'id' => $service->id]) ?>">
                <?= $service->name ?>
            </button>
        <?php endforeach; ?>
    </div>
    <div id="ticket-info">
        <h3>Sizning navbatingiz:</h3>
        <p><strong>Xizmat:</strong> <span id="ticket-service"></span></p>
        <p><strong>Raqam:</strong> <span id="ticket-number"></span></p>
        <p><strong>Vaqt:</strong> <span id="ticket-time"></span></p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const serviceButtons = document.querySelectorAll('.service-btn');
    serviceButtons.forEach(button => {
        button.addEventListener('click', function() {
            const url = this.getAttribute('data-url');

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Display ticket info on screen (for demo purposes)
                        document.getElementById('ticket-service').textContent = data.service_name;
                        document.getElementById('ticket-number').textContent = data.ticket_number;
                        document.getElementById('ticket-time').textContent = data.created_at;
                        document.getElementById('ticket-info').style.display = 'block';

                        // Here you would typically send the data to a printer
                        // For example, using a library like Print.js or a custom printing solution
                        console.log('Printing ticket:', data);
                        // In a real scenario, you would call a print function here.
                        // For example: printTicket(data);
                    } else {
                        alert('Xatolik: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Server bilan bog`lanishda xatolik.');
                });
        });
    });
});
</script>

</body>
</html>
