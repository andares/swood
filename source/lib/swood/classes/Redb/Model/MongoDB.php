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

namespace Redb\Model;
use Swood\Debug as D;

/**
 * Description of MongoDB
 *
 * @author andares
 */
class MongoDB extends Model {

    protected static function _read($id, \Redb\Driver\Driver $driver) {
        $collection = static::_getCollection($driver);
        $row = $collection->findOne([static::$_id_field => $id]);
        return $row;
    }

    protected static function _readByIds(array $ids, \Redb\Driver\Driver $driver) {
        $collection = static::_getCollection($driver);
        $cursor = $collection->find([static::$_id_field => ['$in' => $ids]]);
        foreach ($cursor as $row) {
            yield $row;
        }
    }

    protected static function _create($id, array $data, \Redb\Driver\Driver $driver) {
        $collection = static::_getCollection($driver);

        $cond   = [static::$_id_field => $id];
        $update = ['$set' => $data];
        return $collection->update($cond, $update, ['upsert' => true]);
    }

    protected static function _update($id, array $data, \Redb\Driver\Driver $driver) {
        $data   = $this->_getUpdateFields($data);
        if (!$data) {
            return true;
        }

        $collection = static::_getCollection($driver);

        $cond   = [static::$_id_field => $id];
        $update = ['$set' => $data];
        return $collection->update($cond, $update, ['upsert' => true]);
    }

    protected static function _delete($id, \Redb\Driver\Driver $driver) {
        $collection = static::_getCollection($driver);

        $cond   = [static::$_id_field => $id];
        return $collection->remove($cond);
    }

    /**
     *
     * @return \MongoCollection
     */
    protected static function _getCollection(\Redb\Driver\MongoDB $driver) {
        return $driver->getCollection(static::$_name);
    }

}
