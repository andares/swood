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
    protected static $_schema = [
        'timestamp' => [
            'key'       => 0,
            'default'   => null,
        ],
        'error' => [
            'key'       => 1,
            'default'   => null,
        ],
    ];

    protected $_data = [
        0,
    ];
}
