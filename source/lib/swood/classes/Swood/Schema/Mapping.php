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
 * Description of Mapping
 *
 * @author andares
 */
trait Mapping {
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
        return $this->_getSchema()[$offset];
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
        return $this->_data[$this->_transKey($offset)];
    }

    public function offsetSet($offset, $value) {
        $this->_data[$this->_transKey($offset)] = $value;
    }

    public function offsetUnset($offset) {
        unset($this->_data[$this->_transKey($offset)]);
    }

}
