<?php
session_start();
include 'header.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'customer') {
    header("Location: login.php");
    exit();
}

require_once 'config.php';


$services = $pdo->query("SELECT * FROM services WHERE status = 'Available'");

$unavailable_dates = $pdo->query("SELECT date FROM unavailable_dates");
$blocked = [];
while ($row = $unavailable_dates->fetch(PDO::FETCH_ASSOC)) {
    $blocked[] = $row['date'];
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user']['id'];
    $service_id = $_POST['service_id'];
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $plate_number = $_POST['plate_number'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $notes = $_POST['notes'];

    if (in_array($date, $blocked)) {
        $message = "<div class='error-message'>Selected date is unavailable. Please choose another date.</div>";
    } else {
        $v_query = $pdo->prepare("SELECT id FROM vehicles WHERE user_id = ? AND plate_number = ?");
        $v_query->execute([$user_id, $plate_number]);
        $vehicle = $v_query->fetch(PDO::FETCH_ASSOC);

        if ($vehicle) {
            $vehicle_id = $vehicle['id'];
        } else {
            $v_insert = $pdo->prepare("INSERT INTO vehicles (user_id, brand, model, plate_number) VALUES (?, ?, ?, ?)");
            $v_insert->execute([$user_id, $brand, $model, $plate_number]);
            $vehicle_id = $pdo->lastInsertId();
        }

        $stmt = $pdo->prepare("INSERT INTO appointments (user_id, vehicle_id, service_id, scheduled_date, scheduled_time, status, notes) VALUES (?, ?, ?, ?, ?, 'Pending', ?)");
        if ($stmt->execute([$user_id, $vehicle_id, $service_id, $date, $time, $notes])) {
            $message = "<div class='success-message'>Appointment booked successfully!</div>";
        } else {
            $message = "<div class='error-message'>An error occurred. Please try again.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment | KCS Auto Service</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="css/book.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>
<body>
    <br><br><br><br>

    <div class="booking-container">
        <h2 class="text-2xl font-semibold mb-6">Service Booking Form</h2>
        
        <?php echo $message; ?>

        <form class="booking-form" method="POST">
            <div class="form-group">
                <label for="service_id">Select Service:</label>
                <select name="service_id" id="service_id" required>
                    <option value="" disabled selected>Choose a service...</option>
                    <?php while ($service = $services->fetch(PDO::FETCH_ASSOC)): ?>
                        <option value="<?= $service['id'] ?>"><?= htmlspecialchars($service['name']) ?> - ₱<?= number_format($service['cost'], 2) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <fieldset>
                <legend>Vehicle Information</legend>
                <div class="form-group">
                    <label for="brand">Brand:</label>
                    <input type="text" id="brand" name="brand" required>
                </div>
                <div class="form-group">
                    <label for="model">Model:</label>
                    <input type="text" id="model" name="model" required>
                </div>
                <div class="form-group">
                    <label for="plate_number">Plate Number:</label>
                    <input type="text" id="plate_number" name="plate_number" required>
                </div>
            </fieldset>

            <div class="form-group">
                <label for="datePicker">Date:</label>
                <input type="text" name="date" id="datePicker" required placeholder="Select a date">
            </div>

            <div class="form-group">
                <label for="timePicker">Time:</label>
                <input type="time" name="time" id="timePicker" required min="09:00" max="17:00">
            </div>

            <div class="form-group">
                <label for="notes">Additional Notes:</label>
                <textarea name="notes" id="notes" rows="4" placeholder="Any specific requests or details?"></textarea>
            </div>

            <button type="submit">Book Now</button>
        </form>
    </div>

    <script>
        const unavailableDates = <?= json_encode($blocked) ?>;

        flatpickr("#datePicker", {
            dateFormat: "Y-m-d",
            minDate: "today",
            disable: unavailableDates,
            onChange: function(selectedDates, dateStr, instance) {
                const timeInput = document.getElementById("timePicker");
                const selected = new Date(dateStr);
                const now = new Date();

                if (selected.toDateString() === now.toDateString()) {
                    const hours = now.getHours().toString().padStart(2, '0');
                    const minutes = now.getMinutes().toString().padStart(2, '0');
                    timeInput.min = (now.getHours() < 9) ? "09:00" : `${hours}:${minutes}`;
                } else {
                    timeInput.min = "09:00";
                }
                timeInput.max = "17:00";
            }
        });
    </script>

</body>
</html>