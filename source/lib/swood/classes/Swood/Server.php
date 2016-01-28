<?php

namespace Swood;
use Swood\Debug as D;

/**
 * Description of Server
 *
 * @author andares
 *
 * @property \swoole_server $swoole swoole server
 */
class Server {
    use Runtime;

    const PROCESSTYPE_MASTER        = 1;
    const PROCESSTYPE_MANAGER       = 2;
    const PROCESSTYPE_WORKER        = 3;
    const PROCESSTYPE_TASKWORKER    = 4;

    public $current_fd;

    /**
     *
     * 当前该进程类型
     *
     * @var int
     */
    private $process_type;

    /**
     *
     * @var \Swood\App\App[]
     */
    private $apps;

    /**
     *
     * @var array
     */
    private $apps_conf;

    /**
     *
     * @var array
     */
    private $conf;

    /**
     *
     * @var Handler\Server
     */
    private $handler;

    public function __construct(array $listen, array $conf, array $swoole_conf = []) {
        $this->conf = $conf;

        $this->createSwooleServer($listen, $swoole_conf);
        $this->createHandler($listen);

        // 创建launcher监听
        $app_name = 'Swood\Launcher\App';
        $this->apps_conf[$app_name] = ['listen' => [$listen]];
        $this->handler->setPortMapping($listen['port'], $app_name);
    }

    public function __call($method, $args = []) {
        $this->swoole->$method(...$args);
    }

    /**
     * 取连接信息
     * @return type
     */
    public function getConnectionInfo() {
        $info = $this->swoole->connection_info($this->current_fd);
        return $info;
    }

    public function setProcessType($process_type) {
        $this->process_type = $process_type;
    }

    public function getProcessType() {
        return $this->process_type;
    }

    private function createSwooleServer(array $listen, array $swoole_conf) {
        $this->swoole = new \swoole_server($listen['host'], $listen['port'],
            \SWOOLE_PROCESS, constant($listen['type']));
        $swoole_conf && $this->swoole->set($swoole_conf);
    }

    private function createHandler(array $listen) {
        $this->handler  = new Handler\Server($this);
    }

    public function setApp($app_name, $app_conf) {
        $this->apps_conf[$app_name] = $app_conf;
        foreach ($app_conf['listen'] as $listen_conf) {
            $this->addListener($app_name, $listen_conf);
        }
    }

    /**
     *
     * @param string $app_name
     * @return App\App
     * @throws \UnexpectedValueException
     */
    public function getApp($app_name) {
        if (!isset($this->apps_conf[$app_name])) {
            throw new \UnexpectedValueException("App $app_name is not registered");
        }

        if (!isset($this->apps[$app_name])) {
            $class_name = "\\$app_name\App";
            $this->apps[$app_name] = new $class_name();
            $this->apps[$app_name]->setServer($this);
        }

        return $this->apps[$app_name];
    }

    public function getAllApps() {
        foreach ($this->apps_conf as $app_name => $app_conf) {
            $app = $this->getApp($app_name);
            yield $app;
        }
    }

    public function addListener($app_name, $listen) {
        $this->swoole->addListener($listen['host'], $listen['port'],
            constant($listen['type']));
        $this->handler->setPortMapping($listen['port'], $app_name);
    }

    public function bindCallback() {
        // 绑定server
        $this->handler->bindCallback();

        // 如果为debug模式会把不支持的也绑上
        if (Debug::level()) {
            $this->handler->bindDebugCallback();
        }
    }

}
