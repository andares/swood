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

namespace Swood\App;
use Swood\Debug as D;

/**
 * Description of Init
 *
 * @author andares
 */
class Init {
    public static function init($root, $is_test = false) {
        static::composerSetup($root);

        // 错误转违例
        set_error_handler([get_called_class(), 'raiseError'], E_ALL ^ E_STRICT);

        // 全局违例捕获
        if (!$is_test) {
            set_exception_handler([get_called_class(), 'raiseException']);
        }

        // 注册进程结束方法
        register_shutdown_function([get_called_class(), 'processShutdown']);
    }

    protected static function composerSetup($root) {
        $autoload = $root . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
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

    function processShutdown() {
        // do something..
    }
}
