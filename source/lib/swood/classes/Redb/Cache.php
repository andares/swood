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
 * Description of Cache
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

    public static function getUnloadIds($entity_name, $ids) {
        $unload_ids = [];
        foreach ($ids as $id) {
            if (!isset(self::$entities[$entity_name][$id])) {
                $unload_ids[] = $id;
            }
        }
        return $unload_ids;
    }

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

    public static function remove($entity_name, $id) {
        unset(self::$entities[$entity_name][$id]);
    }

    public static function gc($entity_name) {
        foreach (self::$entities[$entity_name] as $id => $entity) {
            /* @var $entity Entity */
            if (!$entity->hasUpdate()) {
                unset(self::$entities[$entity_name][$id]);
            }
        }
    }

    public static function getAll() {
        return self::$entities;
    }

    public static function saveAll() {
        foreach (self::$entities as $list) {
            foreach ($list as $entity) {
                /* @var $entity Entity */
                $entity->save();
            }
        }
    }

    /**
     *
     */
    public static function clearAll() {
        self::$entities = [];
    }

}
