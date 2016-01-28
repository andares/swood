<?php
namespace Swood\Schema;
use Swood\Debug as D;

/**
 * 实现一个可维护的kv行结构。
 * 支持填充数据（包括初始化）；
 * 字段检查与过滤器实现，通过 _confirm_ 方法；
 * 提供toArray()， 支持_to_array 方法扩展
 * 支持聚合迭代器；
 * 支持ArrayAccess；
 * 重载数据访问，支持数据操作事件，通过 _set_, _get_ 实现
 * 内/外部结构定义，支持默认值，字段类型，映射关系，说明。
 *
 * 字段类型见常量定义
 *
 *
 * @author andares
 */
abstract class Meta implements \ArrayAccess, \IteratorAggregate {
    use Loader;

    const TYPE_ARRAY       = 1;
    const TYPE_CALLBACK    = 2;
    const TYPE_BOOL        = 3;
    const TYPE_FLOAT       = 4;
    const TYPE_INT         = 5;
    const TYPE_STRING      = 6;

    /**
     * 数据
     * @var array
     */
    protected $_data = [];

    /**
     * 构造器
     * @param array $data
     */
    public function __construct($data = null) {
        $data && $this->fill($data);
    }

    /**
     * 确认数据
     * @throws \UnexpectedValueException
     */
    public function confirm() {
        foreach (static::getSchema() as $name => $info) {
            $value  = $this->$name;
            $this->_confirmField($name, $value, $info);
        }
    }

    /**
     * 确认单条字段
     * @param type $name
     * @param type $value
     * @param type $info
     * @throws \UnexpectedValueException
     */
    protected function _confirmField($name, $value, $info) {
        $method = $this->_getOverloadMethod('_confirm_', $name);
        if ($method) {
            $this->$name = $this->$method($value, $info);
        } else {
            if ($value === null) {
                throw new \UnexpectedValueException("field [$name] could not be null");
            }

            // 自动类型处理
            if (isset($info['type'])) {
                switch ($info['type']) {
                    case self::TYPE_BOOL:
                        !is_bool($value) && $this->$name = boolval($value);
                        break;
                    case self::TYPE_FLOAT:
                        !is_float($value) && $this->$name = floatval($value);
                        break;
                    case self::TYPE_INT:
                        !is_int($value) && $this->$name = intval($value);
                        break;
                    case self::TYPE_STRING:
                        !is_string($value) && $this->$name = strval($value);
                        break;

                    case self::TYPE_ARRAY:
                        if (!is_array($value)) {
                            throw new \UnexpectedValueException("field [$name] type error");
                        }
                    case self::TYPE_CALLBACK:
                    default:
                        break;
                }
            }
        }
    }

    /**
     * 填充数据
     * @param array $data
     * @return boolean
     */
    public function fill($data) {
        if (!is_array($data) && !is_object($data)) {
            throw new \InvalidArgumentException("fill data error");
        }

        foreach (static::getSchema() as $name => $info) {
            isset($data[$name]) && $this->$name = $this->_transFromRaw($data[$name], $info);
        }
    }

    /**
     * 填充数据的魔术方法
     * @param type $data
     */
    public function __invoke($data) {
        $this->fill($data);
    }

    /**
     * 转换到数组
     * @param boolean $by_key
     * @return array
     */
    public function toArray($by_key = false) {
        $arr = [];
        foreach (static::getSchema() as $name => $info) {
            $key = ($by_key && isset($info['key'])) ? $info['key'] : $name;
            $arr[$key] = $this->_transFromField($this->$name, $info);
        }
        return $arr;
    }

    /**
     * meta -> array 的单个字段输出扩展
     * @param mixed $value
     * @param array $info
     * @return mixed
     */
    protected function _transFromField($value, array $info) {
        return $value;
    }

    /**
     * array -> meta 的单个字段输出扩展
     * @param mixed $value
     * @param array $info
     * @return mixed
     */
    protected function _transFromRaw($value, array $info) {
        return $value;
    }

    /**
     * 生成重载方法
     * @param type $prefix
     * @param type $name
     * @return boolean|string
     */
    protected function _getOverloadMethod($prefix, $name) {
        $method = "$prefix$name";
        if (method_exists($this, $method)) {
            return $method;
        }
        return false;
    }

    protected static function _getKey($name) {
        $schema = static::getSchema();
        if (!isset($schema[$name])) {
            throw new \DomainException("field not exists: $name");
        }
        return isset($schema[$name]['key']) ? $schema[$name]['key'] : $name;
    }

    /**
     * 重载
     */
    public function __get($name) {
        $key = static::_getKey($name);

        if (isset($this->_data[$key])) {
            $method = $this->_getOverloadMethod('_get_', $name);
            return $method ? $this->$method() : $this->_data[$key];
        }

        $this->_data[$key] = static::getSchema()[$name]['default'];
        return $this->_data[$key];
    }

    public function __set($name, $value) {
        $key = static::_getKey($name);

        $method = $this->_getOverloadMethod('_set_', $name);
        if ($method) {
            $value  = $this->$method($value);
        }
        $this->_data[$key] = $value;
    }

    public function __isset($name) {
        return isset(static::getSchema()[$name]);
    }

    public function __unset($name) {
        $key = static::_getKey($name);

        if (static::getSchema()[$name]['default'] === null){
            throw new \UnexpectedValueException("the field [$name] could not be unset");
        }

        $this->_data[$key] = static::getSchema()[$name]['default'];
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
