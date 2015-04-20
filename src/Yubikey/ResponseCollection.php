<?php

namespace Yubikey;

class ResponseCollection implements \Countable, \Iterator, \ArrayAccess
{
    /**
     * Set of \Yubikey\Response objects
     * @var array
     */
    private $responses = array();

    /**
     * Position in the data set (for Iterator)
     * @var integer
     */
    private $position = 0;

    /**
     * Init the object and set the Response objects if provided
     *
     * @param array $responses Response object set
     */
    public function __construct(array $responses = array())
    {
        if (!empty($responses)) {
            foreach ($responses as $response) {
                $this->add($response);
            }
        }
    }

    /**
     * Determine, based on the Response status (success)
     *     if the overall operation was successful
     *
     * @return boolean Success/fail status
     */
    public function success($first = false)
    {
        $success = false;
        if ($first === true) {
            // Sort them by timestamp, pop the first one and return pass/fail
            usort($this->responses, function(\Yubikey\Response $r1, \Yubikey\Response $r2) {
                return $r1->getMt() > $r2->getMt();
            });
            $response = $this->responses[0];
            return $response->success();
        } else {
            foreach ($this->responses as $response) {
                if ($response->success() === false
                    && $response->status !== Response::REPLAY_REQUEST) {
                    return false;
                } elseif ($response->success()) {
                    $success = true;
                }
            }
        }
        return $success;
    }

    /**
     * Add a new Response object to the set
     *
     * @param \Yubikey\Response $response Response object
     */
    public function add(\Yubikey\Response $response)
    {
        $this->responses[] = $response;
    }

        /**
     * For Countable
     *
     * @return integer Count of current Requests
     */
    public function count()
    {
        return count($this->responses);
    }

    /**
     * For Iterator
     *
     * @return Current Request object
     */
    public function current()
    {
        return $this->responses[$this->position];
    }

    /**
     * For Iterator
     *
     * @return integer Current position in set
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * For Iterator
     *
     * @return integer Next positiion in set
     */
    public function next()
    {
        return ++$this->position;
    }

    /**
     * For Iterator, rewind set location to beginning
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * For Iterator, check to see if set item is valid
     *
     * @return boolean Valid/invalid result
     */
    public function valid()
    {
        return isset($this->responses[$this->position]);
    }

    /**
     * For ArrayAccess
     *
     * @param mixed $offset Offset identifier
     * @return boolean Found/not found result
     */
    public function offsetExists($offset)
    {
        return (isset($this->responses[$offset]));
    }

    /**
     * For ArrayAccess
     *
     * @param mixed $offset Offset to locate
     * @return \Yubikey\Request object if found
     */
    public function offsetGet($offset)
    {
        return $this->responses[$offset];
    }

    /**
     * For ArrayAccess
     *
     * @param mixed $offset Offset to use in data set
     * @param mixed $data Data to assign
     */
    public function offsetSet($offset, $data)
    {
        $this->responses[$offset] = $data;
    }

    /**
     * For ArrayAccess
     *
     * @param mixed $offset Offset to remove
     */
    public function offsetUnset($offset)
    {
        unset($this->responses[$offset]);
    }
}
