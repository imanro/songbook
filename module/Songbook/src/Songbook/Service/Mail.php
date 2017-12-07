<?php

namespace Songbook\Service;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Mail as ZendMail;
use Zend\Mime as ZendMime;
use Songbook\Entity\Content as ContentEntity;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;

class Mail {

    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface;
     */
    protected $sl;

    /**
     * @var \Songbook\Service\Content
     */
    public $contentService;

    public function createContentEmail($to, $subject, $body, array $contents, $isEmbedFiles = false, $isFilesRenameAddingCounters = false)
    {
        $mail = new ZendMail\Message();

        $mail->setFrom('roman.denisov@gmail.com');

        $tos = explode(',', $to);
        array_walk($tos, function(&$value){ $value = trim($value); });

        foreach($tos as $to){
            $mail->addTo($to);
        }

        $mail->setSubject($subject);

        $mimeMessage = new ZendMime\Message();

        $contentService = $this->getContentService();

        $processedContent = array();
        $attachments = array();

        if($isEmbedFiles){


            $rootFolderFsPath = $this->getServiceLocator()->get('Config')['paths']['tmp'];
            $folderFsPath = $rootFolderFsPath . '/' . 'cloudFiles_' . date('Y-m-j-H-i-s') . '_' . uniqid();

            if(!file_exists($folderFsPath)){
                if(mkdir($folderFsPath)){
                    ;
                } else {
                    throw new MailException(vsprintf('Unable to create temporary folder "%s" for downloading cloud files', array($folderFsPath)), MailException::CODE_UNABLE_TO_CREATE_TMP_FOLDER);
                }
            }

            $successCounter = 0;

            foreach($contents as $content){
                /* @var ContentEntity $content */
                if(($fsPath = $contentService->downloadCloudContent($content, $folderFsPath)) !== false){

                    // attach to email
                    $processedContent[$content->id] = true;

                    // creating attachments and add its to mimeMessage
                    $filename = $fsPath;

                    $attachment = new ZendMime\Part(fopen($filename, "rb"));
                    $attachment->type = $content->mime_type;

                    $fileName = preg_replace('/^\d+\.\s*/', '', $content->file_name);

                    $attachment->filename = ($isFilesRenameAddingCounters? ($successCounter + 1) . '. ' : '') . $fileName;
                    $attachment->disposition = ZendMime\Mime::DISPOSITION_ATTACHMENT;
                    $attachment->encoding = ZendMime\Mime::ENCODING_BASE64;

                    $attachments []= $attachment;

                    $successCounter++;
                }
            }

            //array_map('unlink', glob("${folderFsPath}/*"));
            //rmdir($folderFsPath);
        }

        // add unprocessed parts to body
        foreach($contents as $content){
            /* @var ContentEntity $content */
            if(!isset($processedContent[$content->id])){
                switch($content->type){
                    case(ContentEntity::TYPE_LINK):
                    default:
                        $body .= "\n\n" . $content->content;
                        break;
                    case(ContentEntity::TYPE_GDRIVE_CLOUD_FILE):
                        $body .= "\n\n" . 'https://drive.google.com/open?id=' . $content->content;
                        break;
                }
            }
        }

        $textPart = new ZendMime\Part($body);
        $textPart->type = ZendMime\Mime::TYPE_TEXT;
        $textPart->charset = 'utf-8';

        $mimeMessage->setParts(array_merge(array($textPart), $attachments));
        $mail->setBody($mimeMessage);

        //var_dump($mail);
        //exit;
        return $mail;
    }

    public function sendMail(ZendMail\Message $mail)
    {
        $transport = new SmtpTransport();

        $options = new SmtpOptions(array(
            'host' => 'smtp.gmail.com',
            'connection_class' => 'login',
            'connection_config' => array(
                'ssl' => 'tls',
                'username' => 'roman.denisov@gmail.com',
                'password' => 'wm91rn2maC',
            ),
            'port' => 587,
        ));

        $transport->setOptions($options);
        $transport->send($mail);
    }

    public function setServiceLocator (ServiceLocatorInterface $sl)
    {
        $this->sl = $sl;
    }

    public function getServiceLocator ()
    {
        return $this->sl;
    }

    /**
     * @return \Songbook\Service\Content
     */
    protected function getContentService ()
    {
        if (is_null($this->contentService)){
            $sm = $this->getServiceLocator();
            $this->contentService = $sm->get('Songbook\Service\Content');
        }

        return $this->contentService;
    }

}