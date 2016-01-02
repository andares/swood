<?php

/*
 * license less
 */

namespace Swood\Launcher\App\Protocol;

/**
 * Description of ResponseHeader
 *
 * @author andares
 */
class ResponseHeader extends \Swood\Protocol\Header {
    protected static $_schema = ['timestamp' => 0, 'error' => 1];

    protected $_data = [
        0,
    ];
}
