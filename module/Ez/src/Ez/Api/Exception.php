<?php

namespace Ez\Api;

use Ez\Api\Response as Response;

class Exception extends \Exception {

    public function __construct($message, $code = null, $previous = null)
    {
        $response = new Response();
        $model = $response->prepareException($message, $code, $previous);
        die( $model->serialize() );
    }
}