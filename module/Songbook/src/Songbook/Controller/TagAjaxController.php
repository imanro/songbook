<?php

namespace Songbook\Controller;

use Ez\Api\Controller;
use Ez\Api\Request;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Doctrine\ORM\EntityManager;

class TagAjaxController extends Controller
{
    public function indexAction()
    {
    }

    public function searchAction()
    {
        $request = $this->getRequest();
        $request->setRequiredMethod(Request::METHOD_POST);
        $request->setRequiredParams(array('term' => true));
        $request->validate();

        $name = $this->getRequest()->getParam('term');
        return $response = $this->getResponse()->prepareError('missing something');
    }
}