<?php
namespace Songbook\Entity;

class AbstractEntity {
    /**
     * Magic getter to expose protected properties.
     *
     * @param string $property
     * @return mixed
     */
    public function __get($property)
    {
        return $this->$property;
    }

    /**
     * Magic setter to save protected properties.
     *
     * @param string $property
     * @param mixed $value
     */
    public function __set($property, $value)
    {
        $this->$property = $value;
    }

    public function getArrayCopy ()
    {
        return get_object_vars($this);
    }

    public function __call($method, $arguments)
    {
        if(substr($method, 0, 3) == 'get'){
            $property = lcfirst(substr($method, 3));
            if(property_exists($this, $property)){
                return $this->$property;
            } else {
                throw new \Exception(vsprintf('Unknown property: "%s"', array($property)));
            }
        } else {
            $property = $method;
            if(property_exists($this, $property)){
                return $this->$property;
            } else {
                throw new \Exception(vsprintf('Unknown method called: "%s"', array($method)));
            }
        }
    }
}