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

namespace Redb;

/**
 * 连接集群
 *
 * @author andares
 */
class Cluster {
    /**
     * cluster实例
     * @var Cluster[]
     */
    private static $instance = [];

    /**
     * 集群名
     * @var string
     */
    private $name;

    /**
     * driver类名
     * @var string
     */
    private $driver_type;

    /**
     * 当前集群环
     * @var array
     */
    private $ring = [];

    /**
     * 当前集群中节点总数
     * @var array
     */
    private $ring_total = 0;

    /**
     * 待迁移集群
     * @var Cluster|null
     */
    private $pre_cluster = null;

    /**
     * 获取集群单例
     * @param string $name
     * @return self
     */
    public static function get($name) {
        if (!isset(self::$instance[$name])) {
            $conf = self::loadConf($name);

            self::$instance[$name] = new self($name, $conf['driver_type'], $conf['ring']);
            if ($conf['ring_pre']) {
                self::$instance[$name]->setPreCluster(new self($name,
                    $conf['driver_type'], $conf['ring_pre']));
            }
        }

        return self::$instance[$name];
    }

    /**
     * 载入配置
     * @param string $name
     * @return array
     */
    private static function loadConf($name) {
        return \Swood\Dock::select('instance')['conf']->get('redb', "cluster/$name");
    }

    /**
     * 构造器
     * @param string $name
     * @param string $driver_type
     * @param array $ring
     */
    public function __construct($name, $driver_type, array $ring) {
        $this->name         = $name;
        $this->driver_type  = $driver_type;
        $this->ring         = $ring;
        $this->ring_total   = count($ring);
    }

    /**
     * 设置待迁移集群
     * @param self $cluster
     */
    public function setPreCluster(self $cluster) {
        $this->pre_cluster = $cluster;
    }

    /**
     * 获取待迁移集群
     * @return self
     */
    public function getPreCluster() {
        return $this->pre_cluster;
    }

    /**
     * 获取数据连接对象
     * @param mixed $id
     * @return Driver\Driver
     */
    public function getDriver($id) {
        $conn_name = $this->getConnName($id);
        return Connections::get($this->driver_type, $conn_name);
    }

    /**
     * 获取集群中所有driver
     */
    public function getAllDrivers() {
        foreach ($this->ring as $conn_name) {
            yield $this->getDriverByName($conn_name);
        }
    }

    /**
     *
     * @param string $conn_name
     * @return Driver\Driver
     */
    public function getDriverByName($conn_name) {
        return Connections::get($this->driver_type, $conn_name);
    }

    /**
     * 根据id获取集群中连接名
     * @param mixed $id
     * @return string
     */
    public function getConnName($id) {
        return $this->ring[self::consistentHashing($id, $this->ring_total)];
    }

    /**
     * 一致性哈希
     *
     * @todo 一致性哈希分布算法有待改进
     *
     * @param mixed $id
     * @param int $total
     * @param int $offset
     * @return int
     */
    private static function consistentHashing($id, $total, $offset = 0) {
        $val = md5($id);
        $val = hexdec($val[0] . $val[1] . $val[2] . $val[3] . $val[4] . $val[5]);
        return (floor($val / (16777216) * $total) + $offset) % $total;
    }
}
