<?php
namespace Helper;

/**
 * 提供的数据结构维护功能：
 * toArray()
 * 聚合迭代器
 * ArrayAccess
 * 对_data访问重载
 * 结构和默认值定义
 * 数据填充（包括初始化），数据填充前缀
 * confirm
 *
 * @author andares
 */
abstract class Meta implements \ArrayAccess, \IteratorAggregate {
    protected static $_struct = [];

    protected $_data = [];

    public function __construct($data = null) {
        $data && $this->fill($data);
    }

    public function confirm() {
        foreach (static::$_struct as $name => $default) {
            // 默认值设置
            if (!isset($this->_data[$name]) || $this->_data[$name] === null) {
                if ($default === null) {
                    throw new \UnexpectedValueException("field [$name] could not be null");
                }
                $this->_data[$name] = $default;
            }

            // 检测
            $confirm_method = "confirm_$name";
            if (method_exists($this, $confirm_method)) {
                $this->_data[$name] = $this->$confirm_method($this->_data[$name]);
            }
        }
    }

    /**
     * 填充数据
     * @param array $data
     * @param string $prefix
     * @return boolean
     */
    public function fill($data, $prefix = '') {
        if (!is_array($data) && !is_object($data)) {
            throw new \InvalidArgumentException("fill data error");
        }

        foreach (static::$_struct as $name => $default) {
            $from   = $prefix ? ($prefix . $name) : $name;
            $this->_data[$name]  = isset($data[$from]) ? $data[$from] : $default;
        }
    }

    public function __invoke($data, $prefix = '') {
        $this->fill($data, $prefix);
    }

    public function getStruct() {
        return static::$_struct;
    }

    public function toArray(array $structure = []) {
        $arr = [];
        foreach (static::$_struct as $name => $default) {
            $arr[$name] = isset($this->_data[$name]) ? $this->_data[$name] : $default;
        }
        return $arr;
    }

    /**
     * 重载
     */
    public function __get($name) {
        if (!isset(static::$_struct[$name])) {
            throw new \DomainException("field not exists: $name");
        }
        return isset($this->_data[$name]) ? $this->_data[$name] : static::$_struct[$name];
    }

    public function __set($name, $value) {
        if (!isset(static::$_struct[$name])) {
            throw new \DomainException("field not exists: $name");
        }
        $this->_data[$name] = $value;
    }

    public function __isset($name) {
        return isset(static::$_struct[$name]);
    }

    public function __unset($name) {
        return true;
    }

    /**
     * Array Access
     * @param mixed $offset
     * @return mixed
     */
    public function offsetExists($offset) {
        return isset($this->$offset);
    }

    public function offsetGet($offset) {
        return $this->$offset;
    }

    public function offsetSet($offset, $value) {
        $this->$offset = $value;
    }

    public function offsetUnset($offset) {
        $this->$offset = null;
    }

    /**
     * 聚合迭代器
     * @return array
     */
    public function getIterator() {
        return new \ArrayIterator($this->toArray());
    }
}
