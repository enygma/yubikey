<?php

namespace Yubikey;

class Response
{
    /**
     * Returned value of the hash given in the request
     * @var string
     */
    private $h = null;

    /**
     * Timestamp in UTC of request
     * @var string
     */
    private $t = null;

    /**
     * OTP given in request
     * @var string
     */
    private $otp = null;

    /**
     * Nonce given in the request
     * @var string
     */
    private $nonce = null;

    /**
     * Percent of validation servers that replied with "success" (0% - 100%)
     * @var integer
     */
    private $sl = null;

    /**
     * Status of the response (see constants below)
     * @var string
     */
    private $status = null;

    /**
     * OTP given by the user (used for verification)
     * @var string
     */
    private $inputOtp = null;

    /**
     * Nonce used in the request (used for verification)
     * @var string
     */
    private $inputNonce = null;

    /**
     * Define constants for the return status from API
     */
    const SUCCESS = 'OK';
    const REPLAY_OTP = 'REPLAYED_OTP';
    const REPLAY_REQUEST = 'REPLAY_REQUEST';
    const MISSING_PARAMETER = 'MISSING_PARAMETER';
    const NO_CLIENT = 'NO_SUCH_CLIENT';
    const BAD_OTP = 'BAD_OTP';
    const BAD_SIGNATURE = 'BAD_SIGNATURE';
    const OP_NOT_ALLOWED = 'OPERATION_NOT_ALLOWED';
    const BACKEND_ERROR = 'BACKEND_ERROR';
    const NOT_ENOUGH_ANSWERS = 'NOT_ENOUGH_ANSWERS';

    /**
     * Init the object and set the data into the response
     * 
     * @param array $data Data from the Yubi API response
     */
    public function __construct($data = null)
    {
        if ($data !== null) {
            $this->load($data);
        }
    }

    /**
     * Load the data into the object
     * 
     * @param array $data Data from the object
     * @return boolean True when loading is done
     */
    public function load($data)
    {
        foreach ($data as $index => $value) {
            if (property_exists($this, $index)) {
                $this->$index = trim($value);
            }
        }
        return true;
    }

    /**
     * Set the OTP used in the request
     * @param string $otp OTP string (from key)
     */
    public function setInputOtp($otp)
    {
        $this->userOtp = $otp;
        return $this;
    }

    /**
     * Get the OTP used in the request
     * @return string OTP string
     */
    public function getInputOtp()
    {
        return $this->userOtp;
    }

    /**
     * Set the nonce used in the request
     * @param string $nonce Nonce from request
     */
    public function setInputNonce($nonce)
    {
        $this->inputNonce = $nonce;
        return $this;
    }

    /**
     * Get the nonce used in the request
     * @return string Nonce from request
     */
    public function getInputNonce()
    {
        return $this->inputNonce;
    }

    /**
     * Magic method to get access to the private properties
     * @param string $name Property name
     * @return string|null If found, returns teh value. If not, null
     */
    public function __get($name)
    {
        return (property_exists($this, $name)) ? $this->$name : null;
    }

    /**
     * Check the success of the response
     *     Validates: status, OTP and nonce
     * @return boolean Success/fail of request
     */
    public function success()
    {
        $inputOtp = $this->getInputOtp();
        $inputNonce = $this->getInputNonce();

        if ($inputNonce === null || $inputOtp == null) {
            return false;
        }

        return (
            $inputOtp == $this->otp
            && $inputNonce === $this->nonce
            && $this->status === Response::SUCCESS
        ) ? true : false;
    }
}

?>