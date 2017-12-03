<?php


namespace Songbook\Model\Content\Service;

class Exception extends \Exception {
    const CODE_WRONG_SERVICE_NAME = 2;

    const CODE_LINK_DOES_NOT_MATCH_ANY_SERVICE = 3;
}