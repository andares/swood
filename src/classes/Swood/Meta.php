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
namespace Swood;

/**
 *
 * @author andares
 */
abstract class Meta implements \ArrayAccess, \IteratorAggregate, \Serializable {
    /**
     * 数组化包版本号
     * @var int
     */
    protected static $_version  = 1;

    /**
     * 格式配置
     * @var array
     */
    protected static $_schema = [];

    /**
     * @var array
     */
    protected $_data = [];

    /**
     * 构造器
     * @param array $data
     */
    public function __construct(array $data = null) {
        $data && $this->fill($data);
    }


    /**
     * 确认数据
     * @throws \UnexpectedValueException
     */
    public function confirm() {
        foreach (static::$_schema as $name => $default) {
            $method = "_confirm_$name";
            if ($this->$name === null && $default === null) {
                throw new \InvalidArgumentException("meta field [$name] could not be empty", 10006);
            }
            if (method_exists($this, $method)) {
                $this->$name = $this->$method($this->$name);
            }
        }
        return $this;
    }

    /**
     * 填充数据
     * @param array $data
     */
    public function fill($data) {
        if (!is_array($data) && !is_object($data)) {
            throw new \InvalidArgumentException("fill data error");
        }

        foreach (static::$_schema as $name => $default) {
            isset($data[$name]) && $this->$name = $data[$name];
        }
    }

    /**
     * 根据数字下标的array填充
     * @param array $arr
     * @param boolean $allow_default
     * @return array
     */
    public function fillByArray(array $arr, $allow_default = false) {
        $count  = 0;
        foreach (static::$_schema as $name => $default) {
            if ($allow_default && !isset($arr[$count])) {
                $this->$name = $default;
                continue;
            }
            $this->$name = $arr[$count];
            $count++;
        }

    }

    public function serialize() {
        $arr[] = static::$_version;
        foreach (static::$_schema as $name => $default) {
            $arr[] = isset($this->$name) ? $this->$name : $default;
        }
        return static::pack($arr);
    }

    /**
     *
     * @param type $data
     * @throws \UnexpectedValueException
     */
    public function unserialize($data) {
        $arr = static::unpack($data);
        if (!$arr) {
            throw new \UnexpectedValueException("unpack fail");
        }
        $last_version = array_shift($arr);

        // 触发升级勾子
        if ($last_version != static::$_version) {
            $arr = static::_renew($arr, $last_version);
        }
        if (!$arr) {
            throw new \UnexpectedValueException("unserialize fail");
        }

        $this->fillByArray($arr);
    }

    protected static function _renew(array $data, $last_version) {
        return $data;
    }

    protected static function pack(array $value) {
        return \msgpack_pack($value);
    }

    protected static function unpack($data) {
        return \msgpack_unpack($data);
    }

    /**
     * 转换到数组
     * @return array
     */
    public function toArray() {
        $arr = [];
        foreach (static::$_schema as $name => $default) {
            $arr[$name] = isset($this->$name) ? $this->$name : $default;
        }
        return $arr;
    }

    /**
     * 重载系列方法
     * @param type $name
     * @param type $value
     */
    public function __set($name, $value) {
        $this->_data[$name] = $value;
    }

    public function __get($name) {
        if (!array_key_exists($name, static::$_schema)) {
            return null;
        }
        return isset($this->_data[$name]) ? $this->_data[$name] : static::$_schema[$name];
    }

    public function __isset($name) {
        return isset($this->_data[$name]);
    }

    public function __unset($name) {
        unset($this->_data[$name]);
    }

    /**
     * Array Access
     * @param mixed $offset
     * @return mixed
     */
    public function offsetExists($offset) {
        return isset($this->$offset);
    }

    public function offsetGet($offset) {
        return $this->$offset;
    }

    public function offsetSet($offset, $value) {
        $this->$offset = $value;
    }

    public function offsetUnset($offset) {
        $this->$offset = null;
    }

    /**
     * 聚合迭代器
     * @return \ArrayIterator
     */
    public function getIterator(): \ArrayIterator {
        return new \ArrayIterator($this->toArray());
    }

    public function __toString() {
        return json_encode($this->toArray());
    }

    public static function getSchema() {
        return static::$_schema;
    }
}
