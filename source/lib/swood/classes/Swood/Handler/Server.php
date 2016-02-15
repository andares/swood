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
 * Description of Server
 *
 * @author andares
 *
 */
class Server {
    use ServerUnsupport;

    /**
     *
     * @var \Swood\Server
     */
    private $server;

    /**
     *
     * @var array
     */
    private static $bind_list = [
        'start',
        'shutdown',
        'workerStart',
        'receive',
        'workerStop',
        'workerError',
        'task',
        'finish',
    ];

    /**
     *
     * @var array
     */
    private $port_mapping = [];

    /**
     *
     * @param \Swood\Server $server
     */
    public function __construct(\Swood\Server $server) {
        $this->server   = $server;
    }

    /**
     *
     * @param type $port
     * @param type $name
     */
    public function setPortMapping($port, $name) {
        $this->port_mapping[$port][] = $name;
    }

    /**
     *
     * @param type $port
     * @return type
     */
    public function mappingPort($port) {
        return isset($this->port_mapping[$port]) ? $this->port_mapping[$port] : [];
    }

    /**
     * 绑定回调事件
     */
    public function bindCallback() {
        foreach (static::$bind_list as $event) {
            $this->server->swoole->on($event, [$this, $event]);
        }
    }

    /**
     *
     * @param Swoole\Server $server
     */
    public function start(Swoole\Server $server) {
        // 启动前输出一下autoload到的数据
        D::log('debug', \Swood\Dock::select('swood')['autoload']->getLoadedClasses(), "Autoload classes list before server started");

        D::du("Server start [$server->master_pid]");

        $this->server->setProcessType(\Swood\Server::PROCESSTYPE_MASTER);

        try {
            foreach ($this->server->getAllApps() as $app) {
                /* @var $app \Swood\App\App */
                // 设置模式
                $app->setMode('web');

                $app->start();
            }
        } catch (\Exception $exc) {
            D::logError($exc);
            D::ec("server start fail");
        }
        D::ec("server is started");
    }

    /**
     *
     * @param Swoole\Server $server
     */
    public function shutdown(Swoole\Server $server) {
        D::du("Server shutdown [$server->master_pid]");

        foreach ($this->server->getAllApps() as $app) {
            /* @var $app \Swood\App\App */
            $app->shutdown();
        }

        // TODO hook shutdown
        // TODO shutdown 目前在这里关一下连接
        if (class_exists('\\Redb\Connections')) {
            \Redb\Connections::closeAll();
        }
    }

    /**
     *
     * @param Swoole\Server $server
     * @param type $worker_id
     */
    public function workerStart(Swoole\Server $server, $worker_id) {
        D::du("Worker start[$server->worker_pid]: $worker_id");

        // 清除缓存
        if (function_exists('\opcache_reset')) {
            D::du("Reset opcache");
            \opcache_reset();
        }

        foreach ($this->server->getAllApps() as $app) {
            /* @var $app \Swood\App\App */
            if ($server->taskworker) {
                $this->server->setProcessType(\Swood\Server::PROCESSTYPE_TASKWORKER);
                $app->worker = $app->createTaskWorker();
            } else {
                $this->server->setProcessType(\Swood\Server::PROCESSTYPE_WORKER);
                $app->worker = $app->createWorker();
            }
            $app->worker && $app->worker->start();
        }
    }

    public function receive(Swoole\Server $server, $fd, $from_id, $data) {
        D::du("received[$server->worker_pid]");

        // 连接信息
        $this->server->current_fd = $fd;
        $connection_info = $this->server->getConnectionInfo();
        foreach ($this->mappingPort($connection_info['server_port']) as $app_name) {
            try {
                $app = $this->server->getApp($app_name);
                if (!$app->worker) {
                    // worker可能不存在
                    continue;
                }

                $data = $this->server->unpackData($data);
                if (!$data) {
                    // TODO 是否做处理待考
                    continue;
                }

                $request    = $app->buildRequest($data);
                $response   = $app->buildResponse();
            } catch (\Exception $exc) {
                D::logError($exc);
                continue;
            }

            // 执行
            $app->worker->call($request, $response);
            D::level() && D::du("call: <$app_name> " . \json_encode($request->toArray()) .
                " => " . \json_encode($response->toArray()));

            // 发回
            $result   = $this->server->packData("$response");
            $server->send($fd, $result);
        }

        // hook
        foreach ($this->mappingPort($connection_info['server_port']) as $app_name) {
            try {
                $app = $this->server->getApp($app_name);
                // TODO hook app after receive
            } catch (\Exception $exc) {
                D::logError($exc);
                continue;
            }
        }
    }

    /**
     *
     * @param Swoole\Server $server
     * @param type $worker_id
     */
    public function workerStop(Swoole\Server $server, $worker_id) {
        D::du("Worker stop[$server->worker_pid]: $worker_id");

        foreach ($this->server->getAllApps() as $app) {
            /* @var $app \Swood\App\App */
            $app->worker && $app->worker->stop();
        }
    }

    /**
     *
     * @param Swoole\Server $server
     * @param type $worker_id
     * @param type $worker_pid
     * @param type $exit_code
     */
    public function workerError(Swoole\Server $server, $worker_id, $worker_pid, $exit_code) {
        // NOTICE 这里不能调$server->worker_pid，在某些情况下会访问不到该字段
        D::du("Worker error: $worker_id - $exit_code");

        if (D::level()) {
            return $this->server->swoole->shutdown();
        }

        foreach ($this->server->getAllApps() as $app) {
            /* @var $app \Swood\App\App */
            $app->worker && $app->worker->error();
        }
    }

    /**
     *
     * @todo 未完成
     *
     * @param Swoole\Server $server
     * @param type $task_id
     * @param type $from_id
     * @param type $data
     */
    public function task(Swoole\Server $server, $task_id, $from_id, $data) {
        D::du("Task got[$server->worker_pid]: $task_id");

        foreach ($this->server->getAllApps() as $app) {
            /* @var $app \Swood\App\App */
            if (!$app->worker) { // 并不是所有app都有task worker
                continue;
            }
//            $app->worker->call();
        }
    }

    /**
     *
     * @todo 未完成
     *
     * @param Swoole\Server $server
     * @param type $task_id
     * @param type $data
     */
    public function finish(Swoole\Server $server, $task_id, $data) {
        D::du("Task finish[$server->worker_pid]: $task_id");

        foreach ($this->server->getAllApps() as $app) {
            /* @var $app \Swood\App\App */
            $app->worker && $app->worker->taskFinish();
        }
    }


}
