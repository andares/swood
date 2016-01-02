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

namespace Swood\Protocol;

/**
 * Description of Channel
 *
 * @author andares
 */
class Channel implements \IteratorAggregate {
    /**
     *
     * @var string
     */
    private $name;

    /**
     *
     * @var array
     */
    private $data;

    /**
     *
     * @param string $name
     * @param array $data
     */
    public function __construct($name, $data = []) {
        $this->name = $name;
        $this->data = $data;
    }

    /**
     *
     * @param mixed $info
     * @param string $key
     */
    public function __invoke($info, $key = null) {
        $this->set($info, $key);
    }

    /**
     *
     * @param mixed $info
     * @param string $key
     */
    public function set($info, $key = null) {
        if ($key) {
            $this->data[$key] = $info;
        } else {
            $this->data[] = $info;
        }
    }

    /**
     *
     * @return array
     */
    public function toArray() {
        return $this->data;
    }

    /**
     * 聚合迭代器
     * @return \ArrayIterator
     */
    public function getIterator() {
        return new \ArrayIterator($this->toArray());
    }
}
