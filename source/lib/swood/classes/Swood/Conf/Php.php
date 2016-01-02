<?php
namespace Swood\Conf;

/**
 * Description of Php
 *
 * @author andares
 */
class Php extends Yml {

    protected function loadContent($file) {
        return require "$file.php";
    }

    protected function loadSchema($file) {
        $file .= ".sch.php";
        if (!file_exists($file)) {
            return false;
        }

        $this->schema = require $file;
    }
}

