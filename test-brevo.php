<?php
require_once 'vendor/autoload.php';
include('API_config.php');

use SendinBlue\Client\Configuration;
use SendinBlue\Client\Api\AccountApi;
use GuzzleHttp\Client;

try {
    $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', BREVO_API_KEY);
    $apiInstance = new AccountApi(new Client(), $config);
    $account = $apiInstance->getAccount();

    echo "Connected to Brevo successfully!<br>";
    echo "Email: " . $account['email'] . "<br>";
    echo "Plan type: " . $account['plan'][0]['type'] . "<br>";
} catch (Exception $e) {
    echo "Brevo connection failed: " . $e->getMessage();
}
