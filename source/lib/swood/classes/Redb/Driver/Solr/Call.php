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

namespace Redb\Driver\Solr;
use Swood\Debug as D;

/**
 * Description of Call
 *
 * @author andares
 */
class Call {
    private $url;
    private $param;
    private $post;

    public function __construct($url, $param, $post = []) {
        $this->url   = $url;
        $this->param = $param;
        $this->post  = $post;
    }

    public function __invoke($core) {
        return $this->post($core);
    }

    public function post($core) {
        $url = "$this->url/$core/$this->param";
		$ch  = curl_init($url);
        if ($this->post) {
            $post = json_encode($this->post);

            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type: application/json; charset=utf-8",
                'Content-Length: ' . strlen($post),
                'Accept: application/json',
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

            D::log('debug', "call solr: " . urldecode($url) . " | " . json_encode($post));
        } else {
            curl_setopt($ch, CURLOPT_POST, 0);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type: application/json; charset=utf-8",
                'Accept: application/json',
            ]);

            D::log('debug', "call solr: " . urldecode($url));
        }
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = json_decode(curl_exec($ch), true);
		curl_close($ch);

		if (!$data || $data['responseHeader']['status'] != 0) {
            if (isset($data['error']) && !empty($data['error']['msg'])) {
                try {
                    throw new \Exception($data['error']['msg']);
                } catch (\Exception $e) {
					D::log('debug', $url);
					D::log('debug', $this->post);
                    D::log('debug', $e->getMessage());
                }
            } else {
				D::log('debug', 'solr query err');
            }
        }

		return $data;
    }
}
