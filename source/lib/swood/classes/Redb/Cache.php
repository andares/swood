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
 * Redb的缓存类
 *
 * @author andares
 */
class Cache {
    /**
     *
     * @var array
     */
    private static $entities = [];

    /**
     * 缓存中每个entity的数量限制
     * @var int
     */
    private static $cache_limit = 10000;

    /**
     *
     * @param type $entity_name
     * @param type $id
     * @return Entity
     */
    public static function load($entity_name, $id) {
        return isset(self::$entities[$entity_name][$id]) ?
            self::$entities[$entity_name][$id] : null;
    }

    /**
     * 从一个id列表中分离出未载入的id列表
     * @param string $entity_name
     * @param array $ids
     * @return array
     */
    public static function getUnloadIds($entity_name, $ids) {
        $unload_ids = [];
        foreach ($ids as $id) {
            if (!isset(self::$entities[$entity_name][$id])) {
                $unload_ids[] = $id;
            }
        }
        return $unload_ids;
    }

    /**
     * 向缓存中存储entity
     * @param \Redb\Entity $entity
     * @param type $auto_gc
     * @throws \RuntimeException
     * @throws \LogicException
     */
    public static function store(Entity $entity, $auto_gc = true) {
        $entity_name = $entity->getEntityName();
        $id = $entity->getId();
        if (!$id) {
            throw new \RuntimeException("can not store an entity [$entity_name] without id");
        }

        // 防重复
        if (isset(self::$entities[$entity_name][$id])) {
            throw new \LogicException("entity [$entity_name] #$id can not store again");
        }

        // gc处理
        if ($auto_gc && isset(self::$entities[$entity_name]) &&
            count(self::$entities[$entity_name]) >= self::$cache_limit) {
            self::gc($entity_name);
        }

        // 存入
        self::$entities[$entity_name][$id] = $entity;

        // 再次判断
        if (count(self::$entities[$entity_name]) > self::$cache_limit) {
            throw new \RuntimeException("entity [$entity_name] cache is full");
        }
    }

    /**
     * 在缓存中移除entity
     * @param type $entity_name
     * @param type $id
     */
    public static function remove($entity_name, $id) {
        unset(self::$entities[$entity_name][$id]);
    }

    /**
     * 垃圾回收
     * @param type $entity_name
     */
    public static function gc($entity_name) {
        foreach (self::$entities[$entity_name] as $id => $entity) {
            /* @var $entity Entity */
            // TODO 暂时gc时没有对有过修改的entity进行处理
            unset(self::$entities[$entity_name][$id]);
        }
    }

    /**
     * 获取缓存中数据
     * @return type
     */
    public static function getAll() {
        return self::$entities;
    }

    /**
     * 将缓存中的所有数据存档
     */
    public static function saveAll() {
        foreach (self::$entities as $list) {
            foreach ($list as $entity) {
                /* @var $entity Entity */
                $entity->save();
            }
        }
    }

    /**
     * 清除所有缓存
     */
    public static function clearAll() {
        self::$entities = [];
    }

}
