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

namespace Redb\Driver;

/**
 * Description of Solr
 *
 * @author andares
 */
class Solr extends Driver {
    public static $driver_conf = 'solr';

	private $host;
	private $port;
	private $core;
	private $url;

	private $post_cache = [];

    public function connect($name) {
        $conf = $this->getConf();

        $this->conn = new \MongoClient($conf[$name]['host'], $conf[$name]['options']);
        $this->conn->selectDB($conf[$name]['db']);
    }

    public function close() {
        $this->conn->close();
    }

	public function __construct($core, $conf_name = 'default') {
        $config = Core_Config::get("solr.$conf_name");
        $this->core = $config['indexes'][$core];
        $this->host = $config['host'];
        $this->port = $config['port'];

		$this->url = "http://$this->host:$this->port/solr/$this->core";
	}

	public function post(Core_ArrayObject $doc) {
		$this->post_cache[] = $doc->toArray();
	}

	public function postCancel() {
		$this->post_cache = [];
	}

	public function delete($query) {
        $url	= "$this->url/update?wt=json&commit=true";
        $post	= json_encode(['delete' => ['query' => $query]]);
		return $this->callApi($url, $post);
	}

	public function rebuild() {
        $url = "$this->url/update?wt=json&commit=true";

		return $this->callApi($url, ['optimize' => 1]);
	}

    public function update() {
		if (!$this->post_cache) {
			return true;
		}

        $url = "$this->url/update?wt=json&commit=true";

        $post = json_encode($this->post_cache);
		$this->post_cache = [];

		return $this->callApi($url, $post);

    }

	private function callApi($url, $post) {
		$ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json; charset=utf-8",
            'Content-Length: ' . strlen($post),
            'Accept: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = json_decode(curl_exec($ch));
		curl_close($ch);

		Core_Log::log("call solr: $url | " . json_encode($post));

		if (!isset($data) || $data->responseHeader->status != 0) {
            if (isset($data->error) && !empty($data->error->msg)) {
                try {
                    throw new Exception($data->error->msg);
                } catch (Exception $e) {
					Core_Log::log($url);
					Core_Log::log($post);
                    Core_Log::log($e->getMessage());
                }
            } else {
				Core_Log::log('solr query err');
            }
        }

		return $data;
	}

}
