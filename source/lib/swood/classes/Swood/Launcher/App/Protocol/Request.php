<?php

/*
 * license less
 */

namespace Swood\Launcher\App\Protocol;

/**
 * Description of Request
 *
 * @author andares
 */
class Request extends \Swood\Protocol\Request {
    use \Helper\Format\Msgpack;

    protected function createHeader($header_data = []) {
        return new RequestHeader($header_data);
    }
}
