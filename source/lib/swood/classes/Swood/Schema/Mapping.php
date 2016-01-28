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

namespace Swood\Schema;

/**
 * 实现以数组形式存放kv数据，并将对象模拟成一个数组使用定义好的key名访问指定的数据。
 * 支持数字下标至键值转换（仅访问），通过ArrayAccess；
 * 支持toArray()返回原生数组；
 * 内/外部结构定义，支持默认值。
 *
 * @author andares
 */
class Mapping implements \ArrayAccess {
    use Loader;

    /**
     *
     * @var array
     */
    protected $_data = [];

    /**
     *
     * @param string $offset
     * @return int
     */
    protected function _transKey($offset) {
        return static::getSchema()[$offset]['key'];
    }

    protected function _getDefault($offset) {
        return static::getSchema()[$offset]['default'];
    }

    /**
     *
     * @return array
     */
    public function toArray() {
        return $this->_data;
    }

    /**
     * Array Access
     * @param mixed $offset
     * @return mixed
     */
    public function offsetExists($offset) {
        return isset($this->_data[$this->_transKey($offset)]);
    }

    public function offsetGet($offset) {
        $key = $this->_transKey($offset);
        return isset($this->_data[$key]) ? $this->_data[$key] : $this->_getDefault($offset);
    }

    public function offsetSet($offset, $value) {
        $this->_data[$this->_transKey($offset)] = $value;
    }

    public function offsetUnset($offset) {
        unset($this->_data[$this->_transKey($offset)]);
    }

}
