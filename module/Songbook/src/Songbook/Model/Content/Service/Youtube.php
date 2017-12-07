<?php

namespace Songbook\Model\Content\Service;

use Songbook\Entity\Content as ContentEntity;

class Youtube extends AbstractService {
    public function getEmbedLink(ContentEntity $content)
    {
        $re = '/watch\?v=([^&]+)/';
        if(preg_match($re, $content->content, $matches)){
            return 'https://www.youtube.com/embed/' . $matches[1];
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
<iframe width="${width}" height="${height}" src="${link}" frameborder="0" allowfullscreen></iframe>
EOD;
        } else {
            return false;
        }
    }
}
