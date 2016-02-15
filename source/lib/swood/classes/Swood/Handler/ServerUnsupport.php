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

namespace Swood\Handler;
use Swoole,
    Swood\Debug as D;

/**
 * Description of ServerUnsupport
 *
 * @author andares
 */
trait ServerUnsupport {
    /**
     *
     * @var array
     */
    protected static $bind_debug_list = [
        'packet',
        'timer',
        'pipeMessage',
        'connect',
        'close',
        'managerStart',
        'managerStop',
    ];

    /**
     * 绑定未支持的回调事件
     */
    public function bindDebugCallback() {
        foreach (static::$bind_debug_list as $event) {
            $this->server->swoole->on($event, [$this, $event]);
        }
    }

    /**
     *
     * @todo onTimer事件可能会被移除，暂时不支持
     *
     * @param Swoole\Server $server
     * @param type $interval
     */
    public function timer(Swoole\Server $server, $interval) {
        D::du("timer trigger[$server->worker_pid]: $interval", "Unsupported callback raise");

    }

    public function packet(Swoole\Server $server, $data, array $client_info) {
        D::du("udp received", "Unsupported callback raise");
    }

    public function pipeMessage(Swoole\Server $server, $from_id, $message) {
        D::du("send message received", "Unsupported callback raise");
    }

    public function connect(Swoole\Server $server, $fd, $from_id) {
        D::du("connected", "Unsupported callback raise");
    }

    public function close(Swoole\Server $server, $fd, $from_id) {
        D::du("closed", "Unsupported callback raise");
    }

    public function managerStart(Swoole\Server $server) {
        D::du("manager started", "Unsupported callback raise");
        $this->server->setProcessType(\Swood\Server::PROCESSTYPE_MANAGER);
    }

    public function managerStop(Swoole\Server $server) {
        D::du("manager stopped", "Unsupported callback raise");
    }

}
