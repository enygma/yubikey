<?php

/**
 * You can request your API key from:
 * https://upgrade.yubico.com/getapikey/
 * 
 * Usage:
 * php test.php [OTP from the Yubikey]
 */

require_once 'vendor/autoload.php';

$apiKey = 'dGVzdGluZzEyMzQ1Njc4OTA=';
$clientId = '12345';

$v = new \Yubikey\Validate($apiKey, $clientId);

if (isset($_SERVER['argv'][1])) {
    $result = $v->check($_SERVER['argv'][1]);

    print_r($result);
    var_export($result->success());
} else {
    echo "No key value specified\n\n";
}
?>