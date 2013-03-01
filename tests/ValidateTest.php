<?php

class ValidateTest extends \PHPUnit_Framework_TestCase
{
    private $validate = null;

    private $apiKey = 'dGVzdGluZzEyMzQ1Njc4OTA=';

    private $clientId = 12345;

    public function setUp()
    {
        $this->validate = new \Yubikey\Validate($this->apiKey, $this->clientId);
    }

    /**
     * Test the getter and setter for the API key
     * @covers \Yubikey\Validate::setApiKey
     * @covers \Yubikey\Validate::getApiKey
     */
    public function testGetSetApiKey()
    {
        $preKey = 'testing1234567890';
        $key = base64_encode($preKey);

        $this->validate->setApiKey($key);
        $this->assertEquals($this->validate->getApiKey(), $preKey);
    }

    /**
     * Test the setting of a non-base64 encoded API key
     * @covers \Yubikey\Validate::setApiKey
     * @expectedException \InvalidArgumentException
     */
    public function testSetInvalidApiKey()
    {
        $key = 'testing1234^%$#^#';
        $this->validate->setApiKey($key);
    }

    /**
     * Test the getter and setter for the One-time password
     * @covers \Yubikey\Validate::setOtp
     * @covers \Yubikey\Validate::getOtp
     */
    public function testGetSetOtp()
    {
        $otp = base64_encode('testing1234567890');

        $this->validate->setOtp($otp);
        $this->assertEquals($this->validate->getOtp(), $otp);
    }
}