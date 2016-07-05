<?php
namespace Swood;

/**
 * Description of Autoload
 *
 * @author andares
 */
class Autoload {
    public $import_path = [];

    protected $loaded_classes = [];

    public function __construct() {
        $this->import_path = array_reverse(explode(':', ini_get('include_path')));
    }

    public function import($path) {
        if (strpos($path, 'phar://') !== 0) {
            $path = realpath($path);
        }
        if (in_array($path, $this->import_path)) {
            return true;
        }

        $this->import_path[] = $path;
        ini_set('include_path', implode(':', array_reverse($this->import_path)));

        return true;
    }

    public function register() {
        spl_autoload_register([$this, 'called']);
    }

    public function getLoadedClasses() {
        return $this->loaded_classes;
    }

    public function called($classname) {
        $classname  = \str_replace("\\", DIRECTORY_SEPARATOR, $classname);

        // TODO 这里加入try，只为处理phpunit中愚蠢的、莫名奇妙地对composer autload类的载入
        try {
            include "$classname.php";
        } catch (\Exception $exc) {
            return false;
        }

        $this->loaded_classes[] = $classname;
        if (class_exists($classname) || interface_exists($classname) ||
            trait_exists($classname)) {
            return true;
        }
        return false;
    }
}
