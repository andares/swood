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
    public $default_scene = 'default';

    protected $data      = [];
    private $base_dir;
    private $class;
    private $scene;

    public function __construct($base_dir, $scene, $class = '\Swood\Conf\Yml') {
        $this->base_dir = $base_dir;
        $this->scene    = $scene;
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
            $class  = $this->class;
            $conf   = new $class($this->base_dir, $this->scene,
                $space . DIRECTORY_SEPARATOR . $path, $this->default_scene);
            $this->data[$space][$path] = $conf;
        }

        return $this->data[$space][$path];
    }

}
