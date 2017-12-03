<?php

namespace Songbook\Model;

use Zend\Mail as ZendMail;
use Songbook\Entity\Content as ContentEntity;

class Mail {
    public function createContentEmail($to, $subject, $body, $contents, $isEmbedFiles = false, $isFilesRenameAddingCounters = false)
    {
        $mail = new ZendMail\Message();

        $mail->setFrom('roman.denisov@gmail.com');
        $mail->addTo($to);
        $mail->setSubject($subject);

        if($isEmbedFiles){
            foreach($contents as $content){
                /* @var ContentEntity $content */
            }
        }
    }
}