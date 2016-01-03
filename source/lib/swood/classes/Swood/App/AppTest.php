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

/**
 * AppTest用于在单元测试中构建一个低耦合性的App对象，
 * 供Action等对象的构造器使用。
 *
 * @author andares
 */
abstract class AppTest extends App {
    public function __construct($conf = []) {
        parent::__construct($conf);
    }

    public function buildRequest($data = []) {
        return new \stdClass();
    }

    public function buildResponse($data = []) {
        return new \stdClass();
    }

    /**
     *
     * @return Worker
     */
    public function createWorker() {
        return new \stdClass();
    }

    public function createTaskWorker() {
        return new \stdClass();
    }

    public function start() {
        D::ec(">> server start");
    }

    public function shutdown() {
        D::ec(">> server stop");
    }
}
