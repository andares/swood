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

/**
 * Description of Dock
 *
 * @author andares
 */
class Dock implements \ArrayAccess {
    private static $instance = [];

    protected $name;

    protected $objs;

    public static function select($name) {
        if (!isset(self::$instance[$name])) {
            self::$instance[$name] = new self($name);
        }

        return self::$instance[$name];
    }

    public function __construct($name) {
        $this->name = $name;
    }

    public function offsetExists($offset) {
        return isset($this->objs[$offset]);
    }

    public function offsetGet($offset) {
        return $this->objs[$offset];
    }

    public function offsetSet($offset, $value) {
        $this->objs[$offset] = $value;
    }

    public function offsetUnset($offset) {
        unset($this->objs[$offset]);
    }
}
