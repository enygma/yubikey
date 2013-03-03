Yubikey PHP Library
=======================

[![Build Status](https://secure.travis-ci.org/enygma/yubikey.png?branch=master)](http://travis-ci.org/enygma/yubikey)

This library lets you easily interface with the Yubico REST API for validating
the codes created by the Yubikey.

### Requirements:
- An API as requested from the Yubico site
- A client ID requested from Yubico
- A Yubikey to test out the implementation

### Usage:

Look at the `test.php` example script to see how to use it. This can be executed like:

`php test.php [generated key]`

Example code:

```
<?php

$apiKey = 'dGVzdGluZzEyMzQ1Njc4OTA=';
$clientId = '12345';

$v = new \Yubikey\Validate($apiKey);
$response = $v->check($inputtedKey, $clientId);

echo ($response->success() === true) ? 'success!' : 'you failed. aw.';

?>
```

@author Chris Cornutt <ccornutt@phpdeveloper.org>