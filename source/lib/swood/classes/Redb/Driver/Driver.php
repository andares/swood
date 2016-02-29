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

namespace Redb\Driver;

/**
 * Description of Driver
 *
 * @author andares
 */
abstract class Driver {
    public static $driver_conf = '';

    /**
     *
     * @var mixed
     */
    protected $conn;

    /**
     * 待扩展方法
     */
    abstract public function connect($name);
    abstract public function close();
    abstract public function getConn();
    abstract public function query(array $query, $fields, $sort, $rows, $start = 0);

    protected function getConf($name) {
        $conf_path = "connections" . DIRECTORY_SEPARATOR . static::$driver_conf;
        $list = \Swood\Dock::select('instance')['conf']->get('redb', $conf_path);

        // alias处理
        if (isset($list[$name]['alias']) && $list[$name]['alias']) {
            $name = $list[$name]['alias'];
        }

        // link处理
        // 通过此种方法实现外部配置文件
        if (isset($list[$name]['link']) && $list[$name]['link']) {
            $conf = \Swood\Dock::select('instance')['conf']->get('redb',
                $conf_path . DIRECTORY_SEPARATOR . $list[$name]['link']);
        } else {
            $conf = $list[$name];
        }

        return $conf;
    }
}
