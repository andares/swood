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
 * Description of Entity
 *
 * @author andares
 *
 * @property int $createdat 创建时间，与id一样是必须有的字段
 */
abstract class Entity extends Data {
    const SAVEMETHOD_NONE   = 0;
    const SAVEMETHOD_UPDATE = 1;
    const SAVEMETHOD_CREATE = 2;
    const SAVEMETHOD_DELETE = 4;

    /**
     * Entity表名
     * @var string
     */
    protected static $_name     = '';

    /**
     * 更新状态，见上面常量定义
     * @var int
     */
    protected $_save_method = self::SAVEMETHOD_UPDATE;

    /**
     * 有更新过的字段列表
     * @var array
     */
    protected $_update_fields    = [];

    /**
     * confirm过后确定要变更的字段
     * @var array
     */
    protected $_update_confirmed = [];

    /**
     *
     * @var boolean
     */
    private $_fill_when_load = false;

    /**
     * 获得entity名
     * @return string
     */
    public function getEntityName() {
        return static::$_name;
    }

    /**
     * 根据id读取单个Entity
     * @param mixed $id
     * @return Entity
     */
    public static function load($id) {
        $id     = static::_wrapId($id);
        $entity = Cache::load(static::$_name, $id);
        if (!$entity) {
            $entity   = static::_load($id);
            if (!$entity) {
                return $entity;
            }

            // hook
            $entity->_hook_load();

            // 放入缓存
            Cache::store($entity);
        }
        return $entity;
    }

    /**
     * 根据一组id列表读取多个Entity
     * @param array $ids
     * @return array
     */
    public static function loadList(array $ids) {
        $ids = static::_wrapIds($ids);

        $unload_ids = Cache::getUnloadIds(static::$_name, $ids);
        foreach (static::_loadList($unload_ids) as $entity) {
            // hook
            $entity->_hook_load();

            // 不进行gc，超限自然报错
            Cache::store($entity, false);
        }

        $list = [];
        foreach ($ids as $id) {
            $entity = Cache::load(static::$_name, $id);
            if ($entity) {
                $list[$entity->getId()] = $entity;
            }
        }

        return $list;
    }

    /**
     * 创建一个Entity并放入Cache
     * @param array $data
     * @param mixed $id
     * @param bool $auto_gc
     * @return Entity
     */
    public static function create(array $data = [], $id = null) {
        // 生成id
        if ($id) {
            $id = static::_wrapId($id);
        } else {
            $id = static::genId();
        }

        // 创建类
        $class  = get_called_class();
        $entity = new $class($data);
        /* @var $entity Entity */
        $entity->setId($id);

        // hook
        $entity->_hook_create();

        // 放入缓存
        Cache::store($entity);
        return $entity;
    }

    /**
     * 确认数据，并将之前的改动加入更新列表。
     * 只有confirm过的记录才会被更新或创建。
     */
    public function confirm() {
        // 父类meta处理
        parent::confirm();

        switch ($this->_save_method) {
            case self::SAVEMETHOD_NONE:
            case self::SAVEMETHOD_DELETE:
            case self::SAVEMETHOD_CREATE: // 这里被标记为create后应该是已经confirm过了
                return false;
            case self::SAVEMETHOD_UPDATE:
            default:
                // do nothing..
                break;
        }

        // 更新更新时间
        if (isset($this->updatedat)) {
            $this->updatedat = time();
        }

        // 判断是创建还是更新
        if ($this->createdat) {
            $this->_confirmUpdate();
        } else {
            // 创建时间
            $this->createdat = time();

            // 设置为需要创建
            $this->_save_method = self::SAVEMETHOD_CREATE;
        }
    }

    /**
     * 是否有更新
     * @return boolean
     */
    public function hasUpdate() {
        $result = false;

        switch ($this->_save_method) {
            case self::SAVEMETHOD_UPDATE:
                $result = $this->_update_confirmed ? true : false;
                break;
            case self::SAVEMETHOD_CREATE:
            case self::SAVEMETHOD_DELETE:
                $result = true;
                break;

            case self::SAVEMETHOD_NONE:
            default:
                // do nothing..
                break;
        }
        return $result;
    }

    /**
     * 保存至数据库
     * @return boolean
     */
    public function save() {
        // 无更新不存盘
        if (!$this->hasUpdate()) {
            return false;
        }

        $save_method = $this->_save_method;
        // hook
        $this->_hook_before_save($save_method);

        // 调pattern
        switch ($save_method) {
            case self::SAVEMETHOD_CREATE:
                if (!$this->_create()) { // 目前创建失败必须抛错
                    throw new \RuntimeException("entity create fail");
                }
                //  重置状态
                $this->_save_method = self::SAVEMETHOD_UPDATE;
                break;
            case self::SAVEMETHOD_UPDATE:
                if ($this->_update()) {
                    $this->clearUpdate();
                }
                break;
            case self::SAVEMETHOD_DELETE:
                if ($this->_delete()) {
                    $this->_save_method = self::SAVEMETHOD_NONE;
                }
                break;

            case self::SAVEMETHOD_NONE:
            default:
                // do nothing..
                break;
        }

        // hook
        $this->_hook_after_save($save_method);

        return true;
    }

    /**
     * 标记删除
     * @todo 目前只有读取后标记删除，统一事务处理。是否做不读取直接删除需要考量
     */
    public function delete() {
        $this->_save_method = self::SAVEMETHOD_DELETE;
    }

    /**
     * 从缓存中移除，等于也取消了更新
     */
    public function cancel() {
        Cache::remove($this->getEntityName(), $this->getId());
    }

    /**
     * 获取当前保存操作
     * @return int
     */
    public function getSaveMethod() {
        return $this->_save_method;
    }

    /**
     * 扩展字段类型处理
     * @param type $name
     * @param \Redb\class $value
     * @param array $info
     */
    protected function _confirmField($name, $value, array $info) {
        if (is_string($info['type'])) { // Struct 对象处理
            if ($value) { // 自动confirm Struct对象
                $value->confirm();

            } else { // 自动初始化
                $class = $info['type'];
                if (!$value || !($value instanceof $class)) {
                    $value = new $class();
                    /* @var $value \Swood\Schema\Struct */
                    $value->init();
                    $value->confirm();
                    $this->$name = $value;
                }
            }

        } elseif (is_array($info['type'])) { // Struct 对象集处理
             // Struct 对象集不会自动confirm所有
            if (!$value && !is_array($value)) {
                $this->$name = [];
            }
        }

        parent::_confirmField($name, $value, $info);
    }

    /**
     * 扩展字段类型处理
     * @param type $value
     * @param array $info
     * @return type
     */
    protected function _transFromField($value, array $info) {
        if (is_string($info['type'])) { // Struct 对象处理
            return $value->toArray();

        } elseif (is_array($info['type'])) { // Struct 对象集处理
            if (is_string($info['type']['unit'])) {
                $it = \Helper\Arr::traversalByDepth($value, $info['type']['depth']);
                foreach ($it as $c => $v) {
                    /* @var $v \Swood\Schema\Struct */
                    // 使用数组迭代器修改了 $value 中的值
                    $c[0][$c[1]] = $v->toArray();
                }
            }
        }

        // 直接返回
        return $value;
    }

    /**
     * 扩展字段类型处理
     * @param type $value
     * @param array $info
     * @return \Redb\class
     */
    protected function _transFromRaw($value, array $info) {
        if (is_string($info['type'])) { // Struct 对象处理
            $class  = $info['type'];
            $struct = new $class($value);
            return $struct;

        } elseif (is_array($info['type'])) { // Struct 对象集处理
            if (is_string($info['type']['unit'])) {
                $class  = $info['type'];
                $it     = \Helper\Arr::traversalByDepth($value, $info['type']['depth']);
                foreach ($it as $c => $v) {
                    // 使用数组迭代器修改了 $value 中的值
                    $struct = new $class($v);
                    $c[0][$c[1]] = $struct;
                }
            }
        }

        // 直接返回
        return $value;
    }

    /**
     * 清除此次更新，并恢复值到上次confirm之前的状态。
     * confirm之后无效
     */
    public function clearUpdate() {
        // 移除更新信息
        $this->_update_fields   = $this->_update_confirmed = [];
        // 重新载入数据
        $this->_reload();
    }

    /**
     * 标记某字段有所更新
     * @param type $name
     * @param type $value
     */
    public function fieldUpdated($name) {
        !isset($this->_update_fields[$name]) && $this->_update_fields[$name] = 1;
    }

    /**
     * 获取经过schema中键值映射的等更新字段
     *
     * @return array
     */
    public function getUpdateWithKey() {
        $update = [];
        foreach ($this->_update_confirmed as $field => $flag) {
            $value = $this->$field;
            $update[static::_getKey($field)] = is_object($value) ? $value->toArray() : $value;
        }
        return $update;
    }

    /**
     * 载入时填充，此方法不会标记update fields
     * @param array $data
     */
    public function fillWhenLoad(array $data) {
        // 开关变量绕开__set中对update fields的处理
        // TODO 这里的处理方案有待重构和改进
        $this->_fill_when_load = true;
        $this->fill($data);
        $this->_fill_when_load = false;
    }

    /**
     * 确认update fields中的数据到update confirmed中
     */
    private function _confirmUpdate() {
        // 处理update数据
        foreach ($this->_update_fields as $field => $flag) {
            $this->_update_confirmed[$field] = $this->$field;
        }

        // 重置fields
        $this->_update_fields = [];
    }

    /**
     * 对set重载做处理
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value) {
        // 备份修改前的值，这个在一次confirm之前只记录最早的原始值
        !$this->_fill_when_load && $this->fieldUpdated($name);
        parent::__set($name, $value);
    }

    /**
     * 以下为需要在pattern中扩展的方法
     */
    abstract public static function genId();
    abstract protected static function _wrapId($id);
    abstract protected static function _wrapIds(array $id);
    abstract protected static function _load($id);
    abstract protected function _reload();
    abstract protected static function _loadList(array $ids);
    abstract protected function _create();
    abstract protected function _update();
    abstract protected function _delete();

    /**
     * 可扩展的勾子方法
     */
    protected function _hook_load() {}
    protected function _hook_create() {}
    protected function _hook_before_save($save_method) {}
    protected function _hook_after_save($save_method) {}

    /**
     *
     * @return \Redb\Query
     */
    public static function query($statment = null) {
        $query = new Query($this);
        $query->setStatment();
        return $query;
    }


}
