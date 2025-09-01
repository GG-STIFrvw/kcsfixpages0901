<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = strip_tags(trim($_POST["name"]));
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $phone = strip_tags(trim($_POST["phone"]));
    $message_body = strip_tags(trim($_POST["message"]));

    if (empty($name) || empty($email) || empty($phone) || empty($message_body) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Invalid input."]);
        exit;
    }

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'kcsautorepair09@gmail.com'; // Your Gmail address
        $mail->Password = 'bgcnvirahydweuxd'; // Your Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        //FIX: Disable SSL certificate verification for local development
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Recipients
        $mail->setFrom($email, $name);
        $mail->addAddress('kcsautorepair09@gmail.com', 'KCS Customer Support'); // Your support email
        $mail->addReplyTo($email, $name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "New Contact Form Message from $name";
        $mail->Body    = "You have received a new message from your website contact form.<br><br>".
                         "<b>Name:</b> {$name}<br>".
                         "<b>Email:</b> {$email}<br>".
                         "<b>Phone:</b> {$phone}<br><br>".
                         "<b>Message:</b><br>{$message_body}";
        $mail->AltBody = "Name: {$name}\nEmail: {$email}\nPhone: {$phone}\n\nMessage:\n{$message_body}";

        $mail->send();
        echo json_encode(["status" => "success", "message" => "Message sent successfully!"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
    }
} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed."]);
}
?>