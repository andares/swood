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
use Swood\Debug as D;

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
     * 设置app当前工作模式。工作模式可用于程序流程上的多种判定，为不同的模式提供不同的判定及行为。
     * 目前用于action判断使用哪个注册表。
     * @var string
     */
    protected $mode = 'web';

    public function __construct() {
        $this->initSpaceName();
    }

    /**
     *
     * @param string $mode
     */
    public function setMode($mode) {
        $this->mode = $mode;
    }

    public function getMode() {
        return $this->mode;
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
