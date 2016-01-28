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

namespace Swood\Schema;

/**
 * Description of Loader
 *
 * @author andares
 */
trait Loader {
    /**
     *
     * @var array|string
     */
    protected static $_schema = '';

    /**
     *
     * @return array
     */
    public static function getSchema() {
        if (is_string(static::$_schema)) {
            if (!static::$_schema) {
                throw new \RuntimeException("schema of " . __CLASS__ . " is not defined");
            }
            static::$_schema = static::getSchemaConf()->get('schema', static::$_schema);
        }

        return static::$_schema;
    }

    /**
     *
     * @return \Swood\Conf
     */
    protected static function getSchemaConf() {
        return \Swood\Dock::select('instance')['conf'];
    }

}
