<?php

namespace Yubikey;

class Validate
{
    /**
     * Yubico API hosts
     * @var array
     */
    private $hosts = array(
        'api.yubico.com',
        'api2.yubico.com',
        'api3.yubico.com',
        'api4.yubico.com',
        'api5.yubico.com'
    );

    /**
     * Selected hosted for request
     * @var string
     */
    private $host = null;

    /**
     * API given for request
     * @var string
     */
    private $apiKey = null;

    /**
     * Use a secure/insecure connection (HTTPS vs HTTP)
     * @var boolean
     */
    private $useSecure = true;

    /**
     * OTP provided by user
     * @var string
     */
    private $otp = null;

    /**
     * Init the object and set the API key, Client ID and optionally hosts
     *
     * @param string $apiKey API Key
     * @param string $clientId Client ID
     * @param array $hosts Set of hostnames (overwrites current)
     * @throws \DomainException If curl is not enabled
     */
    public function __construct($apiKey, $clientId, array $hosts = array())
    {
        if ($this->checkCurlSupport() === false) {
            throw new \DomainException('cURL support is required and is not enabled!');
        }

        $this->setApiKey($apiKey);
        $this->setClientId($clientId);

        if (!empty($hosts)) {
            $this->setHosts($hosts);
        }
    }

    /**
     * Check for enabled curl support (requirement)
     *
     * @return boolean Enabled/not found flag
     */
    public function checkCurlSupport()
    {
        return (function_exists('curl_init'));
    }

    /**
     * Get the currently set API key
     *
     * @return string API key
     */
    public function getApiKey($decoded = false)
    {
        return ($decoded === false) ? $this->apiKey : base64_decode($this->apiKey);
    }

    /**
     * Set the API key
     *
     * @param string $apiKey API request key
     */
    public function setApiKey($apiKey)
    {
        $key = base64_decode($apiKey, true);
        if ($key === false) {
            throw new \InvalidArgumentException('Invalid API key');
        }

        $this->apiKey = $key;
        return $this;
    }

    /**
     * Set the OTP for the request
     *
     * @param string $otp One-time password
     */
    public function setOtp($otp)
    {
        $this->otp = $otp;
        return $this;
    }

    /**
     * Get the currently set OTP
     *
     * @return string One-time password
     */
    public function getOtp()
    {
        return $this->otp;
    }

    /**
     * Get the current Client ID
     *
     * @return integer Client ID
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Set the current Client ID
     *
     * @param integer $clientId Client ID
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
        return $this;
    }

    /**
     * Get the "use secure" setting
     *
     * @return boolean Use flag
     */
    public function getUseSecure()
    {
        return $this->useSecure;
    }

    /**
     * Set the "use secure" setting
     *
     * @param boolean $use Use/don't use secure
     * @throws \InvalidArgumentException when value is not boolean
     */
    public function setUseSecure($use)
    {
        if (!is_bool($use)) {
            throw new \InvalidArgumentException('"Use secure" value must be boolean');
        }
        $this->useSecure = $use;
        return $this;
    }

    /**
     * Get the host for the request
     *     If one is not set, it returns a random one from the host set
     *
     * @return string Hostname string
     */
    public function getHost()
    {
        if ($this->host === null) {
            // pick a "random" host
            $host = $this->hosts[mt_rand(0,count($this->hosts)-1)];
            $this->setHost($host);
            return $host;
        } else {
            return $this->host;
        }
    }

    /**
     * Get the current hosts list
     *
     * @return array Hosts list
     */
    public function getHosts()
    {
        return $this->hosts;
    }

    /**
     * Set the API host for the request
     *
     * @param string $host Hostname
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Add a new host to the list
     *
     * @param string $host Hostname to add
     */
    public function addHost($host)
    {
        $this->hosts[] = $host;
        return $this;
    }

    /**
     * Set the hosts to request results from
     *
     * @param array $hosts Set of hostnames
     */
    public function setHosts(array $hosts)
    {
        $this->hosts = $hosts;
    }

    /**
     * Geenrate the signature for the request values
     *
     * @param array $data Data for request
     * @throws \InvalidArgumentException when API key is invalid
     * @return Hashed request signature (string)
     */
    public function generateSignature($data, $key = null)
    {
        if ($key === null) {
            $key = $this->getApiKey();
            if ($key === null || empty($key)) {
                throw new \InvalidArgumentException('Invalid API key!');
            }
        }

        $query = http_build_query($data);
        $query = utf8_encode(str_replace('%3A', ':', $query));

        $hash = preg_replace(
            '/\+/', '%2B',
            // base64_encode(hash_hmac('sha1', http_build_query($data), $key, true))
            base64_encode(hash_hmac('sha1', $query, $key, true))
        );
        return $hash;
    }

    /**
     * Check the One-time Password with API request
     *
     * @param string $otp One-time password
     * @param integer $clientId Client ID for API
     * @throws \InvalidArgumentException when OTP length is invalid
     * @return \Yubikey\Response object
     */
    public function check($otp, $multi = false)
    {
        $otp = trim($otp);
        if (strlen($otp) < 32 || strlen($otp) > 48) {
            throw new \InvalidArgumentException('Invalid OTP length');
        }

        $clientId = $this->getClientId();
        if ($clientId === null) {
            throw new \InvalidArgumentException('Client ID cannot be null');
        }

        $nonce = $this->generateNonce();
        $params = array(
            'id' => $clientId,
            'otp' => $otp,
            'nonce' => $nonce,
            'timestamp' => '1'
        );
        ksort($params);

        $url = '/wsapi/2.0/verify?'.http_build_query($params).'&h='.$this->generateSignature($params);
        $hosts = ($multi === false) ? array(array_shift($this->hosts)) : $this->hosts;

        return $this->request($url, $hosts, $otp, $nonce);
    }

    /**
     * Generate a good nonce for the request
     *
     * @return string Generated hash
     */
    public function generateNonce()
    {
        if (function_exists('openssl_random_pseudo_bytes') === true) {
            $hash = md5(openssl_random_pseudo_bytes(32));
        } else {
            $hash = md5(uniqid(mt_rand()));
        }
        return $hash;
    }

    /**
     * Make the request(s) to the Yubi server(s)
     *
     * @param string $url URL for request
     * @param array $hosts Set of hosts to request
     * @param string $otp One-time password string
     * @param string $nonce Generated nonce
     * @return array Set of responses
     */
    public function request($url, array $hosts, $otp, $nonce)
    {
        $client = new \Yubikey\Client();
        $pool = new \Yubikey\RequestCollection();

        // Make the requests for the host(s)
        $prefix = ($this->getUseSecure() === true) ? 'https' : 'http';
        foreach ($hosts as $host) {
            $link = $prefix.'://'.$host.$url;
            $pool->add(new \Yubikey\Request($link));
        }
        $responses = $client->send($pool);
        $responseCount = count($responses);

        for ($i = 0; $i < $responseCount; $i++) {
            $responses[$i]->setInputOtp($otp)->setInputNonce($nonce);

            if ($this->validateResponseSignature($responses[$i]) === false) {
                unset($responses[$i]);
            }
        }

        return $responses;
    }

    /**
     * Validate the signature on the response
     *
     * @param  \Yubikey\Response $response Response instance
     * @return boolean Pass/fail status of signature validation
     */
    public function validateResponseSignature(\Yubikey\Response $response)
    {
        $params = array();
        foreach ($response->getProperties() as $property) {
            $value = $response->$property;
            if ($value !== null) {
                $params[$property] = $value;
            }
        }
        ksort($params);

        $signature = $this->generateSignature($params);
        return $this->hash_equals($signature, $response->getHash(true));
    }

    /**
     * Polyfill PHP 5.6.0's hash_equals() feature
     */
    public function hash_equals($a, $b)
    {
        if (\function_exists('hash_equals')) {
            return \hash_equals($a, $b);
        }
        if (\strlen($a) !== \strlen($b)) {
            return false;
        }
        $res = 0;
        $len = \strlen($a);
        for ($i = 0; $i < $len; ++$i) {
            $res |= \ord($a[$i]) ^ \ord($b[$i]);
        }
        return $res === 0;
    }
}
