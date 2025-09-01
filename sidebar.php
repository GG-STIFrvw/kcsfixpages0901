<?php
if (!isset($_SESSION)) session_start();

$user = $_SESSION['user'] ?? null;
$role = $user['role'] ?? '';
$default_pic = 'https://tinyurl.com/pf-default';
$profile_dir = 'profile_pics/';
$uploaded_pic_filename = $user['profile_picture'] ?? '';
$full_path = __DIR__ . '/' . $profile_dir . $uploaded_pic_filename;

if ($uploaded_pic_filename && file_exists($full_path) && !is_dir($full_path)) {
    $profile_path = $profile_dir . $uploaded_pic_filename;
} else {
    $profile_path = $default_pic;
}
?>

<div class="sidebar">
    <div class="profile">
        <img src="<?php echo htmlspecialchars($profile_path); ?>" alt="Profile Picture" />
        <h3><?php echo htmlspecialchars($user['full_name'] ?? ''); ?></h3>
        <p><?php echo ucfirst($role); ?></p>
    </div>

    <div class="nav-section">
        <p class="section-title">Navigation</p>
        <?php
        switch ($role) {
            case 'admin':
                echo "<a href='admin_modules/settings/admin_settings.php'><i class='fas fa-cog'></i> Settings</a>";
                echo "<a href='inventory_management.php'><i class='fas fa-chart-bar'></i> Inventory Reports</a>";
                echo "<a href='admin_modules/maintenance/maintenance.php'><i class='fas fa-users-cog'></i> Maintenance</a>";
                echo "<a href='admin_modules/log_history/loghistory.php'><i class='fas fa-history'></i> Log History</a>";
                echo "<a href='admin_modules/settings/services.php'><i class='fas fa-cogs'></i> Services</a>";
                break;
            case 'staff':
                echo "
                <div class='dropdown'>
                    <a class='dropdown-toggle'><i class='fas fa-calendar-check'></i> Appointment Management <i class='fas fa-caret-down'></i></a>
                    <div class='dropdown-menu'>
                        <a href='staff_appointment_managent.php?status=pending'>Appointment Management</a>
                        <a href='staff_calendar_manager.php'>Calendar Manager</a>
                        <a href='staff_bay_manager.php'>Bay Manager</a>
                    </div>
                </div>
                <hr>";
                echo "
                <div class='dropdown'>
                    <a href='#' class='dropdown-toggle'><i class='fas fa-tasks'></i> Job Order Manager <i class='fas fa-caret-down'></i></a>
                    <div class='dropdown-menu'>
                        <a href='staff_create_JO.php '>Create Job Order</a>
                        <a href='staff_all_Job_orders.php'>Job Order List</a>
                    </div>
                </div><hr>";
                echo "<a href='staff_quotation_manager.php'><i class='fas fa-file-invoice'></i> Quotation Management</a>";
                echo "<a href='staff_sendNotif.php'><i class='fas fa-paper-plane'></i> Send Notification</a>";
                echo "<a href='staff_billing.php'><i class='fas fa-file-invoice-dollar'></i> Billing</a>";
                echo "<a href='modules/appointments/CDM.php'><i class='fas fa-user'></i> Customer Data Management</a>";
                echo "<a href='staff_settings.php'><i class='fas fa-cog'></i> Settings</a>";
                break;
            case 'inventory_manager':
                echo "<a href='modules/inventory/dashboard.php'><i class='fas fa-boxes'></i> Inventory Dashboard</a>";
                echo "<a href='inventory_management.php'><i class='fas fa-warehouse'></i> Inventory Manager</a>";
                break;
            case 'mechanic':
                echo "<a href='modules/job_orders/view.php'><i class='fas fa-tools'></i> Job Orders</a>";
                break;
        }
        ?>
    </div>

    <div class="logout-section">
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>
