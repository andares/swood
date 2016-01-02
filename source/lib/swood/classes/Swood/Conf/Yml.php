<?php
namespace Swood\Conf;

/**
 * Description of Yml
 *
 * @author andares
 */
class Yml implements \ArrayAccess, \IteratorAggregate {
    protected $data;
    protected $index    = [];

    private $schema = [];

    public function __construct($file) {
        $this->load($file);
    }

    public function search($field, $value) {
        if (isset($this->index[$field][$value])) {
            $keys = is_array($this->index[$field][$value]) ? $this->index[$field][$value] : [$this->index[$field][$value]];
        } else {
            $keys = [];
        }

        foreach ($keys as $key) {
            yield $this->data[$key];
        }
    }

    public function load($file) {
        $this->data = $this->loadContent($file);

        // 载入schema
        $this->loadSchema($file);

        // 生成索引
        isset($this->schema['index']) && $this->buildIndex($this->schema['index']);
    }

    protected function loadContent($file) {
        return \yaml_parse_file("$file.yml");
    }

    protected function loadSchema($file) {
        $file .= ".sch.yml";
        if (!file_exists($file)) {
            return false;
        }

        $this->schema = \yaml_parse_file($file);
    }

    private function buildIndex() {
        // 唯一索引创建
        if (isset($this->schema['index'])) {
            foreach ($this->data as $key => $row) {
                foreach ($this->schema['index'] as $field) {
                    $this->index[$field][$row[$field]] = $key;
                }
            }
        }

        // 多值索引创建
        if (isset($this->schema['mindex'])) {
            foreach ($this->data as $key => $row) {
                foreach ($this->schema['mindex'] as $field) {
                    $this->index[$field][$row[$field]][] = $key;
                }
            }
        }
    }

    public function toArray() {
        return $this->data;
    }

    public function offsetExists($offset) {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset) {
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value) {
        $this->data[$offset] = $value;
    }

    public function offsetUnset($offset) {
        unset($this->data[$offset]);
    }

    /**
     * 聚合迭代器
     * @return \ArrayIterator
     */
    public function getIterator() {
        return new \ArrayIterator($this->toArray());
    }
}

