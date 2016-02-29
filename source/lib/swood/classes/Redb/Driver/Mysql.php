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
 * Description of  Mysql
 *
 * @author andares
 *
 */
class Mysql extends Driver {
    public static $driver_conf = 'mysql';

    /**
     *
     * @param string $name
     * @return \Redb\Driver\MongoDB
     * @throws \Exception
     */
    public function connect($name) {
        $conf = $this->getConf($name);

        try {
            $dsn     = "mysql:host={$conf['host']};port={$conf['port']};charset={$conf['charset']};dbname={$conf['db']}";
            $options = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            ];
            isset($conf['options']['persist']) &&
                $options[\PDO::ATTR_PERSISTENT] = (bool)$conf['options']['persist'];
            isset($conf['options']['timeout']) &&
                $options[\PDO::ATTR_TIMEOUT] = (bool)$conf['options']['timeout'];

            $this->conn = new \PDO($dsn, $conf['user'], $conf['pass'], $options);
        } catch (\Exception $exc) {
            // 暂时继续外抛
            throw $exc;
        }
        return $this;
    }

    /**
     *
     */
    public function close() {
        unset($this->conn);
    }

    public function query(array $query, $fields, $sort, $rows, $start = 0) {

    }

    /**
     *
     * @return \PDO
     */
    public function getConn() {
        return $this->conn;
    }

}
