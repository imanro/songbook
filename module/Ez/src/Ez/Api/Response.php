<?php

namespace Ez\Api;
use Zend\View\Model\JsonModel;
use Zend\Http\Response as ZendHttpResponse;

class Response extends ZendHttpResponse {

    public function __construct()
    {
        $this->getHeaders()->addHeaderLine('Content-Type', 'application/json');
    }

    public function prepareStatus($status = 'ok')
    {
        return $this->prepare(
            array(
                'status' => $status,
            ));
    }

    public function prepareData(array $data, $statusCode = self::STATUS_CODE_200)
    {
        $this->setStatusCode($statusCode);

        $data = $this->prepare(
                array(
                    'status' => $statusCode,
                    'data' => $data,
        ));
        $this->setContent($data);
        return $data;
    }

    public function prepareError ($data, $statusCode = self::STATUS_CODE_500)
    {
        $this->setStatusCode($statusCode);

        if( !is_array($data))
        {
            $data = array('message' => $data);
        }

        $data = $this->prepare(
                array(
                    'status' => $statusCode,
                    'data' => $data
                ));
        $this->setContent($data);
        return $data;
    }

    public function prepareException ($message, $code = null, $previous = null, $statusCode)
    {
        $e = new \Ez\Api\Exception($message, $code, $previous, $statusCode);
        throw $e;
    }

    protected function prepare (array $array)
    {
        return new JsonModel($array);
    }
}