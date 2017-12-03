<?php

namespace Songbook\Model\Content\Service;

class Factory {
    const SERVICE_YOUTUBE = 'youtube';

    const SERVICE_GODTUBE = 'godtube';

    public static function create($serviceName)
    {
        return new $serviceName();
    }
}