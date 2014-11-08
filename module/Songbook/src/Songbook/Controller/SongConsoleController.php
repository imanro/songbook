<?php
namespace Songbook\Controller;

use Doctrine\ORM\EntityManager;
use Zend\Mvc\Controller\AbstractActionController;

class SongConsoleController extends AbstractActionController {

    /**
     * @var \Sonbook\Model\SongImport
     */
    protected $songImport;

    public function importAction()
    {
        $request = $this->getRequest();
        $isCsv = $request->getParam('csv');
        $isDb = $request->getParam('db');

        $songImport = $this->getSongImport();

        if ($isCsv) {
            $retval = $songImport->importCsv();
        } elseif ($isDb) {
            $retval = $songImport->importDb();
        } else {
            throw new \Exception('required arguments is csv|db');
        }
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
}