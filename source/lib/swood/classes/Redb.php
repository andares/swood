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

/**
 * Description of Redb
"/home/worker/local/bin/php" "-d" "phar.readonly=0" "/home/worker/local/bin/phpunit-skelgen" "--ansi" "generate-test" "--bootstrap=/home/andares/repos/swood/tests/bootstrap.php" "Redb" "/home/andares/repos/swood/source/lib/swood/classes/Redb.php" "RedbTest" "/home/andares/repos/swood/tests/source/lib/swood/classes/RedbTest.php"
 * @author andares
 */
class Redb {
    public static function init($is_test = false, callback $shutdown_register = null) {
        if ($shutdown_register) {
            $shutdown_register([self, 'closeAllConnections']);
        }
    }

    public static function query() {

    }

    public static function releaseAllEnities() {

    }

    public static function closeAllConnections() {

    }
}
