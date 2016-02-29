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
use Swood\Debug as D;

/**
 * Description of Solr
 *
 * @author andares
 */
class Solr extends Driver {
    public static $driver_conf = 'solr';

	private $url;

	private $post_cache = [];

    private static $query_keys = [
        'q', 'fq', 'sort',
    ];

    public function connect($name) {
        $conf = $this->getConf($name);

        $this->url = "http://{$conf['host']}:{$conf['port']}/{$conf['path']}";
    }

    public function close() {
        return true;
    }

    public function getConn() {
        return $this;
    }

    /**
     *
     * @param array $query
     * @param type $fields
     * @param type $sort
     * @param type $rows
     * @param type $start
     * @return \Redb\Driver\Solr\Call
     */
    public function query(array $query, $fields, $sort, $rows, $start = 0) {
        // 拼接
        $param = 'select?';
        isset($query['q'])  && $param   .= "q={$query['q']}&";
        isset($query['fq']) && $param   .= "{$query['fq']}&";
        isset($query['qf']) && $param   .= "qf={$query['qf']}&";

        // 查询字段
        $fields && $param .= "fl=$fields&";

        // 排序
        $sort && $param .= "sort=$sort&";

        $param .= "start=$start&rows=$rows&stopwords=true&wt=json&indent=true";
		return new Solr\Call($this->url, $param);
    }

	public function post(\Swood\Schema\Meta $doc) {
		$this->post_cache[] = $doc->toArray();
	}

	public function postCancel() {
		$this->post_cache = [];
	}

	public function delete($query) {
        $param	= "$this->url/update?wt=json&commit=true";
		return new Solr\Call($this->url, $param, ['delete' => ['query' => $query]]);
	}

	public function rebuild() {
        $param = "$this->url/update?wt=json&commit=true";
		return new Solr\Call($this->url, $param, ['optimize' => 1]);
	}

    public function update($core) {
		if (!$this->post_cache) {
			return true;
		}
        $param = "update?wt=json&commit=true";

        $post = json_encode($this->post_cache);
		$this->post_cache = [];
		return new Solr\Call($this->url, $param, $post);
    }
}
