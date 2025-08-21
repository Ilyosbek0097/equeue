<?php
/** @var yii\web\View $this */

use yii\helpers\Url;

$this->title = 'Elektron Navbat Tablosi';
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->title ?></title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #003366; color: white; margin: 0; padding: 20px; overflow: hidden; }
        .display-board { display: flex; height: calc(100vh - 40px); }
        .main-call { flex: 3; display: flex; flex-direction: column; justify-content: center; align-items: center; background-color: #004080; border-radius: 15px; margin-right: 20px; padding: 40px; animation: fadeIn 1s; }
        .main-call .ticket-number { font-size: 15vw; font-weight: bold; line-height: 1; }
        .main-call .window-number { font-size: 8vw; font-weight: normal; }
        .recent-calls { flex: 2; display: flex; flex-direction: column; background-color: #002952; border-radius: 15px; padding: 20px; }
        .recent-calls h2 { text-align: center; margin-top: 0; font-size: 3vw; border-bottom: 2px solid #004080; padding-bottom: 10px; }
        .recent-list { list-style: none; padding: 0; margin: 0; flex-grow: 1; }
        .recent-list li { display: flex; justify-content: space-between; align-items: center; padding: 15px 10px; font-size: 2.5vw; border-bottom: 1px solid #003366; }
        .recent-list li:last-child { border-bottom: none; }
        .recent-list .ticket { font-weight: bold; }
        .recent-list .window { background-color: #007bff; padding: 5px 15px; border-radius: 8px; }
        .new-call-animation { animation: highlight 1.5s; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes highlight { 0% { background-color: #ffc107; color: #000; } 50% { background-color: #ffc107; color: #000; } 100% { background-color: #004080; color: #fff; } }
    </style>
</head>
<body>

<div class="display-board">
    <div class="main-call" id="main-call-panel">
        <div class="ticket-number">-</div>
        <div class="window-number">
            <span>Oyna:</span>
            <span class="window-id">-</span>
        </div>
    </div>
    <div class="recent-calls">
        <h2>Keyingi chaqiruvlar</h2>
        <ul class="recent-list" id="recent-list-panel">
            <!-- Items will be inserted here by JS -->
        </ul>
    </div>
</div>

<audio id="notification-sound" src="https://www.soundjay.com/buttons/sounds/button-16.mp3" preload="auto"></audio>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mainCallPanel = document.getElementById('main-call-panel');
    const mainTicketNumber = mainCallPanel.querySelector('.ticket-number');
    const main_window_id = mainCallPanel.querySelector('.window-id');
    const recentListPanel = document.getElementById('recent-list-panel');
    const notificationSound = document.getElementById('notification-sound');
    let lastCalledId = 0;

    const getCalledTicketsUrl = '<?= Url::to(['/equeue/display/get-called-tickets']) ?>';

    function updateDisplay() {
        fetch(getCalledTicketsUrl + '?last_called_id=' + lastCalledId)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.tickets.length > 0) {
                    const latestTicket = data.tickets[0];

                    if (data.new_call) {
                        lastCalledId = latestTicket.id;
                        mainTicketNumber.textContent = latestTicket.ticket_number;
                        main_window_id.textContent = latestTicket.window_number;
                        mainCallPanel.classList.add('new-call-animation');
                        notificationSound.play().catch(e => console.log("Autoplay was prevented."));

                        mainCallPanel.addEventListener('animationend', () => {
                            mainCallPanel.classList.remove('new-call-animation');
                        }, { once: true });
                    }

                    recentListPanel.innerHTML = '';
                    // Show other tickets in the side list (from the 2nd ticket onwards)
                    for (let i = 1; i < data.tickets.length; i++) {
                        const ticket = data.tickets[i];
                        const li = document.createElement('li');
                        li.innerHTML = `<span class="ticket">${ticket.ticket_number}</span> <span class="window">${ticket.window_number}</span>`;
                        recentListPanel.appendChild(li);
                    }
                } else {
                    // Handle case with no called tickets yet
                    mainTicketNumber.textContent = '-';
                    main_window_id.textContent = '-';
                    recentListPanel.innerHTML = '';
                }
            })
            .catch(error => {
                console.error('Error fetching data:', error);
            });
    }

    // Initial call
    updateDisplay();

    // Poll for updates every 3 seconds
    setInterval(updateDisplay, 3000);
});
</script>

</body>
</html>
