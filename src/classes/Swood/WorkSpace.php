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
 * Description of WorkSpace
 *
 * @author andares
 */
class WorkSpace {
    private $path;
    private $is_test;

    public function __construct($path, $is_test = false) {
        if (!file_exists($path)) {
            throw new \RuntimeException("work dir is not exists");
        }

        $this->path     = $path;
        $this->is_test  = $is_test;

        // init.php
        require "$this->path/init.php";

    }

    public function init($conf_scene, $debug_level = null) {
        $autoload_path  = $this->getDir('classes');
        $conf_path      = $this->getDir('conf');

        if (!$this->is_test) {
            $result = $this->checkEnv([
                $autoload_path,
                $conf_path,
            ]);
            if (!$result) {
                throw new \RuntimeException('env check fail');
            }
        }

        // autoload 同时导入 dock.instance
        $autoload = \Swood\Dock::select('swood')['autoload'];
        $autoload->import($autoload_path);
        \Swood\Dock::select('instance')['autoload'] = $autoload;

        // 载入配置
        $conf = new Conf($conf_path, $conf_scene);
        \Swood\Dock::select('instance')['conf'] = $conf;

        // 设置debug level
        if ($debug_level !== null) {
            D::setLevel($debug_level);
        } else {
            D::setLevel($conf->get('swood', 'swood')['debug']['level']);
        }
    }

    public function getDir($dir) {
        static $cache = [];
        if (!isset($cache[$dir])) {
            $cache[$dir] = $this->path . DIRECTORY_SEPARATOR . $dir;
        }
        return $cache[$dir];
    }

    public function __invoke($dir) {
        return $this->getDir($dir);
    }

    /**
     * 基础环境检测
     *
     * @todo checkEnv完善
     */
    private function checkEnv($check_path) {
        if (!function_exists('\swoole_version')) {
            throw new \RuntimeException("Swood is depend swoole module");
        }

        if (PHP_VERSION_ID < 50600) {
            D::ec(" * Check PHP version ......... " . phpversion() . " >> Fail! (require 5.6)");
            return false;
        } else {
            D::ec(" * Check PHP version ......... " . phpversion() . " - OK.");
        }

        $version = explode('.', \swoole_version());
        $require = [
            1, 8, 1,
        ];
        foreach ($require as $key => $value) {
            if ($value[$key] > $version[$key]) {
                D::ec(" * Check Swoole version ...... " . \swoole_version() . " >> Fail! (require 1.8.1)");
                return false;
            }
        }
        D::ec(" * Check Swoole version ...... " . \swoole_version() . " - OK.");

        // 检查目录
        foreach ($check_path as $path) {
            if (!file_exists($path)) {
                throw new \RuntimeException("work dir [$this->path] is incomplete. ($path not exist)");
            }
        }

        D::ec('');
        return true;
    }

}
