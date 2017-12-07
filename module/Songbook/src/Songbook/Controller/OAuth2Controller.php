<?php
/**
 * Created by PhpStorm.
 * User: manro
 * Date: 30.06.17
 * Time: 13:41
 */
namespace Songbook\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\Session\Container;

class OAuth2Controller extends AbstractActionController
{
    public function gdriveAction()
    {
        $config = $this->getServiceLocator()->get('Config');
        $container = new Container($config['cloud']['gdrive']['session_ns']);
        var_dump($container->gdrive_return_uri);

        $cloud = $this->getServiceLocator()->get('Songbook\Model\Cloud');
        /* @var $cloud \Songbook\Model\Cloud */

        $request = $this->getRequest();

        $cloud->oauth2Authenticate($request->getQuery('code'));
        return new JsonModel();
    }
}