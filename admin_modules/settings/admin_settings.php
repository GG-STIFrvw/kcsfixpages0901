<?php
session_start();
require_once '../../config.php';

// Ensure only logged-in admins can access
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

$userId = $_SESSION['user']['id'];
$message = '';
$message_type = '';

$profile_dir = '../../profile_pics/';
if (!is_dir($profile_dir)) {
    mkdir($profile_dir, 0755, true);
}

// Fetch admin data
$stmt = $pdo->prepare("SELECT id, username, full_name, email, contact_number, home_address, profile_picture, password FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: ../../login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $contact_number = trim($_POST['contact_number']);
    $home_address = trim($_POST['home_address']);
    $new_password = $_POST['new_password'] ?? '';
    $current_password = $_POST['current_password'] ?? '';

    if (empty($full_name) || empty($email) || empty($contact_number)) {
        $message = "Please fill in all required fields.";
        $message_type = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
        $message_type = "error";
    } else {
        $check_stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check_stmt->execute([$email, $userId]);
        if ($check_stmt->fetch()) {
            $message = "Email already taken by another account.";
            $message_type = "error";
        }
    }

    if (empty($message)) {
        $update_fields = ['full_name', 'email', 'contact_number', 'home_address'];
        $update_values = [$full_name, $email, $contact_number, $home_address];

        if (!empty($new_password)) {
            if (empty($current_password) || !password_verify($current_password, $user['password'])) {
                $message = "Current password is incorrect.";
                $message_type = "error";
            } elseif (strlen($new_password) < 8 || !preg_match("/[a-z]/", $new_password) || !preg_match("/[A-Z]/", $new_password) || !preg_match("/[0-9]/", $new_password) || !preg_match("/[^\w]/", $new_password)) {
                $message = "New password must be at least 8 characters long and include lowercase, uppercase, number, and special character.";
                $message_type = "error";
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_fields[] = 'password';
                $update_values[] = $hashed_password;
            }
        }

        if (empty($message) && isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['profile_picture'];
            $tmp_name = $file['tmp_name'];
            $original_name = basename($file['name']);
            $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($ext, $allowed)) {
                $message = "Only JPG, PNG, and GIF files are allowed.";
                $message_type = "error";
            } else {
                $new_filename = $userId . '_' . uniqid() . '.' . $ext;
                $destination = $profile_dir . $new_filename;

                if (move_uploaded_file($tmp_name, $destination)) {
                    if (!empty($user['profile_picture']) && file_exists($profile_dir . $user['profile_picture']) && $user['profile_picture'] != 'default_profile.png') {
                        unlink($profile_dir . $user['profile_picture']);
                    }
                    $update_fields[] = 'profile_picture';
                    $update_values[] = $new_filename;
                    $_SESSION['user']['profile_picture'] = $new_filename;
                } else {
                    $message = "Failed to upload profile picture.";
                    $message_type = "error";
                }
            }
        }

        if (empty($message)) {
            $sql = "UPDATE users SET " . implode(' = ?, ', $update_fields) . " = ? WHERE id = ?";
            $update_values[] = $userId;
            $update_stmt = $pdo->prepare($sql);

            if ($update_stmt->execute($update_values)) {
                $_SESSION['user']['full_name'] = $full_name;
                $_SESSION['user']['email'] = $email;
                $_SESSION['user']['contact_number'] = $contact_number;
                $_SESSION['user']['home_address'] = $home_address;

                $message = "Profile updated successfully!";
                $message_type = "success";

                $stmt = $pdo->prepare("SELECT id, username, full_name, email, contact_number, home_address, profile_picture FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $message = "Error updating profile.";
                $message_type = "error";
            }
        }
    }
}

$current_profile_pic = !empty($user['profile_picture']) && file_exists($profile_dir . $user['profile_picture'])
    ? $profile_dir . $user['profile_picture']
    : '../../assets/default_profile.png';

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Settings</title>
<link rel="stylesheet" href="settings.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>

<div class="header">
    <h1>Admin Settings</h1>
    <a href="../../dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
</div>

<div class="container">
    <?php if ($message): ?>
        <div class="message <?= $message_type ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="form-section">
        <h2>Update Profile</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="profile-picture-container">
                <img src="<?= htmlspecialchars($current_profile_pic) ?>" alt="Profile Picture">
                <input type="file" name="profile_picture" accept="image/*">
            </div>

            <div class="form-group">
                <label for="full_name">Full Name:</label>
                <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>

            <div class="form-group">
                <label for="contact_number">Contact Number:</label>
                <input type="tel" id="contact_number" name="contact_number" value="<?= htmlspecialchars($user['contact_number']) ?>">
            </div>

            <div class="form-group">
                <label for="home_address">Home Address:</label>
                <input type="text" id="home_address" name="home_address" value="<?= htmlspecialchars($user['home_address'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="new_password">New Password (leave blank if not changing):</label>
                <div class="password-field-wrapper">
                    <input type="password" id="new_password" name="new_password">
                    <i class="fas fa-eye" id="toggleNewPassword"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="current_password">Current Password (required to change password):</label>
                <div class="password-field-wrapper">
                    <input type="password" id="current_password" name="current_password">
                    <i class="fas fa-eye" id="toggleCurrentPassword"></i>
                </div>
            </div>

            <div class="form-group">
                <button type="submit">Update Profile</button>
            </div>
        </form>
    </div>
</div>

<script>
function setupPasswordToggle(toggleId, inputId) {
    const toggle = document.getElementById(toggleId);
    const input = document.getElementById(inputId);

    if (toggle && input) {
        toggle.addEventListener('click', () => {
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            toggle.classList.toggle('fa-eye-slash');
        });
    }
}

setupPasswordToggle('toggleNewPassword', 'new_password');
setupPasswordToggle('toggleCurrentPassword', 'current_password');
</script>

</body>
</html>
