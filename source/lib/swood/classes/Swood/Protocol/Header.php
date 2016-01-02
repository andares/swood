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

namespace Swood\Protocol;

/**
 * Description of Header
 *
 * @author andares
 */
class Header implements \ArrayAccess {
    use \Swood\Schema\Mapping;

//    protected static $_schema = [
//        'timestamp' => 0,
//        'timeinfo'  => 1,       // microtime和时区
//        'dev'       => 2,       // 设备号, 设备类型，客户端版本号等相关数据
//        'lang'      => 3,       // 本地化数据
//        'token'     => 4,       // 认证token
//        'verify'    => 5,       // 加密信息列表
//        'error'     => 6,       // 错误信息
//    ];

    public function __construct($data = []) {
        $data && $this->_data = $data;
    }
}
