<?php

namespace Songbook\Model\Content\Service;

class Pool {
    const SERVICE_YOUTUBE = 'youtube';

    const SERVICE_GODTUBE = 'godtube';

    public static $instances = array();

    /**
     * @return AbstractService|null
     */
    protected static function create($serviceName)
    {
        $className = '\Songbook\Model\Content\Service\\' . ucfirst($serviceName);
        if(class_exists($className)){
            return new $className();
        } else {
            return null;
        }
    }

    /**
     * @return AbstractService|null
     */
    public static function get($serviceName)
    {
        if (!isset(self::$instances[$serviceName])) {
            $service = self::create($serviceName);
            if ($service) {
                self::$instances[$serviceName] = $service;
            } else {
                return null;
            }
        }

        return self::$instances[$serviceName];
    }
}