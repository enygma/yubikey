<?php

namespace Yubikey;

class Request
{
    /**
     * Request HTTP type (verb)
     * @var string
     */
    private $type = 'GET';

    /**
     * Request URL location
     * @var string
     */
    private $url;

    /**
     * Init the object and set the URL if given
     *
     * @param string $url URL to request
     */
    public function __construct($url = null)
    {
        if ($url !== null) {
            $this->setUrl($url);
        }
    }

    /**
     * Get the type of request
     *
     * @return string HTTP verb type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the type of the request
     *
     * @param string $type HTTP verb type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get the current request URL location
     *
     * @return string URL location
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set the URL location for the request
     *
     * @param string $url URL location
     */
    public function setUrl($url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL) !== $url) {
            throw new \Exception('Invalid URL: '.$url);
        }
        $this->url = $url;
    }
}