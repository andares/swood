<?php

/*
 * Copyright (C) 2016 andares.
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
 * Description of Action
 *
 * @author andares
 */
abstract class Action {
    /**
     *
     * @var App
     */
    protected $app;

    public function __construct(App $app) {
        $this->app = $app;
    }

    /**
     *
     * @param \Swood\App\App $app
     * @param type $version
     * @return \Swood\App\Action
     * @throws \UnexpectedValueException
     */
    public static function call(App $app, $version = 0) {
        // 载入action注册信息
        $conf   = $app->getConf()->get($app->conf_space, 'actions');
        $class  = get_called_class();

        // 判断注册状态
        if (!isset($conf[$class]) || !in_array($app->getMode(), $conf[$class]['apply'])) {
            throw new \UnexpectedValueException("action is not register");
        }

        // 确定action版本
        $action_conf = $conf[$class];
        if ($action_conf['last_version']) {
            if ($version) {
                if ($version > $action_conf['last_version']) {
                    $class .= "\\V{$action_conf['last_version']}";
                } else {
                    $class .= "\\V$version";
                }
            } else {
                $class .= "\\V{$action_conf['last_version']}";
            }
        }

        $action = new $class($app);
        return $action;
    }

    abstract public function main(array $params);
}
