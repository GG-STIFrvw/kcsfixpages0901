<?php
require_once 'vendor/autoload.php';
include('API_config.php');

// Configure API key authorization: api-key
$config = SendinBlue\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', BREVO_API_KEY);

$apiInstance = new SendinBlue\Client\Api\TransactionalEmailsApi(
    new GuzzleHttp\Client(),
    $config
);

$sendSmtpEmail = new \SendinBlue\Client\Model\SendSmtpEmail();
$sendSmtpEmail->setSender(new \SendinBlue\Client\Model\SendSmtpEmailSender(['name' => 'KCS Auto Service', 'email' => 'rockenrollin6767@gmail.com']));
$sendSmtpEmail->setTo([new \SendinBlue\Client\Model\SendSmtpEmailTo(['email' => 'acuna.neil22@gmail.com', 'name' => 'Test Recipient'])]);
$sendSmtpEmail->setSubject('Brevo Test Email');
$sendSmtpEmail->setHtmlContent('<html><body><h1>Test Email</h1><p>This is a test email from Brevo.</p></body></html>');

try {
    $result = $apiInstance->sendTransacEmail($sendSmtpEmail);
    print_r($result);
    echo "<br><br>Test email sent successfully!";
} catch (Exception $e) {
    echo 'Exception when calling TransactionalEmailsApi->sendTransacEmail: ', $e->getMessage(), PHP_EOL;
}
?>