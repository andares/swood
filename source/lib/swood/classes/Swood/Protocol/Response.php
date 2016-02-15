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
 * Description of Response
 *
 * @author andares
 */
abstract class Response {

    /**
     *
     * @var Header
     */
    protected $header = null;

    /**
     *
     * @var array
     */
    protected $result = [];

    /**
     *
     * @var type
     */
    protected $errors = [];

    /**
     *
     * @var Channel[]
     */
    protected $channel = [];

    /**
     *
     * @param array $data
     */
    public function __construct($data) {
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
        isset($data[1]) && $this->result   = $data[1];
        isset($data[2]) && $this->errors   = $data[2];

        if (isset($data[3])) {
            foreach ($data[3] as $channel_name => $channel_data) {
                $this->channel[$channel_name] = new Channel($channel_name, $channel_data);
            }
        }
    }

    /**
     *
     * @param type $header_data
     * @return \Swood\Protocol\Header
     */
    protected function createHeader($header_data = []) {
        return new Header($header_data);
    }

    /**
     *
     * @param type $channel_name
     * @return \Swood\Protocol\Channel
     */
    protected function createChannel($channel_name) {
        return new Channel($channel_name);
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
    public function getAllResult() {
        return $this->result;
    }

    /**
     *
     * @param int $action_id
     * @param \Swood\App\Action\Result $result
     */
    public function setResult($action_id, \Swood\App\Action\Result $result) {
        // auto confirm
        $result->confirm();
        $this->result[$action_id] = $result->toArray();
    }

    public function clearResult() {
        $this->result = [];
    }

    public function getAllErrors() {
        return $this->errors;
    }

    public function setError($action_id, \Swood\App\Action\Error $error) {
        $this->error[$action_id] = $error->toArray();
    }

    public function getChannelList() {
        return array_keys($this->channel);
    }

    /**
     *
     * @param string $channel_name
     * @return Channel
     */
    public function getChannel($channel_name) {
        if (!isset($this->channel[$channel_name])) {
            $this->channel[$channel_name] = $this->createChannel($channel_name);
        }

        return $this->channel[$channel_name];
    }

    /**
     *
     * @return array
     */
    public function toArray() {
        $channel = [];
        foreach ($this->channel as $name => $channel) {
            $channel[$name] = $channel->toArray();
        }
        return [$this->header->toArray(), $this->result, $this->errors, $channel];
    }

    /**
     *
     * @note 可在子类中复写此方法自定义判断规则
     *
     * @return bool
     */
    public function hasError() {
        return $this->getHeader()->hasError();
    }

    abstract public function encode($data);
    abstract public function decode($data);
    abstract public function __toString();
}
