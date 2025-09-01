<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'staff') {
    header("Location: login.php");
    exit();
}

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['date'])) {
    $date = $_POST['date'];
    $stmt = $pdo->prepare("SELECT id FROM unavailable_dates WHERE date = ?");
    $stmt->execute([$date]);

    if ($stmt->fetch()) {
        $del = $pdo->prepare("DELETE FROM unavailable_dates WHERE date = ?");
        $del->execute([$date]);
        echo json_encode(["status" => "available"]);
    } else {
        $ins = $pdo->prepare("INSERT INTO unavailable_dates (date) VALUES (?)");
        $ins->execute([$date]);
        echo json_encode(["status" => "unavailable"]);
    }
    exit();
}

$dates = $pdo->query("SELECT date FROM unavailable_dates");
$unavailable = [];
while ($row = $dates->fetch(PDO::FETCH_ASSOC)) {
    $unavailable[] = $row['date'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Availability</title>
    <link rel="stylesheet" href="css/calendar_manager.css">
</head>
<body>

<div class="header">
    <h1>Manage Date Availability</h1>
    <a href="dashboard.php" class="back-link">Back to Dashboard</a>
</div>

<div class="container">
    <div class="calendar-nav">
        <button onclick="changeMonth(-1)">‹ Prev Month</button>
        <h2 id="monthLabel"></h2>
        <button onclick="changeMonth(1)">Next Month ›</button>
    </div>
    <div id="calendar"></div>
</div>

<div id="toast"></div>

<script>
const unavailableDates = <?= json_encode($unavailable) ?>;
const container = document.getElementById('calendar');
const label = document.getElementById('monthLabel');
const toast = document.getElementById('toast');
let currentDate = new Date();

function showToast(message) {
    toast.textContent = message;
    toast.style.display = 'block';
    setTimeout(() => {
        toast.style.display = 'none';
    }, 2000);
}

function renderCalendar(date) {
    container.innerHTML = '';
    const year = date.getFullYear();
    const month = date.getMonth();
    label.textContent = date.toLocaleString('default', { month: 'long', year: 'numeric' });

    const daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    daysOfWeek.forEach(day => {
        const dayHeader = document.createElement('div');
        dayHeader.className = 'day-header';
        dayHeader.textContent = day;
        container.appendChild(dayHeader);
    });

    const firstDayOfMonth = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    for (let i = 0; i < firstDayOfMonth; i++) {
        const emptyDiv = document.createElement('div');
        emptyDiv.className = 'empty';
        container.appendChild(emptyDiv);
    }

    for (let d = 1; d <= daysInMonth; d++) {
        const thisDate = new Date(year, month, d);
        const ymd = `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;

        const div = document.createElement('div');
        div.textContent = d;
        div.className = 'day ' + (unavailableDates.includes(ymd) ? 'unavailable' : 'available');
        
        const today = new Date();
        if (year === today.getFullYear() && month === today.getMonth() && d === today.getDate()) {
            div.classList.add('today');
        }

        div.onclick = () => {
            fetch(window.location.pathname, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'date=' + encodeURIComponent(ymd)
            })
            .then(res => res.json())
            .then(data => {
                showToast(`Date marked as ${data.status}.`);
                const index = unavailableDates.indexOf(ymd);
                if (data.status === 'unavailable') {
                    if (index === -1) unavailableDates.push(ymd);
                } else {
                    if (index > -1) unavailableDates.splice(index, 1);
                }
                renderCalendar(currentDate);
            })
            .catch(error => console.error('Error:', error));
        };

        container.appendChild(div);
    }
}

function changeMonth(offset) {
    currentDate.setMonth(currentDate.getMonth() + offset);
    renderCalendar(currentDate);
}

renderCalendar(currentDate);
</script>

</body>
</html>