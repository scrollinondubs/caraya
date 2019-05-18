<?php
require_once("secupay_api.php");

$requestData = array("apikey" => "", "apiversion" => secupay_api::get_api_version());

$sp_api = new secupay_api($requestData, 'gettypes', 'application/json', true);
$api_return = $sp_api->request();

echo "Parameter an Secupay:\n\n";
print_r($requestData);
echo "\n---------\n\nAntwort von Secupay:\n\n";
print_r($api_return);