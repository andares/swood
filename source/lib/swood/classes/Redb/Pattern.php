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
 * Description of Pattern
 *
 *
 * @author andares
 */
trait Pattern {
    protected static $_model_space = '';

    protected static function _getModelSpace() {
        if (!static::$_model_space) {
            $space = str_replace('\\Entity\\', '\\Model\\', __CLASS__);
            static::$_model_space = $space;
        }
        return static::$_model_space;
    }

    protected static function _wrapIds(array $ids) {
        $wrapped_ids = [];
        foreach ($ids as $key => $id) {
            $wrapped_ids[$key] = static::_wrapId($id);
        }
        return $wrapped_ids;
    }

    abstract public static function genId();
    abstract protected static function _wrapId($id);
    abstract protected static function _load($id);
    abstract protected static function _loadList(array $ids);
    abstract protected function _create($update);
    abstract protected function _update($update);
    abstract protected function _delete();
}
