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

namespace Helper;

/**
 * Description of Uuid
 *
 * @author andares
 */
class Uuid {
    public static function gen($prefix = '')
    {
        if (function_exists('uuid_make')) {
            uuid_create($v1);
            uuid_make($v1, UUID_MAKE_V1);
            uuid_export($v1, UUID_FMT_STR, $uuid);
        } else {
            $uuid = uuid_create();
        }
        return $prefix ? ($prefix . $uuid) : $uuid;
    }

}
