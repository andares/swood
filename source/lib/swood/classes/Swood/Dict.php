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

namespace Swood;

/**
 * Description of Dict
 *
 * @author andares
 */
class Dict extends Conf {

    private $lang = 'zh_CN.utf8';

    public function __construct($lang, $base_dir, $class = '\Swood\Conf\Yml') {
        $this->lang = $lang;
        $base_dir .= DIRECTORY_SEPARATOR . $lang;

        parent::__construct($base_dir, $class);
    }

    public function __invoke($key, array $values = []) {
        if ($values) {
            return $this->assign($key, $values);
        } else {
            return $this[$key];
        }
    }

    public function assign($key, $values) {
        return sprintf($key, $values);
    }

}
