<?php
namespace Ez\Api;
use Zend\Http\Request as ZendHttpRequest;

class Request extends ZendHttpRequest
{

    /**
     *
     * @var \Zend\Http\Request
     */
    protected $request;

    protected $requiredParams = array();

    protected $requiredMethod;

    public function decorate (\Zend\Http\Request $request)
    {
        $this->request = $request;
    }

    protected function getRequest ()
    {
        if ($this->request instanceof \Zend\Http\Request) {
            return $this->request;
        } else {
            throw new \Exception('Request is not yet decorated');
        }
    }

    /**
     * Pass all undefined methods to decorated object
     *
     * @param unknown $method
     * @param unknown $params
     * @return mixed
     */
    public function __call ($method, $params)
    {
        return call_user_func_array(array(
            $this->getRequest(),
            $method
        ), $params);
    }

    /**
     * Note, prams names must be a _keys_ of array
     * @param array $array
     * @return \Ez\Api\Request
     */
    public function setRequiredParams (array $array)
    {
        $this->requiredParams = $array;
        return $this;
    }

    public function getRequiredParams ()
    {
        return $this->requiredParams;
    }

    /**
     * @param string $method
     * @return \Ez\Api\Request
     */
    public function setRequiredMethod ($method = \Zend\Http\Request::METHOD_POST)
    {
        $this->requiredMethod = $method;
        return $this;
    }

    public function getRequiredMethod ()
    {
        return $this->requiredMethod;
    }

    public function validate ()
    {
        $params = $this->getParams();

        foreach ($this->getRequiredParams() as $name => $value) {
            if (! isset($params[$name])) {
                throw new \Ez\Api\Exception(
                        sprintf(
                                'request validation failed: {%s} is required param',
                                $name));
            }
        }

        return true;
    }

    public function getParams ()
    {
        if ($this->getRequiredMethod() == \Zend\Http\Request::METHOD_POST) {
            return $this->getRequest()->getPost();
        } else {
            return $this->getRequest()->getQuery();
        }
    }

    public function getParam ($name, $default = null)
    {
        if ($this->getRequiredMethod() == \Zend\Http\Request::METHOD_POST) {
            $params = $this->getRequest()->getPost();
        } else {
            $params = $this->getRequest()->getQuery();
        }

        if (isset($params[$name])) {
            return $params[$name];
        } else {
            return $default;
        }
    }
}
