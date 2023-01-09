<?php

use Yubikey\Validate;

/**
 * @internal
 *
 * @coversNothing
 */
final class ValidateTest extends \PHPUnit\Framework\TestCase
{
    private ?Validate $validate = null;

    private string $apiKey = 'dGVzdGluZzEyMzQ1Njc4OTA=';

    private int $clientId = 12345;

    protected function setUp(): void
    {
        $this->validate = new Validate($this->apiKey, $this->clientId);
    }

    /**
     * Test the getter and setter for the API key.
     *
     * @covers \Yubikey\Validate::getApiKey
     * @covers \Yubikey\Validate::setApiKey
     */
    public function testGetSetApiKey(): void
    {
        $preKey = 'testing1234567890';
        $key = base64_encode($preKey);

        $this->validate->setApiKey($key);
        static::assertSame($this->validate->getApiKey(), $preKey);
    }

    /**
     * Test the setting of a non-base64 encoded API key.
     *
     * @covers \Yubikey\Validate::setApiKey
     */
    public function testSetInvalidApiKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $key = 'testing1234^%$#^#';
        $this->validate->setApiKey($key);
    }

    /**
     * Test the getter and setter for the One-time password.
     *
     * @covers \Yubikey\Validate::getOtp
     * @covers \Yubikey\Validate::setOtp
     */
    public function testGetSetOtp(): void
    {
        $otp = base64_encode('testing1234567890');

        $this->validate->setOtp($otp);
        static::assertSame($this->validate->getOtp(), $otp);
    }

    /**
     * Test that the getter/setter for the Client ID works correctly.
     *
     * @covers \Yubikey\Validate::getClientId
     * @covers \Yubikey\Validate::setClientId
     */
    public function testGetSetClientId(): void
    {
        $clientId = 12345;

        $this->validate->setClientId($clientId);
        static::assertSame($clientId, $this->validate->getClientId());
    }

    /**
     * Test that the getter/setter for the "use secure" setting works correctly.
     *
     * @covers \Yubikey\Validate::getUseSecure
     * @covers \Yubikey\Validate::setUseSecure
     */
    public function testGetSetUseSecure(): void
    {
        $useSecure = true;

        $this->validate->setUseSecure($useSecure);
        static::assertSame($useSecure, $this->validate->getUseSecure());
    }

    /**
     * Test that an exception is thrown when the "use secure" values
     *     is not boolean.
     *
     * @covers \Yubikey\Validate::setUseSecure
     */
    public function testSetUseSecureInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $useSecure = 'invalid';
        $this->validate->setUseSecure($useSecure);
    }

    /**
     * Test that the getter/setter for the host works correctly.
     *
     * @covers \Yubikey\Validate::getHost
     * @covers \Yubikey\Validate::setHost
     */
    public function testGetSetHost(): void
    {
        $host = 'test.foo.com';

        $this->validate->setHost($host);
        static::assertSame($this->validate->getHost(), $host);
    }

    /**
     * Test that a valid random host is selected if none was previously set.
     *
     * @covers \Yubikey\Validate::getHost
     */
    public function testGetRandomHost(): void
    {
        $host1 = $this->validate->getHost();
        static::assertNotSame($host1, null);
    }

    /**
     * Test that the signature generation is valid.
     *
     * @covers \Yubikey\Validate::generateSignature
     */
    public function testSignatureGenerate(): void
    {
        $data = ['foo' => 'bar'];
        $key = $this->validate->getApiKey();
        $hash = preg_replace(
            '/\+/',
            '%2B',
            base64_encode(hash_hmac('sha1', http_build_query($data), $key, true))
        );

        $signature = $this->validate->generateSignature($data);
        static::assertSame($hash, $signature);
    }

    /**
     * Test that an exception is thrown when the API is invalid (null or empty).
     *
     * @covers \Yubikey\Validate::generateSignature
     */
    public function testSignatureGenerateNoApiKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $key = null;
        $data = ['foo' => 'bar'];
        $validate = new Validate($key, $this->clientId);
        $hash = preg_replace(
            '/\+/',
            '%2B',
            base64_encode(hash_hmac('sha1', http_build_query($data), $key, true))
        );

        $signature = $validate->generateSignature($data);
    }

    /**
     * Add a new Host to the list.
     *
     * @covers \Yubikey\Validate::addHost
     */
    public function testAddNewHost(): void
    {
        $this->validate->addHost('test.com');
        static::assertTrue(
            in_array('test.com', $this->validate->getHosts(), true)
        );
    }

    /**
     * Set the new Hosts list (override).
     *
     * @covers \Yubikey\Validate::getHosts
     * @covers \Yubikey\Validate::setHosts
     */
    public function testSetHosts(): void
    {
        $hosts = ['foo.com'];
        $this->validate->setHosts($hosts);

        static::assertSame(
            $this->validate->getHosts(),
            $hosts
        );
    }
}
