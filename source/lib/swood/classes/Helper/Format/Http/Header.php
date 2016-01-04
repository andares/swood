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

namespace Helper\Format\Http;

/**
 * Description of Header
 *
 * @author andares
 */
class Header {
    public $raw_data;

    public $method  = '';
    public $path    = '';
    public $version = 'HTTP/1.1';
    public $code    = '';
    public $message = '';
    public $info    = [];

    private static $_hp = '/^([A-Z]+ |)([^ ]+ |)(HTTP\/[0-9\.]+)( [0-9]+|)( [A-Z]+|)$/';

    public function __construct($data = []) {
        $this->raw_data = $data;
        $this->raw_data && $this->decode();
    }

    public function isRequest() {
        return $this->method ? true : false;
    }

    public function getParamsByPath() {
        $pos = strpos($this->path, '?');
        if ($pos === false) {
            return [];
        }
        \parse_str(urldecode(substr($this->path, $pos + 1)), $result);
        return $result ? $result : [];
    }

    public function __toString() {
        return $this->encode();
    }

    public function setRequestTaget($path, $method = 'POST') {
        $this->method = $method;
        $this->path   = $path;
    }

    public function setResponseCode($code = 200) {
        $this->code     = $code;
        $this->message  = isset(Constant::$code_messages[$code]) ?
            Constant::$code_messages[$code] : 'Unknown Error';
    }

    public function setInfo($name, $value) {
        $this->info[$name] = $value;
    }

    public function encode() {
        $data = [];

        // 第一行
        if ($this->isRequest()) {
            $data[0] = [
                $this->method,
                $this->path,
                $this->version,
            ];
        } else {
            $data[0] = [
                $this->version,
                $this->code,
                $this->message,
            ];
        }
        $data[0] = implode(' ', $data[0]);

        // info
        foreach ($this->info as $name => $value) {
            $data[] = "$name: $value";
        }

        // 组合
        return implode("\r\n", $data);
    }

    public function decode() {
        $data = explode("\r\n", $this->raw_data);

        // http协议第一行分析
        if (!isset($data[0]) || !preg_match(self::$_hp, $data[0], $match)) {
            throw new \LogicException("http header error");
        }
        $this->method   = trim($match[1]);
        $this->path     = trim($match[2]);
        $this->version  = $match[3];
        $this->code     = intval(trim($match[4]));
        $this->message  = trim($match[5]);

        // 参数分析
        $length = count($data);
        for ($i = 1; $i < $length; $i++) {
            $pos = strpos($data[$i], ':');
            $this->info[trim(substr($data[$i], 0, $pos))] = trim(substr($data[$i], $pos + 1));
        }
    }
}
