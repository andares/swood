<?php

/*
 * Copyright (C) 2016 andares.
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

namespace Swood;
use Swood\Debug as D;

/**
 * Description of Init
 *
 * @author andares
 */
class Init {
    protected static $shutdown_funcions = [];

    public static function registerShutdownFunction(callback $func) {
        static::$shutdown_funcions[] = $func;
    }

    public static function init($composer_root, $is_test = false) {
        static::composerSetup($composer_root);

        $class = get_called_class();
        // 错误转违例
        set_error_handler([$class, 'raiseError'], E_ALL ^ E_STRICT);

        // 全局违例捕获
        if (!$is_test) {
            set_exception_handler([$class, 'raiseException']);
        }

        // 注册进程结束方法
        // TODO 之后改为event + hook的形式
        register_shutdown_function([$class, 'callShutdownFunctions']);
    }

    protected static function composerSetup($composer_root) {
        $autoload = $composer_root . DIRECTORY_SEPARATOR . 'vendor' .
            DIRECTORY_SEPARATOR . 'autoload.php';
        if (!file_exists($autoload)) {
            return false;
        }

        require $autoload;
    }

    public static function raiseError($errno, $errstr, $errfile, $errline, $errcontext = []) {
        if ($errno == E_STRICT) {
            return false;
        }

        D::logError("PHP Error[$errno]: $errstr in $errfile on line $errline");
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline, null);
    }

    public static function raiseException(\Exception $exception) {
        echo "Uncaught exception: " , $exception->getMessage(), "\n";
        if (\Swood\Debug::level()) {
            echo $exception->getTraceAsString();
        }

        // TODO 后续处理待考
        die();
    }

    public static function callShutdownFunctions() {
        foreach (static::$shutdown_funcions as $func) {
            $func();
        }
    }
}
