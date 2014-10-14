<?php
namespace Ez\Api;

use Zend\Mvc\Controller\AbstractController;
use Zend\Mvc\MvcEvent;

class Controller extends AbstractController
{

    public function dispatch (\Zend\Stdlib\RequestInterface $request,
            \Zend\Stdlib\ResponseInterface $response = NULL)
    {
        $apiRequest = new \Ez\Api\Request();
        $apiRequest->decorate($request);

        $apiResponse = new \Ez\Api\Response();

        $this->request = $apiRequest;
        $this->response = $apiResponse;

        $e = $this->getEvent();
        $e->setRequest($apiRequest)
            ->setResponse($apiResponse)
            ->setTarget($this);

        $result = $this->getEventManager()->trigger(MvcEvent::EVENT_DISPATCH,
                $e,
                function  ($test)
                {
                    return ($test instanceof \Ez\Api\Response);
                });

        if ($result->stopped()) {
            return $result->last();
        }

        return $e->getResult();
    }

    /**
     * Execute the request
     *
     * @param MvcEvent $e
     * @return mixed
     * @throws Exception\DomainException
     */
    public function onDispatch (MvcEvent $e)
    {
        $routeMatch = $e->getRouteMatch();
        if (! $routeMatch) {
            /**
             *
             * @todo Determine requirements for when route match is missing.
             *       Potentially allow pulling directly from request metadata?
             */
            throw new Exception\DomainException(
                    'Missing route matches; unsure how to retrieve action');
        }

        $action = $routeMatch->getParam('action', 'not-found');
        $method = static::getMethodFromAction($action);

        if (! method_exists($this, $method)) {
            $method = 'notFoundAction';
        }

        $actionResponse = $this->$method();
        $e->setResult($actionResponse);
        return $actionResponse;
    }
}