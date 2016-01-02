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

namespace Swood\Launcher\App;
use Swood\Debug as D;

/**
 * Description of App
 *
 * @author andares
 */
class App extends \Swood\App\App {
    public function buildRequest($data = []) {
        return new Protocol\Request($data);
    }

    public function buildResponse($data = []) {
        return new Protocol\Response($data);
    }

    /**
     *
     * @return Worker
     */
    public function createWorker() {
        $worker = new Worker($this);
        return $worker;
    }

    public function createTaskWorker() {
        return null;
    }

    public function start() {
        D::ec(">> server start");
    }

    public function shutdown() {
        D::ec(">> server stop");
    }
}
