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
use Swood\Debug as D;

/**
 * 查询类
 *
 * @author andares
 */
class Query {
    /**
     * 排序模式
     */
    const SORTMODE_ASC  = 1;
    const SORTMODE_DESC = -1;

    /**
     * 基于查询model字段值的条件
     * @var array
     */
    private $conds  = [];

    /**
     * 和model整合好的查询条件
     * @var array
     */
    private $conds_built = [];

    /**
     * 原生条件参数
     * @var array
     */
    private $raw    = [];

    /**
     * 要查询的字段，为空数组时为默认。大多数driver中默认均为全部
     * @var array
     */
    private $fields = [];

    /**
     * 排序参数。
     * @var array
     */
    private $sort   = [];

    /**
     * 跳过条目数
     * @var int
     */
    private $skip  = 0;

    /**
     * 查询限制数量
     * @var int
     */
    private $limit = 10;

    /**
     *
     * @var Entity
     */
    private $entity_class = null;

    /**
     * 构造器
     * @param type $entity_class
     */
    public function __construct($entity_class = null) {
        $this->entity_class = $entity_class;
    }

    /**
     * setCond()的魔术方法别名
     * @return self
     */
    public function __invoke(...$args) {
        return $this->setCond(...$args);
    }

    /**
     * 设置条件
     *
     * @param string $field 字段名。如果传入数组，则operator参数变为group
     * @param string $operator 操作符，目前支持的有： =, >, >=, <, <=, !=, in, nin, %
     * @param string $group 条件分组，默认在0组
     * @return self
     */
    public function setCond($field, $operator = null, $group = 0) {
        if (is_array($field)) {
            $operator !== null && $group = $operator;
            $this->conds[$group] = $field;
        } else {
            $this->conds[$group][] = [$field, $operator];
        }

        return $this;
    }

    /**
     * 设置原生条件
     * @param array $raw
     * @param type $group
     * @return \Redb\Query
     */
    public function setRaw(array $raw, $group = 0) {
        $this->raw[$group] = $raw;
        return $this;
    }

    /**
     * 获取基于model生成后的条件数组
     * @return array
     */
    public function getConds() {
        $conds = $this->conds_built;
        return (count($conds) == 1 && isset($conds[0])) ? $conds[0] : $conds;
    }

    /**
     * 基于model构建条件数组
     * @params Model\Model $model
     * @return array
     * @throws \UnexpectedValueException
     */
    private function buildConds($model) {
        $conds = [];
        foreach ($this->conds as $group => $fields) {
            foreach ($fields as $field_cond) {
                $value = $model[$field_cond[0]];
                if ($value == null) {
                    throw new \UnexpectedValueException("query condition [$field_cond[0]] is lost");
                }

                $field_cond[] = $value;
                $conds[$group][] = $field_cond;
            }
        }
        return $conds;
    }

    /**
     * 获取原生查询条件
     * @return array
     */
    public function getRaw() {
        return (count($this->raw) == 1 && isset($this->raw[0])) ? $this->raw[0] : $this->raw;
    }

    /**
     * 将model条件和原生条件基于group合并后返回
     * @return array
     */
    public function getMixed() {
        $conds  = $this->conds_built;
        foreach ($this->raw as $group => $raw_conds) {
            if (isset($conds[$group])) {
                $conds[$group] = array_merge($conds[$group], $raw_conds);
            } else {
                $conds[$group] = $raw_conds;
            }
        }
        return (count($conds) == 1 && isset($conds[0])) ? $conds[0] : $conds;
    }

    /**
     * 设置/获取查询字段列表。
     * 不设参数则是获取返回，给参数则是设置。
     *
     * @return array|self
     */
    public function selectFields(...$fields) {
        if (!$fields) {
            return $this->fields;
        }

        if (is_array($fields[0])) {
            $fields = $fields[0];
        }
        $this->fields = array_merge($this->fields, $fields);
        return $this;
    }

    /**
     *
     * @param string|array $field 排序字段。如果为数组则包括了mode，一次性赋值多项。
     * @param int $mode 排序模式，1 顺序，2 倒序
     * @return self
     */
    public function sort($field, $mode = 0) {
        if (is_array($field)) {
            $this->sort = $field;
        } else {
            $this->sort[$field] = $mode;
        }
        return $this;
    }

    /**
     * 获取排序参数
     * @return array
     */
    public function getSort() {
        return $this->sort;
    }

    /**
     * 跳过条目
     * @param int $number
     * @return \Redb\Query
     */
    public function skip($number = 0) {
        $this->skip = $number;
        return $this;
    }

    /**
     * 获取skip
     * @return int
     */
    public function getSkip() {
        return $this->skip;
    }

    /**
     * 每次取时限制的数量
     * @param int $number
     * @return \Redb\Query
     */
    public function limit($number = 10) {
        $this->limit = $number;
        return $this;
    }

    /**
     * 获取limit
     * @return int
     */
    public function getLimit() {
        return $this->limit;
    }

    /**
     * 查询
     * @param \Redb\Model\Model $model
     * @return Result
     */
    public function by(Model\Model $model) {
        $this->conds_built = $this->buildConds($model);

        if ($this->entity_class) {
            $call   = [$this->entity_class, 'query'];
            $result = $call($this, $model);
        } else {
            $result = $model->query($this);
        }

        return $result;
    }

    /**
     * 翻到下一页
     * @return \Redb\Query
     */
    public function more($previous = false) {
        if ($previous) {
            $this->skip -= $this->limit;
            $this->skip = max(0, $this->skip);
        } else {
            $this->skip += $this->limit;
        }
        return $this;
    }

    /**
     *
     * @return Model\Model
     */
    public function getModel() {
        $call = [$this->entity_class, 'getQueryModel'];
        return $call();
    }

}
