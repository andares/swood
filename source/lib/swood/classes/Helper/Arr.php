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
 * Description of Array
 *
 * @author andares
 */
class Arr {
    public static function traversalByDepth(array &$arr,
        $depth_total = 1, $depth_current = 0) {

        $depth_current++;
        foreach ($arr as $key => $unit) {
            if ($depth_current < $depth_total) {
                if (!is_array($unit)) {
                    yield [&$arr, $key] => null;
                    continue;
                }

                $it = self::traversalByDepth($arr[$key], $depth_total, $depth_current);
                foreach ($it as $cursor => $unit) {
                    yield $cursor => $unit;
                }
            } else {
                yield [&$arr, $key] => $unit;
            }
        }
    }

    public static function traversal(array &$arr) {
        foreach ($arr as $key => $unit) {
            if (is_array($unit)) {
                $it = self::traversal($arr[$key]);
                foreach ($it as $cursor => $unit) {
                    yield $cursor => $unit;
                }
            } else {
                yield [&$arr, $key] => $unit;
            }
        }
    }
}
