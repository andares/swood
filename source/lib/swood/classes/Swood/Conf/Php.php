<?php
namespace Swood\Conf;

/**
 * Description of Php
 *
 * @author andares
 */
class Php extends Yml {
    protected static $suffix = 'php';

    protected function loadContent($file) {
        return require "$file." . static::$suffix;
    }

    protected function loadSchema($file) {
        $file .= ".sch." . static::$suffix;
        if (!file_exists($file)) {
            return false;
        }

        $this->schema = require $file;
    }
}

