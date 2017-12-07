<?php

namespace Songbook\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;

class FrontendController extends AbstractActionController {
    /**
     * Execute the request
     *
     * @param  MvcEvent $e
     * @return mixed
     * @throws \Zend\Mvc\Exception\DomainException
     */
    public function onDispatch(MvcEvent $e)
    {
        $routeMatch = $e->getRouteMatch();
        if (!$routeMatch) {
            /**
             * @todo Determine requirements for when route match is missing.
             *       Potentially allow pulling directly from request metadata?
             */
            throw new \Zend\Mvc\Exception\DomainException('Missing route matches; unsure how to retrieve action');
        }

        $action = $routeMatch->getParam('action', 'not-found');
        $method = static::getMethodFromAction($action);

        if (!method_exists($this, $method)) {
            $method = 'notFoundAction';
        }

        $this->beforeAction();
        $actionResponse = $this->$method();
        $this->afterAction();

        $e->setResult($actionResponse);

        return $actionResponse;
    }

    public function beforeAction()
    {
        // js requirements
        $this->getViewHelper('HeadScript')->appendFile('/assets/bower_components/jquery-ui/jquery-ui.min.js');
        $this->getViewHelper('HeadScript')->appendFile('/assets/bower_components/jquery-impromptu/dist/jquery-impromptu.min.js');
        $this->getViewHelper('HeadScript')->appendFile('/assets/bower_components/jquery-sortable/source/js/jquery-sortable-min.js');
        $this->getViewHelper('HeadLink')->appendStylesheet('/assets/bower_components/components-font-awesome/css/font-awesome.css');
        $this->getViewHelper('HeadLink')->appendStylesheet('/assets/bower_components/jquery-impromptu/dist/jquery-impromptu.min.css');

        $this->getViewHelper('HeadLink')->appendStylesheet('/assets/bower_components/jquery-ui/themes/base/jquery-ui.min.css');

        $this->getViewHelper('HeadScript')->appendFile('/assets/js/songbook-ui.js');
        $this->getViewHelper('HeadScript')->appendFile('/assets/js/app.js');
    }

    public function afterAction()
    {

    }

    /**
     * @param string $helperName
     */
    protected function getViewHelper ($helperName)
    {
        return $this->getServiceLocator()
            ->get('ViewHelperManager')
            ->get($helperName);
    }

}