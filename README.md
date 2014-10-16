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

```php
<?php
$apiKey = 'dGVzdGluZzEyMzQ1Njc4OTA=';
$clientId = '12345';

$v = new \Yubikey\Validate($apiKey, $clientId);
$response = $v->check($inputtedKey);

echo ($response->success() === true) ? 'success!' : 'you failed. aw.';
?>
```

### Multi-Server Requests:

Additonally, the library also supports simultaneous connections to multiple servers. By default it will only make
the request to the first server in the `hosts` list. You can enable the multi-server checking with a second parameter on
the `check()` method:

```php
<?php
$v = new \Yubikey\Validate($apiKey, $clientId);
$response = $v->check($inputtedKey, true);

echo ($response->success() === true) ? 'success!' : 'you failed. aw.';
?>
````

This will make multiple requests and return the pass/fail status of the aggregate responses from each. So, if you have all but one
server pass, the overall response will be a fail. If all return `OK` though, you're in the clear.

### "First in" result

Additionally, you can also switch on and off this aggregation of the results and go with only the "first in" response. You do this
with a flag on the `success` checking method:

```php
<?php
$v = new \Yubikey\Validate($apiKey, $clientId);
$response = $v->check($inputtedKey, true);

echo ($response->success(true) === true) ? 'success!' : 'you failed. aw.';
?>
````

**NOTE:** This will still work without multi-server checking. The "first in" will just always be the single response.


@author Chris Cornutt <ccornutt@phpdeveloper.org>