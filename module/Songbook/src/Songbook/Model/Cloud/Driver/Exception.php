<?php
/**
 * Created by PhpStorm.
 * User: manro
 * Date: 18.08.17
 * Time: 13:42
 */
namespace Songbook\Model\Cloud\Driver;

class Exception extends \Exception {
    const CODE_UNABLE_TO_INIT_DRIVER = 2;

    const CODE_UNABLE_TO_CREATE_ROOT_FOLDER = 3;

    const CODE_UNABLE_TO_CREATE_FOLDER = 4;

    const CODE_UNABLE_TO_FIND_MIME_TYPE = 5;
}