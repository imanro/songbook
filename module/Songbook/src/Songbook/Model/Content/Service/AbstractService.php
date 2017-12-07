<?php

namespace Songbook\Model\Content\Service;

use Songbook\Entity\Content as ContentEntity;

abstract class AbstractService {
    abstract public function getEmbedLink(ContentEntity $content);

    abstract public function getEmbedCode(ContentEntity $content);

    public function getParams($params = null)
    {
        $defaultParams = array(
            'width' => 380,
            'height' => 285
        );

        if(is_array($params)){
            return array_merge($defaultParams, $params);
        } else {
            return $defaultParams;
        }
    }
}