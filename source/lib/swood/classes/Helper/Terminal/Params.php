<?php
namespace Helper\Terminal;
use Swood\Debug as D;

/**
 * 命令行参数解析器
 *
 * 规则如下：
 * 单横线后面跟单个字符，一律为开关参数，存在则返回1，如：
 *  -s -m 则返回s=1, m=1
 *
 * 单横线后面跟多个字符，表示可赋值参数，参数与命名间以空格分隔，如：
 *  -mode http -host 127.0.0.1 则返回mode='http', host='127.0.0.1'
 *
 * 双横线表示之后参数名与值以等号=分隔，如果不带等号则返回1，如：
 *  --mode=http --retry 则返回mode='http', retry=1
 *
 * @author andares
 */
class Params {
    private $_argv;
    protected $_main    = [];
    protected $_current = '';

    protected static $options_mapping = [];

    public function __construct($argv = null) {
        $this->_argv = $argv;
        $this->_argv && $this->parse($argv);
    }

    public function getMain() {
        return $this->_main;
    }

    public function getArgv() {
        return $this->_argv;
    }

    public function parse($argv) {
        foreach ($argv as $param) {
            $param = trim($param);
            if (!$param) {
                continue;
            }

            // current 处理
            if ($this->_current) {
                $name = $this->_current;
                if (!$this->setValue($name, $param)) {
                    $this->_main[] = $param;
                }

                continue;
            }

            if ($param[0] == '-') {
                if ($param[1] == '-') {
                    $param  = explode('=', substr($param, 2));
                    $name   = $this->processName($param[0]);

                    $value  = isset($param[1]) ? trim($param[1]) : 1;
                    $this->setValue($name, $value);
                } else {
                    $name   = $this->processName(substr($param, 1));
                    $name && $this->_current = $name;
                }
            } else {
                $this->_main[] = $param;
            }
        }
    }

    protected function processName($original_name) {
        $original_name = str_replace('-', '_', trim($original_name));

        // mapping处理
        $name = isset(static::$options_mapping[$original_name]) ? static::$options_mapping[$original_name] : $original_name;

        // 单字母配置直接作为开关参数
        if (strlen($name) == 1) {
            $this->setValue($name, 1);
            return null;
        }

        return $name;
    }

    protected function setValue($name, $value) {
        try {
            // 一旦产生option赋值操作, 即重置当前option
            $this->_current = '';

            $this->$name = $value;
        } catch (\DomainException $exc) {
            D::logError($exc);
            return false;
        }

        return true;
    }
}

