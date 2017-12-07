<?php

namespace Ez\Api;

use Ez\Api\Response as Response;

class Exception extends \Exception {

    protected $statusCode = 500;

    public function __construct($message, $code = null, $previous = null, $httpCode = 500)
    {
        if($message instanceof \Exception ) {
            $e = $message;
            $message = $e->getMessage();
            $code = $e->getCode();
            $previous = $e;
        }

        $this->message = $message;
        $this->code = $code;
        $this->previous = $previous;
        $this->statusCode = $httpCode;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }
}