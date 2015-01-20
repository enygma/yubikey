Yubikey PHP Library
=======================

[![Travis-CI Build Status](https://secure.travis-ci.org/enygma/yubikey.png?branch=master)](http://travis-ci.org/enygma/yubikey)
[![Codacy Badge](https://www.codacy.com/project/badge/6b73c56a21734a6d93dae6019f733c5e)](https://www.codacy.com)
[![Code Climate](https://codeclimate.com/github/enygma/yubikey/badges/gpa.svg)](https://codeclimate.com/github/enygma/yubikey)
[![Total Downloads](https://img.shields.io/packagist/dt/enygma/yubikey.svg?style=flat-square)](https://packagist.org/packages/enygma/yubikey)

This library lets you easily interface with the Yubico REST API for validating
the codes created by the Yubikey.

### Requirements:

- An API as requested from the Yubico site
- A client ID requested from Yubico
- A Yubikey to test out the implementation

### Installation

Use the followng command to install the library via Composer:

```
composer require enygma/yubikey
```

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

### HTTP vs HTTPS

By default the library will try to use a `HTTPS` request to the host given. If you need to disable this for some reason
(like no SSL support), you can use the `setUseSecure` method and set it to false:

```php
$v = new \Yubikey\Validate($apiKey, $clientId);
$v->setUseSecure(false);
```

### Overriding hosts

The library comes with a set of hostnames for the Yubico external API servers (api.yubico.com through api5.yubico.com). If
you ever have a need to override these, you can use `setHosts`:

```php
$v = new \Yubikey\Validate($apiKey, $clientId);
$v->setHosts(array(
    'api.myhost1.com',
    'api1.myhost.com'
));
```
Remember, this will *overwrite* the current hosts in the class, so be sure you don't still need those. If you just want to add
another host, look at the `addHost` method.

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
