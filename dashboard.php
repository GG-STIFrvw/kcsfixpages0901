<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$role = $user['role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/dynamic-content.css">
</head>
<body>

    <?php include 'navbar.php'; ?>
    <?php include 'sidebar.php'; ?>

    <div class="main-content" id="main-content">
        <div class="dashboard-heading">
            <h1>Dashboard</h1>
            <p>Welcome back to the dashboard<br>
            Select an item from the sidebar to load content.</p>
        </div>
    </div>

    <script>
        function loadContent(page) {
            fetch(page)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('main-content').innerHTML = data;
                })
                .catch(error => {
                    document.getElementById('main-content').innerHTML = '<p>Error loading content.</p>';
                    console.error(error);
                });
        }

        // Optional dark mode logic
        const toggle = document.getElementById("theme");
        if (toggle) {
            toggle.addEventListener("change", () => {
                document.body.classList.toggle("dark-mode", toggle.checked);
                localStorage.setItem("darkMode", toggle.checked ? "enabled" : "disabled");
            });
            if (localStorage.getItem("darkMode") === "enabled") {
                document.body.classList.add("dark-mode");
                toggle.checked = true;
            }
        }
    </script>

</body>
</html>
