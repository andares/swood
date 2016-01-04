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

namespace Helper\Format;

/**
 * Description of Http
 *
 * @author andares
 */
trait Http {
    /**
     *
     * @var Http\Header
     */
    protected $http_header = null;

    abstract protected function toArray();

    public function encode($data) {
        return $this->encodeHttpData($data);
    }

    public function decode($data) {
        return $this->decodeHttpData($data);
    }

    /**
     *
     * @param type $data
     * @return Http\Header
     */
    public function getHttpHeader($data = []) {
        if (!$this->http_header) {
            $this->http_header = new Http\Header($data);
        }
        return $this->http_header;
    }

    protected function encodeHttpData($data) {
        $http_header = $this->getHttpHeader();
        if (!$http_header) {
            throw new \LogicException("http header is lost");
        }

        // 生成body
        $body = is_array($data) ? http_build_query($data) : $data;

        $http_header->setInfo('Content-Length', strlen($body));
        return "$http_header\r\n\r\n$body";
    }

    protected function decodeHttpData($data) {
        // 分离出header和body
        list($header, $body) = explode("\r\n\r\n", $data);

        // 处理header
        $http_header = $this->getHttpHeader($header);

        // 处理body
        // 先处理get中的参数，并由post中的覆盖
        $get_data   = $http_header->getParamsByPath();
        $post_data  = \Helper\String::decodeJson(\urldecode(\trim($body)));
        if ($post_data) {
            return array_merge($get_data, $post_data);
        }
        return $get_data;
    }

    public function __toString() {
        return $this->encode($this->toArray());
    }
}
