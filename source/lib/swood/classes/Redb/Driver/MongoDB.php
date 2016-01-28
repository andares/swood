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
 * Description of MongoDB
 *
 * @author andares
 *
 * @property \MongoClient $conn mongo客户端
 */
class MongoDB extends Driver {
    public static $driver_conf = 'mongodb';

    /**
     * mongo连接列表
     * @var array
     */
    private static $clients = [];

    /**
     *
     * @var string
     */
    private $host_uri = '';

    /**
     *
     * @param string $name
     * @return \Redb\Driver\MongoDB
     * @throws \Exception
     */
    public function connect($name) {
        $conf = $this->getConf($name);

        try {
            if (!isset(self::$clients[$conf['host']])) {
                self::$clients[$conf['host']] = new \MongoClient($conf['host'], $conf['options']);
            }

            $this->conn = self::$clients[$conf['host']]->selectDB($conf['db']);
        } catch (\Exception $exc) {
            // 暂时继续外抛
            throw $exc;
        }

        $this->host_uri = $conf['host'];
        return $this;
    }

    /**
     * 
     */
    public function close() {
        self::$clients[$this->host_uri]->close();
    }

    /**
     *
     * @return \MongoDB
     */
    public function getConn() {
        return $this->conn;
    }

    /**
     *
     * @param string $collection
     * @return \MongoCollection
     */
    public function getCollection($collection) {
        return $this->conn->selectCollection($collection);
    }
}
