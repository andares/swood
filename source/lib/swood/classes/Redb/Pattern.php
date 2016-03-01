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
 * 存储管理模式（策略）基类
 *
 *
 * @author andares
 */
trait Pattern {
    /**
     * 模型空间
     * @var string
     */
    protected static $_model_space = '';

    /**
     * 获取模型空间
     * @return string
     */
    protected static function _getModelSpace() {
        if (!static::$_model_space) {
            $space = str_replace('\\Entity\\', '\\Model\\', __CLASS__);
            static::$_model_space = $space;
        }
        return static::$_model_space;
    }

    abstract public function genId();
    abstract protected static function _wrapId($id);
    abstract public static function getQueryModel();
    abstract protected static function _query(Query $query, Model\Model $model);
    abstract protected static function _load($id);
    abstract protected function _reload();
    abstract protected static function _loadList(array $ids);
    abstract protected function _create();
    abstract protected function _update();
    abstract protected function _delete();

}
