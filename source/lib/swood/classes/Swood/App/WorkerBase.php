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

namespace Swood\App;
use Swood\Debug as D;

/**
 * Description of Worker
 *
 * @todo 需要抽象出来fd的全局共享存储方案提供给使用者
 *
 * @author andares
 */
abstract class WorkerBase {
    /**
     *
     * @var App
     */
    public $app;

    /**
     *
     * @var int
     */
    protected $_current_fd;

    public function __construct(App $app) {
        $this->app = $app;
    }

    abstract public function start();
    abstract public function stop();
    abstract public function error();

}
