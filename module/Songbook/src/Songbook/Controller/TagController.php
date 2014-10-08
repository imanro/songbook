<?php

namespace Songbook\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Doctrine\ORM\EntityManager;

class TagController extends AbstractActionController
{
    public function get()
    {
        $name = $this->getRequest()->getPost('name');
        // search such tags, limit 10
    }
}