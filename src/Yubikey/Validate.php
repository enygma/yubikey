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
     * HTTP client for request
     * @var object
     */
    private $client = null;

    /**
     * Use a secure/insecure connection (HTTPS vs HTTP)
     * @var boolean
     */
    private $useSecure = true;

    /**
     * Sync level for request
     * @var integer
     */
    private $syncLevel = 0;

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
     */
    public function __construct($apiKey, $clientId, array $hosts = array())
    {
        $this->setApiKey($apiKey);
        $this->setClientId($clientId);

        if (!empty($hosts)) {
            $this->setHosts($hosts);
        }
    }

    /**
     * Get the currently set API key
     *
     * @return string API key
     */
    public function getApiKey()
    {
        return $this->apiKey;
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
    public function generateSignature($data)
    {
        $key = $this->getApiKey();
        if ($key === null) {
            throw new \InvalidArgumentException('Invalid API key!');
        }

        $hash = preg_replace(
            '/\+/', '%2B',
            base64_encode(hash_hmac('sha1', http_build_query($data), $key, true))
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
        if ($clientId == null) {
            throw new \InvalidArgumentException('Client ID cannot be null');
        }

        $nonce = md5(mt_rand());
        $params = array(
            'id' => $clientId,
            'otp' => trim($otp),
            'nonce' => $nonce,
            'timestamp' => '1'
        );
        ksort($params);

        $signature = $this->generateSignature($params);
        $url = '/wsapi/2.0/verify?'.http_build_query($params).'&h='.$signature;
        $hosts = ($multi == false) ? array(array_shift($this->hosts)) : $this->hosts;
        $c = new \Yubikey\Client();
        $pool = new \Yubikey\RequestCollection();

        // Make the requests for the host(s)
        $prefix = ($this->getUseSecure() === true) ? 'https' : 'http';
        foreach ($hosts as $host) {
            $link = $prefix.'://'.$host.$url;
            $pool->add(new \Yubikey\Request($link));
        }
        $responses = $c->send($pool);

        for ($i = 0; $i < count($responses); $i++) {
            $responses[$i]->setInputOtp($otp)->setInputNonce($nonce);
        }

        return $responses;
    }
}

?>