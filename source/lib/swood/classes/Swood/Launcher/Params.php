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

namespace Swood\Launcher;

/**
 * Description of Params
 *
 * main cmd: start, stop, reload, status, hold, call, exec
 *
 * @author andares
 */
class Params extends \Helper\Terminal\Params {
    private $cmd_list = [
        'start',
        'stop',
        'reload',
        'status',
        'hold',
        'call',
        'exec',
        'play',
    ];

    public $conf    = 'default';

    public $debug   = 0;

    public $help    = 0;

    public $header  = [];

    public $work_dir = '.';

    public $ver     = 0;

    public $app     = null;

    public $port    = 0;

    protected static $options_mapping = [
        'C' => 'work_dir',
        'H' => 'header',
    ];

    public function getCmd() {
        if (count($this->_main) <= 1 || !in_array($this->_main[1], $this->cmd_list)) {
            return null;
        }
        return $this->_main[1];
    }

    public function getWorkDir() {
        return $this->work_dir;
    }

    public function getHeader() {
        if (!$this->header) {
            return [];
        }

        $header = $this->parseCode($this->header);
        if (!$header) {
            throw new \InvalidArgumentException("params format error");
        }
        return $header;
    }

    public function getCallActions() {
        // api版本
        $ver = intval($this->ver);

        $actions = [];
        $count   = count($this->_main);
        // 0为action，1为params
        $col    = 0;
        for ($i = 2; $i < $count; $i++) {
            if (!$col) {
                if (!isset($this->_main[$i])) {
                    break;
                }
                $action = $this->_main[$i];

                $col++;
            } else {
                if (isset($this->_main[$i])) {
                    $params = $this->parseCode($this->_main[$i]);
                    if (!$params) {
                        throw new \InvalidArgumentException("params format error");
                    }
                } else {
                    $params = [];
                }
                $actions[] = [$action, $params, $ver];

                $col--;
            }
        }

        if (!count($action)) {
            throw new \InvalidArgumentException("not action to call");
        }

        return $actions;
    }

    private function parseCode($str) {
        $code = json_decode($str, true);
        if (!$code) {
            parse_str($str, $code);
            if (!$code) {
                return null;
            }
        }
        return $code;
    }

}
