<?php
namespace Songbook\Entity;

use Zend\Stdlib\JsonSerializable;

class AbstractEntity implements JsonSerializable {
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

    public function jsonSerialize($processedIds = null)
    {
        $array = array();

        if(is_null($processedIds)){
            $processedIds = array(spl_object_hash($this) => true);
        } else {
            $processedIds[spl_object_hash($this)] = true;
        }

        foreach(get_object_vars($this) as $key => $value){
            if(is_scalar($value) || is_array($value)){
                $array[$key] = $value;
            } elseif($value instanceof AbstractEntity) {
                if(!isset($processedIds[spl_object_hash($value)])) {
                    $array[$key] = $value->jsonSerialize($processedIds);
                }
            } else {
                continue;
            }
        }

        return $array;
    }
}