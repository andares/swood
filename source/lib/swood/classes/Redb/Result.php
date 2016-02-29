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
use Swood\Debug as D;

/**
 * 查询结果集
 *
 * @author andares
 */
class Result implements \IteratorAggregate {

    /**
     *
     * @var array|\Iterator
     */
    private $handle;

    /**
     *
     * @var callable
     */
    private $wrapper = null;

    /**
     *
     * @var callable
     */
    private $fetch_before = null;

    /**
     *
     * @var callable
     */
    private $fetch_after = null;

    /**
     *
     * @var array
     */
    public $info;

    /**
     * 查询得到的总记录数
     * @var int
     */
    public $total_rows = null;

    /**
     * 构造器
     * @param type $handle
     * @param type $info
     */
    public function __construct($handle, $info = []) {
        $this->handle   = $handle;
        $this->info     = $info;
    }

    /**
     *
     * @param type $wrapper
     * @param type $fetch_before
     * @param type $fetch_after
     */
    public function setWrapper($wrapper, $fetch_before = null, $fetch_after = null) {
        $this->wrapper = $wrapper;
        $this->fetch_before = $fetch_before;
        $this->fetch_after  = $fetch_after;
    }

    /**
     * 获取handle。handle可能是一个数组，或是一个迭代器对象
     * @return array|\Iterator
     */
    public function getHandle() {
        return $this->handle;
    }

    /**
     * 获得取数据迭代器
     *
     * @yield int => array|\Iterator
     */
    public function fetch() {
        if ($this->fetch_before) {
            $call = $this->fetch_before;
            $call($this);
        }

        $wrapper = $this->wrapper;
        $count   = 0;
        foreach ($this->handle as $row) {
            $data = $wrapper ? $wrapper($row) : $row;
            if ($data === null) {
                continue;
            }

            if (is_object($data)) {
                yield $data->getId() => $data;
            } else {
                yield $count => $data;
            }

            $count++;
        }

        if ($this->fetch_after) {
            $call = $this->fetch_after;
            $call($this);
        }
    }

    /**
     * 调用所有的数据对象的某个方法
     * @param type $func
     * @param type $args
     */
    public function __call($func, $args) {
        foreach ($this as $data) {
            $data->$func(...$args);
        }
    }

    /**
     * 聚合迭代器
     * @return \Iterator
     */
    public function getIterator() {
        return $this->fetch();
    }
}
