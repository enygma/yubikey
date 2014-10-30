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

    /**
     * Test that the getter/setter for the Client ID works correctly
     * @covers \Yubikey\Validate::getClientId
     * @covers \Yubikey\Validate::setClientId
     */
    public function testGetSetClientId()
    {
        $clientId = 12345;

        $this->validate->setClientId($clientId);
        $this->assertEquals($clientId, $this->validate->getClientId());
    }

    /**
     * Test thta the getter/setter for the "use secure" setting works correctly
     * @covers \Yubikey\Validate::setUseSecure
     * @covers \Yubikey\Validate::getUseSecure
     */
    public function testGetSetUseSecure()
    {
        $useSecure = true;

        $this->validate->setUseSecure($useSecure);
        $this->assertEquals($useSecure, $this->validate->getUseSecure());
    }

    /**
     * Test that an exception is thrown when the "use secure" valus
     *     is not boolean
     *
     * @expectedException \InvalidArgumentException
     * @covers \Yubikey\Validate::setUseSecure
     */
    public function testSetUseSecureInvalid()
    {
        $useSecure = 'invalid';
        $this->validate->setUseSecure($useSecure);
    }

    /**
     * Test that the getter/setter for the host works correctly
     * @covers \Yubikey\Validate::setHost
     * @covers \Yubikey\Validate::getHost
     */
    public function testGetSetHost()
    {
        $host = 'test.foo.com';

        $this->validate->setHost($host);
        $this->assertEquals($this->validate->getHost(), $host);
    }

    /**
     * Test that a valid random host is selected if none was previously set
     * @covers \Yubikey\Validate::getHost
     */
    public function testGetRandomHost()
    {
        $host1 = $this->validate->getHost();
        $this->assertNotEquals($host1, null);
    }

    /**
     * Test that the signature generation is valid
     * @covers \Yubikey\Validate::generateSignature
     */
    public function testSignatureGenerate()
    {
        $data = array('foo' => 'bar');
        $key = $this->validate->getApiKey();
        $hash = preg_replace(
            '/\+/', '%2B',
            base64_encode(hash_hmac('sha1', http_build_query($data), $key, true))
        );

        $signature = $this->validate->generateSignature($data);
        $this->assertEquals($hash, $signature);
    }

    /**
     * Test that an exception is thrown when the API is invalid (null or empty)
     * @covers \Yubikey\Validate::generateSignature
     * @expectedException \InvalidArgumentException
     */
    public function testSignatureGenerateNoApiKey()
    {
        $key = null;
        $data = array('foo' => 'bar');
        $validate = new \Yubikey\Validate($key, $this->clientId);
        $hash = preg_replace(
            '/\+/', '%2B',
            base64_encode(hash_hmac('sha1', http_build_query($data), $key, true))
        );

        $signature = $validate->generateSignature($data);
    }

    /**
     * Add a new Host to the list
     * @covers \Yubikey\Validate::addHost
     */
    public function testAddNewHost()
    {
        $this->validate->addHost('test.com');
        $this->assertTrue(
            in_array('test.com', $this->validate->getHosts())
        );
    }

    /**
     * Set the new Hosts list (override)
     * @covers \Yubikey\Validate::setHosts
     * @covers \Yubikey\Validate::getHosts
     */
    public function testSetHosts()
    {
        $hosts = array('foo.com');
        $this->validate->setHosts($hosts);

        $this->assertEquals(
            $this->validate->getHosts(),
            $hosts
        );
    }
}

