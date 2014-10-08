<?php

namespace Songbook\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Doctrine\ORM\EntityManager;

class TagAjaxController extends AbstractActionController
{
    public function indexAction()
    {
    }

    public function searchAction()
    {
        $name = $this->getRequest()->getPost('name');
            // search such tags, limit 10
        $result = new JsonModel(array(
            'some_parameter' => 'some value',
            'success' => true
        ));

        return $result;
    }
}