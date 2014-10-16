<?php

namespace Yubikey;

class RequestCollection implements \Countable, \Iterator, \ArrayAccess
{
    /**
     * Set of \Yubikey\Request objects
     * @var array
     */
    private $requests = array();

    /**
     * Current array position
     * @var integer
     */
    private $position = 0;

    /**
     * Init the collection and set requests data if given
     *
     * @param array $requests Set of \Yubikey\Requests objects
     */
    public function __construct(array $requests = array())
    {
        if (!empty($requests)) {
            foreach ($requests as $request) {
                $this->add($request);
            }
        }
    }

    /**
     * Add the given request to the set
     *
     * @param \Yubikey\Request $request Request object
     */
    public function add(\Yubikey\Request $request)
    {
        $this->requests[] = $request;
    }

    /**
     * For Countable
     *
     * @return integer Count of current Requests
     */
    public function count()
    {
        return count($this->requests);
    }

    /**
     * For Iterator
     *
     * @return Current Request object
     */
    public function current()
    {
        return $this->requests[$this->position];
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
        return isset($this->requests[$this->position]);
    }

    /**
     * For ArrayAccess
     *
     * @param mixed $offset Offset identifier
     * @return boolean Found/not found result
     */
    public function offsetExists($offset)
    {
        return (isset($this->requests[$offset]));
    }

    /**
     * For ArrayAccess
     *
     * @param mixed $offset Offset to locate
     * @return \Yubikey\Request object if found
     */
    public function offsetGet($offset)
    {
        return $this->requests[$offset];
    }

    /**
     * For ArrayAccess
     *
     * @param mixed $offset Offset to use in data set
     * @param mixed $data Data to assign
     */
    public function offsetSet($offset, $data)
    {
        $this->requests[$offset] = $data;
    }

    /**
     * For ArrayAccess
     *
     * @param mixed $offset Offset to remove
     */
    public function offsetUnset($offset)
    {
        unset($this->requests[$offset]);
    }
}