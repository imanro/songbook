<?php

namespace Songbook\Service;

class ContentException extends \Exception {
    const CODE_WRONG_CONTENT_TYPE = 2;

    const CODE_UNABLE_TO_CREATE_TMP_FOLDER = 3;
}