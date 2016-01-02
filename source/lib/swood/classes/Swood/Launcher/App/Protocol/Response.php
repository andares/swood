<?php

/*
 * license less
 */

namespace Swood\Launcher\App\Protocol;

/**
 * Description of Response
 *
 * @author andares
 */
class Response extends \Swood\Protocol\Response {
    use \Helper\Format\Msgpack;

    protected function createHeader($header_data = []) {
        $header = new ResponseHeader($header_data);
        $header['timestamp'] = time();
        return $header;
    }
}
