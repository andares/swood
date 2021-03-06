<?php
namespace Swood;


/**
 * 调试类
 *
 * @author andares
 */
class Debug {
    /**
     *
     * @var int
     */
    protected static $level = 0;

    /**
     * 日志路径
     * @var string
     */
    public static $log_base_path = '/var/tmp/swood';

    /**
     *
     * @param int $level
     */
    public static function setLevel($level) {
        self::$level = intval($level);
    }

    /**
     *
     * @return int
     */
    public static function level() {
        return self::$level;
    }

    /**
     *
     * @param type $str
     */
    public static function ec($str) {
        echo $str, "\n";
    }

    /**
     *
     * @param mixed $var
     * @param string $title
     * @param int $level
     */
    public static function log($file = 'debug', ...$argv) {
        $output = self::d(...$argv);
        self::logger($file, $output);
    }

    /**
     *
     * @param string|\Exception $msg
     */
    public static function logError($msg) {
        if (!intval(\ini_get('log_errors'))) {
            return false;
        }

        if (is_object($msg) && $msg instanceof \Exception) {
            \error_log($msg->getMessage());
            \error_log($msg->getTraceAsString());
        } else {
            \error_log($msg);
        }
    }

    /**
     * 调试输出函数
     * @param mixed $var
     * @param string $title
     * @param int $level
     */
    public static function du(...$argv) {
        $output = self::d(...$argv);
        echo $output;
    }

    public static function dt($var) {
        echo self::d($var, '', -1);
    }

    public static function d($var, $title = '', $level = 1) {
        static $counter = 0;

        if (self::$level < $level) {
            return true;
        }
        $counter++;

        $output = "\n[" . date("Y-m-d H:i:s") . "] ";

        if ($title) {
            $output .= "#$title# ";
        }

        if (is_numeric($var) || is_string($var)) {
            $output .= "$var\n";
        } elseif (is_bool($var) || is_null($var)) {
            ob_start();
            var_dump($var);
            $output .= ob_get_clean();
        } else {
            $output .= "<<<EOT\n";
            ob_start();
            var_dump($var);
            $output .= ob_get_clean();
            $output .= "EOT;\n";
        }

        return $output;
    }

    /**
     * 私有日志记录方法
     * @param string $file
     * @param string $content
     * @param type $base_path
     * @param string $time
     * @return bool
     */
    public static function logger($file, $content, $base_path = null, $time = null) {
        // 基本数据获取
        !$base_path && $base_path = self::$log_base_path;
        !$time && $time = time();

        // 生成内容和写入文件路径
        $content    = date("Y-m-d H:i:s", $time) . '|' . $content . "\r\n";
        $file       = $base_path . DIRECTORY_SEPARATOR . $file . '.log';

        // 检查目录是否存在
        $path = dirname($file);
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        // 写入
        $fp     = fopen($file, 'a');
        if (!$fp) {
            return false;
        }
        fwrite($fp, $content);
        fclose($fp);
        return true;
    }
}
