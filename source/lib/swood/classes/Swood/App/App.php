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

namespace Swood\App;

/**
 * Description of App
 *
 * @author andares
 */
abstract class App {

    public $class_space = '';

    public $conf_space = '';

    /**
     *
     * @var \Swood\Server
     */
    public $server = null;

    /**
     *
     * @var Worker
     */
    public $worker = null;

    /**
     *
     * @var array
     */
    protected $conf = [];

    public function __construct($conf) {
        $this->conf = $conf;

        $this->initSpaceName();
    }

    protected function initSpaceName() {
        if (!$this->class_space) {
            $class = get_called_class();
            $this->class_space = substr($class, 0, strrpos($class, '\\'));
        }

        if (!$this->conf_space) {
            $this->conf_space = strtolower(str_replace('\\', '_', $this->class_space));
        }
    }

    public function setServer(\Swood\Server $server) {
        $this->server   = $server;
    }

    public function getSwoole() {
        return $this->server->swoole;
    }

    /**
     *
     * @return \Swood\Conf
     */
    public function getConf() {
        return \Swood\Dock::select('instance')['conf'];
    }

    public function getListenConf($port_id = null) {
        if ($port_id !== null) {
            return isset($this->conf['listen'][$port_id]) ? $this->conf['listen'][$port_id] : null;
        } else {
            return $this->conf['listen'];
        }
    }

    public function hookAfterReceive() {
        // do something..
    }

    /**
     *
     * @param type $data
     * @return \Swood\Protocol\Request
     */
    abstract public function buildRequest($data = []);

    /**
     *
     * @param type $data
     * @return \Swood\Protocol\Response
     */
    abstract public function buildResponse($data = []);

    abstract public function createWorker();
    abstract public function createTaskWorker();

    abstract public function start();
    abstract public function shutdown();

}
