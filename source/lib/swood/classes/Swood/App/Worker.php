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
 * Description of Worker
 *
 * @author andares
 */
abstract class Worker extends WorkerBase {

    /**
     * action路由
     *
     * @param array $action_call
     * @return Action\Result
     */
    protected function dispatch(array $action_call) {
        // 取参数
        list($name, $params) = $action_call;
        $version = isset($action_call[2]) ? $action_call[2] : 0;

        // 调action
        // TODO 目前写死 Action 目录下
        $class = "{$this->app->class_space}\\Action\\" . str_replace('/', '\\', $name);
        if (!class_exists($class)) {
            throw new \BadMethodCallException("method [$name] not exists");
        }
        $action = $class::call($this->app, $version);
        /* @var $action Action */
        $result = $action->main($params);

        return $result;
    }

    public function call(\Swood\Protocol\Request $request, \Swood\Protocol\Response $response) {
        D::ec(">> worker receive");

        try {
            foreach ($request->getActions() as $action_id => $action_call) {
                $result = $this->dispatch($action_call);
                if ($result) { // 如果某个action返回null或无返回则response.result中会跳过此action id
                    if ($result instanceof Action\Error) {
                        $response->setError($action_id, $result);
                    } else {
                        $response->setResult($action_id, $result);
                    }
                }

                // TODO hook action done
                // 暂时在这里清理掉载入的redb cache并存盘
                \Redb\Cache::saveAll();
                \Redb\Cache::clearAll();
            }
        } catch (\Exception $exc) {
            $header = $response->getHeader();
            $header->raiseError($exc);

            // 日志
            D::logError($exc);

            // 出错后清除所有之前返回的结果
            $response->clearResult();
        } finally {
            // TODO hook request done
        }
    }

    public function taskFinish($task_id, \Swood\Protocol\Response $response) {
        D::du("task[$task_id] finished with $response");
    }

}
