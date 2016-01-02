<?php
namespace Redb;

/**
 * 数据Model。
 * 这层要设法抽出来，Model和任何数据方案无关。
 * 只负责记录和维护数据的唯一性，变化情况。
 * 之后丢到某个容器中处理。
 *
 * Meta\Row的逻辑直接放进model基类:
 * 针对DB而设计的复杂Meta对象。
 * 支持数据校验，穿透访问以及字段重载等功能。
 *
 * @property int $updatedat 更新时间
 * @property int $createdat 创建时间
 *
 * @author andares
 */
abstract class Model {
    protected static $_structure = [];

    public function getId() {}

    public function genId() {}

    /**
     * 重载
     */
    public function __get($name) {
        if (!isset(static::$_struct[$name])) {
            throw new Error(2);
        }

        // wrap扩展支持
        $method = "get" . ucfirst($name);
        if (method_exists($this, $method)) {
            return $this->$method();
        }

        return isset($this->_data[$name]) ? $this->_data[$name] : static::$_struct[$name];
    }

    public function __set($name, $value) {
        if (!isset(static::$_struct[$name])) {
            throw new Error(2);
        }

        // wrap扩展支持
        $method = "set" . ucfirst($name);
        if (method_exists($this, $method)) {
            return $this->$method($value);
        }

        $this->_data[$name] = $value;
    }
}
