<?php

/*
 * Copyright (C) 2015 andares.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301  USA
 */

namespace Swood;
use Swoole,
    Swood\Debug as D;

/**
 * Description
 *
 * @todo Client 异步先不做
 *
 * @author andares
 *
 * @property Swoole\Client $swoole swoole client
 */
class Client {
    use Runtime;

    /**
     *
     * @var array
     */
    private $conf;

    public function __construct(array $conf, array $swoole_conf = []) {
        $this->conf = $conf;
        $this->createSwooleClient($swoole_conf);
    }

    public function __call($method, $args) {
        $this->swoole->$method(...$args);
    }

    public function bindCallback() {
    }

    public function call($request, $host, $port, $timeout = 0.3) {
        $data = $this->packData("$request");

        // TODO 暂不支持flag=1异步
        if (!$this->swoole->connect($host, $port, $timeout, 0)) {
            throw new \RuntimeException('swoole client connect fail');
        }

        $this->swoole->send($data);
        $response = $this->swoole->recv();
        if (!$response) {
            return false;
        }
        $data = $this->unpackData($response);
        return $data;
    }

    private function createSwooleClient(array $swoole_conf) {
        $this->swoole = new Swoole\Client(constant($this->conf['type']),
            constant($this->conf['is_sync']));
        $this->swoole->set($swoole_conf);
    }

    private function createHandler() {
        $this->handler  = new Handler\Client($this);
        $this->handler->setPortMapping($this->listen_conf['port'], 'launcher');
    }

}
