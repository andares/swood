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

namespace Redb;

/**
 * 连接管理器
 *
 * @author andares
 */
class Connections {
    /**
     *
     * @var Driver\Drive|array
     */
    private static $drivers = [];

    /**
     *
     * @param type $type
     * @param type $name
     * @return type
     */
    public static function get($type, $name, $auto_connect = true) {
        if (!isset(self::$drivers[$type][$name])) {
            $class = "\\Redb\\Driver\\$type";
            self::$drivers[$type][$name] = new $class();
            $auto_connect && self::$drivers[$type][$name]->connect($name);
        }
        return self::$drivers[$type][$name];
    }

    /**
     * 连接管理器
     * @param type $type
     */
    public static function closeAll($type = null) {
        if ($type) {
            if (isset(self::$drivers[$type])) {
                foreach (self::$drivers[$type] as $name => $driver) {
                    /* @var $driver Driver\Driver */
                    $driver->close();
                    unset(self::$drivers[$type][$name]);
                }
            }
        } else {
            foreach (self::$drivers as $type => $driver_list) {
                foreach ($driver_list as $name => $driver) {
                    /* @var $driver Driver\Driver */
                    $driver->close();
                    unset(self::$drivers[$type][$name]);
                }
            }
        }
    }
}
