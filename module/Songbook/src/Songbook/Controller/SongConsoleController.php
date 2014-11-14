<?php
namespace Songbook\Controller;

use Doctrine\ORM\EntityManager;
use Zend\Mvc\Controller\AbstractActionController;

class SongConsoleController extends AbstractActionController {

    /**
     * @var \Sonbook\Model\SongImport
     */
    protected $songImport;

    /**
     * @var \Sonbook\Model\SongService
     */
    protected $songService;


    public function importAction()
    {
        $request = $this->getRequest();
        $isTxt = $request->getParam('txt');
        $isTxtConcert = $request->getParam('txt-concerts');
        $isDb = $request->getParam('db');
        $filename = $request->getParam('filename');

        $songImport = $this->getSongImport();
        if ($isTxt) {
            $retval = $songImport->importTxt($filename);
        } elseif ($isTxtConcert) {
            $retval = $songImport->importTxtConcert($filename);
        } elseif ($isDb) {
            $retval = $songImport->importDb();
        } else {
            throw new \Exception('required arguments is csv|db');
        }
    }

    public function createHeadersAction()
    {
        $songService = $this->getSongService();
        $songService->createHeaders();
    }

    /**
     * @return \Sonbook\Model\SongImport
     */
    protected function getSongImport()
    {
        if (! $this->songImport) {
            $sm = $this->getServiceLocator();
            $this->songImport = $sm->get('Songbook\Model\SongImport');
        }
        return $this->songImport;
    }

    /**
     * @return \Sonbook\Model\SongService
     */
    protected function getSongService()
    {
        if (! $this->songService) {
            $sm = $this->getServiceLocator();
            $this->songService = $sm->get('Songbook\Model\SongService');
        }
        return $this->songService;
    }
}