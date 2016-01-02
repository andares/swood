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
use Swood\Debug as D;

/**
 * Description of Request
 *
 * 如需要实现类似RESTful的直接数据存取接口，可通过在Action中实现一个rest接口实现。
 *
 * @author andares
 */
abstract class Request {

    /**
     *
     * @var Header
     */
    protected $header = null;

    /**
     *
     * @var array
     */
    protected $actions = [];

    /**
     *
     * @param array $data
     */
    public function __construct($data = []) {
        if (is_string($data)) {
            $data = $this->decode($data);
            if (!$data) {
                $data = [];
                D::log('request data decode fail', 'error');
            }
        }

        if (isset($data[0])) {
            $this->header   = $this->createHeader($data[0]);
        } else {
            $this->header   = $this->createHeader();
        }
        isset($data[1]) && $this->actions   = $data[1];
    }

    protected function createHeader($header_data = []) {
        return new Header($header_data);
    }

    /**
     *
     * @return Header
     */
    public function getHeader() {
        return $this->header;
    }

    /**
     *
     * @return array
     */
    public function getActions() {
        return $this->actions;
    }

    /**
     *
     * @param array $actions
     */
    public function setActions($actions) {
        $this->actions = $actions;
    }

    /**
     *
     * @param string $name
     * @param array $params
     * @param int $version
     */
    public function appendAction($name, $params = [], $version = 0) {
        $this->actions[] = [$name, $params, $version];
    }

    /**
     *
     * @return array
     */
    public function toArray() {
        return [$this->header->toArray(), $this->actions];
    }

    abstract public function encode($data);
    abstract public function decode($data);
    abstract public function __toString();
}
