<?php

namespace Ez\Api;
use Zend\View\Model\JsonModel;
use Zend\Http\Response as ZendHttpResponse;

class Response extends ZendHttpResponse {

    public function prepareData(array $data)
    {
        return $this->prepare(
                array(
                    'status' => 'ok',
                    'data' => $data,
        ));
    }

    public function prepareError ($data)
    {
        if( !is_array($data))
        {
            $data = array('message' => $data);
        }

        return $this->prepare(
                array(
                    'status' => 'error',
                    'data' => $data
                ));
    }

    public function prepareException ($message, $code = null, $previous = null)
    {
        $array = array(
            'status' => 'exception',
            'message' => $message
        );

        if( !is_null($code)){
            $array['code'] = $code;
        }

         if( !is_null($previous)){
            $array['previous'] = $previous;
        }

        return $this->prepare($array);
    }

    protected function prepare (array $array)
    {
        return new JsonModel($array);
    }
}