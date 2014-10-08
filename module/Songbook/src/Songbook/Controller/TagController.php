<?php

namespace Songbook\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Doctrine\ORM\EntityManager;

class TagController extends AbstractActionController
{
    public function addAction()
    {
        return $this->forward()->dispatch('Tag', array('action' => 'edit', 'id' => $this->params('id', null ) ));
    }

    public function editAction()
    {
        $id = $this->params('id', null );
    }
}