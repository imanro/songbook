<?php

namespace Songbook\Model\Content\Service;

use Songbook\Entity\Content as ContentEntity;

class Youtube extends AbstractService {
    public function getEmbedCode(ContentEntity $content)
    {
        $re = '/watch\?v=([^&]+)/';
        if(preg_match($re, $content->content, $matches)){
            return 'https://www.youtube.com/embed/' . $matches[1];
        } else {
            return false;
        }
    }

    public function getEmbedLink(ContentEntity $content)
    {
        $code = $this->getEmbedCode($content->content);
        if($code) {
            return <<<EOD
<iframe width="400" height="300" src="$code" frameborder="0" allowfullscreen></iframe>
EOD;
        } else {
            return false;
        }
    }
}
