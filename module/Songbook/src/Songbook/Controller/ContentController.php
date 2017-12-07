<?php
namespace Songbook\Controller;

/**
* @author manro
*/
class ContentController extends FrontendController {

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Songbook\Service\Song
     */
    protected $songService;

    /**
     * @var \Songbook\Service\Content
     */
    protected $contentService;

    /**
     * @var \Songbook\Service\Concert
     */
    protected $concertService;

    /**
     * @var \Songbook\Service\Mail
     */
    protected $mailService;

    public function emailComposeAction(){
        // get ids of all contents
        $contentIds = trim($this->params()->fromQuery('content_ids'));
        $concertId = $this->params()->fromQuery('concert_id');

        $concertService = $this->getConcertService();
        $concert = $concertService->getConcertById($concertId);

        if($contentIds !== ''){
            $contentIds = explode(',', $contentIds);
            array_walk($contentIds, function(&$id){
                $id = (int)trim($id);
            });
        } else {
            $contentIds = array();
        }

        // display edit page;

        // get content
        $contentService = $this->getContentService();

        $contents = $contentService->getSongContentsByIds($contentIds);
        $contentsOrdered = $contentService->orderContentsByIds($contents, $contentIds);

        return array(
            'contents' => $contentsOrdered,
            'contentService' => $contentService,
            'concert' => $concert,
            'concertService' => $concertService
        );
    }

    public function emailSendAction() {

        // create email
        $formData = $this->params()->fromPost();

        $contentService = $this->getContentService();

        if(!empty($formData['content'] ) && count($formData['content']) > 0){
            $contents = $contentService->getSongContentsByIds($formData['content']);
        } else {
            $contents = array();
        }

        if(!isset($formData['is_embed_content'])){
            $formData['is_embed_content'] = false;
        }

        if(!isset($formData['is_add_counter_to_names'])){
            $formData['is_add_counter_to_names'] = false;
        }

        $contentsOrdered = $contentService->orderContentsByIds($contents, $formData['content']);

        // collecting contents
        $mailService = $this->getMailService();

        // create email
        $mail = $mailService->createContentEmail($formData['mail_to'], $formData['mail_subject'], $formData['mail_body'], $contentsOrdered, (bool)$formData['is_embed_content'], (bool)$formData['is_add_counter_to_names']);

        try {
            $mailService->sendMail($mail);
        } catch(\Exception $e){
            $string = $e->getMessage();
            if(strpos($string, '5.7.14') !== false){
                if(preg_match('/<(.+)>/si', $string, $matches)){
                    //$link = str_replace('5.7.14 ', '', $matches[1]);
                    $link = preg_replace('/(5.7.14 |\r|\n)/', '', $matches[1]);
                    echo 'Please, open <a href="' . $link . '">' . $link . '</a>';
                }
            }
        }

        // TODO
        // set flash + redirect

        // redirect

        exit;
    }

    public function pdfCompileAction() {
        $contentIds = trim($this->params()->fromQuery('content_ids'));
        $concertId = trim($this->params()->fromQuery('concert_id'));

        $concertService = $this->getConcertService();
        $concert = $concertService->getConcertById($concertId);

        ob_start();
        if ($contentIds !== '') {
            $contentIds = explode(',', $contentIds);
            array_walk($contentIds, function (&$id) {
                $id = (int)trim($id);
            });
        } else {
            $contentIds = array();
        }

        $contentService = $this->getContentService();
        $contents = $contentService->getSongContentsByIds($contentIds);
        $contentsOrdered = $contentService->orderContentsByIds($contents, $contentIds);

        $pdf = $contentService->pdfCompile($contentsOrdered);

        ob_clean();

        header('Content-Type: application/pdf');
        header('Content-Description: File Transfer');
        header('Content-Disposition: inline; filename="revival.l.' . date('Y-m-d', $concert->time) . '"');
        header('Content-Transfer-Encoding: binary');
        header('Connection: Keep-Alive');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . mb_strlen($pdf, '8bit'));

        echo $pdf;
        exit;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager ()
    {
        if (null === $this->em) {
            $this->em = $this->getServiceLocator()->get(
                'doctrine.entitymanager.orm_default');
        }

        return $this->em;
    }

    /**
     * @return \Songbook\Service\Song
     */
    protected function getSongService ()
    {
        if (! $this->songService) {
            $sm = $this->getServiceLocator();
            $this->songService = $sm->get('Songbook\Service\Song');
        }
        return $this->songService;
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

    /**
     * @return \Songbook\Service\Concert
     */
    protected function getConcertService ()
    {
        if (! $this->concertService) {
            $sm = $this->getServiceLocator();
            $this->concertService = $sm->get('Songbook\Service\Concert');
        }

        return $this->concertService;
    }

    /**
     * @return \Songbook\Service\Mail
     */
    protected function getMailService ()
    {
        if (! $this->mailService) {
            $sm = $this->getServiceLocator();
            $this->mailService = $sm->get('Songbook\Service\Mail');
        }
        return $this->mailService;
    }

}
