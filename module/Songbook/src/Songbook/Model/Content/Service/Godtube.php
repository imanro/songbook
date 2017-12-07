<?php

namespace Songbook\Model\Content\Service;

use Songbook\Entity\Content as ContentEntity;

class Godtube extends AbstractService {
    public function getEmbedLink(ContentEntity $content)
    {
        $re = '/watch\/\?v=([^&]+)/';
        if(preg_match($re, $content->content, $matches)){
            return 'http://www.godtube.com/embed/source/' . $matches[1] . '.js';
        } else {
            return false;
        }
    }

    public function getEmbedCode(ContentEntity $content, $params = null)
    {
        $link = $this->getEmbedLink($content);
        $params = $this->getParams($params);
        $width = $params['width'];
        $height = $params['height'];

        if($link) {
            return <<<EOD
<script type="text/javascript" src="{$link}?w=${width}&h=${height}&ap=false&sl=false"></script>
EOD;
        } else {
            return false;
        }
    }
}
