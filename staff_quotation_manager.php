<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation Manager</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/qoutation_manager.css">
</head>
<body>
    <div class="header">
        <h1>Quotation Management</h1>
        <a href="dashboard.php" class="back-link">Back to Dashboard</a>
    </div>

    <div class="container">
        <a href="staff_create_quotation.php" class="card">
            <div class="card-icon">
                <i class="fas fa-plus-circle"></i>
            </div>
            <div class="card-content">
                <h3>Create Quotation</h3>
                <p>Generate a new quotation for a customer.</p>
            </div>
        </a>

        <a href="staff_view_quotation.php" class="card">
            <div class="card-icon">
                <i class="fas fa-tasks"></i>
            </div>
            <div class="card-content">
                <h3>View Quotations</h3>
                <p>Review and manage all existing quotations.</p>
            </div>
        </a>
    </div>
</body>
</html>