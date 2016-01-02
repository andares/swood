<?php
namespace Swood;

/**
 * Description of Conf
 *
 * Conf类不内置version的处理逻辑, 这部分由外部调用负责.
 *
 * @author andares
 */
class Conf {
    protected $data      = [];
    private $base_dir;
    private $class;

    public function __construct($base_dir, $class = '\Swood\Conf\Yml') {
        $this->base_dir = $base_dir;
        $this->class    = $class;
    }

    /**
     *
     * @param type $space
     * @param type $path
     * @return Conf\Yml
     */
    public function get($space, $path) {
        if (!isset($this->data[$space][$path])) {
            $file   = $this->base_dir . DIRECTORY_SEPARATOR . $space . DIRECTORY_SEPARATOR . "$path";
            $class  = $this->class;
            $conf   = new $class($file);
            $this->data[$space][$path] = $conf;
        }

        return $this->data[$space][$path];
    }

}
