<?php
namespace Swood;
use Swood\Debug as D;

/**
 * 启动器
 *
 * @note test: swtry call Poi/GetById 'bbb[]=22&id=111' -H 'token=wwwwww'
 * @note test: swood call aaa 'bbb[]=22' -H 'token=wwwwww' -C ~/repos/117go/gis2
 * @note test: swtry call aaa 'bbb[]=22' ccc '{"ddd":100}' -H 'token=wwwwww'
 *
 * @author andares
 */
class Launcher {
    /**
     *
     * @var Launcher\Params
     */
    private $params;

    /**
     *
     * @var string
     */
    private $cmd;

    /**
     *
     * @var Conf\Yml
     */
    private $conf;

    public function __construct($argv) {
        $this->params = new Launcher\Params($argv);
    }

    public function prepare() {
        // 关闭某些错误
        error_reporting(E_ALL ^ E_STRICT);

        if ($this->params->help) {
            $this->cmd = 'help';
            return true;
        }

        // 取cmd
        $this->cmd = $this->params->getCmd();

        if (!$this->cmd) {
            throw new \BadMethodCallException(\Swood\Dock::select('swood')['dict']
                ->get('swood', 'message')['launcher_cli_example']);
        }

        // 取工作目录
        $workdir = new WorkDir($this->params->work_dir);
        $workdir->init($this->params->conf, $this->params->getDebugLevel());
        \Swood\Dock::select('swood')['workdir'] = $workdir;

        // 载入swood配置
        $this->conf = \Swood\Dock::select('instance')['conf']->get('swood', 'swood');
    }

    /**
     *
     * @return Server
     */
    private function createServer() {
        $swoole_conf = \Swood\Dock::select('instance')['conf']->get('swood', 'swoole_conf');
        $server      = new Server($this->conf['launcher']['listen'],
            $this->conf['server'], $swoole_conf->toArray());
        \Swood\Dock::select('instance')['server'] = $server;
        return $server;
    }

    /**
     *
     * @return Client
     */
    private function createClient() {
        $swoole_conf = \Swood\Dock::select('instance')['conf']->get('swood', 'swoole_client');
        $client      = new Client($this->conf['client'], $swoole_conf->toArray());
        \Swood\Dock::select('instance')['client'] = $client;
        return $client;
    }

    /**
     * 执行指令
     * @return type
     */
    public function run() {
        return $this->{$this->cmd}();
    }

    /**
     * 启动swood
     */
    public function start() {
        // 创建服务
        $server = $this->createServer();

        // 添加app
        $apps_conf    = \Swood\Dock::select('instance')['conf']->get('swood', 'apps');
        foreach ($apps_conf as $app_name => $app_conf) {
            $server->setApp($app_name, $app_conf);
        }


        $server->bindCallback();
        // TODO server start时没有回调，只能先在start前输出一下
        D::ec('server start..');
        $server->start();
    }

    /**
     * 停止swood实例
     */
    public function stop() {
        $this->callLauncherApp(['stop']);
    }

    /**
     * 平滑重启（刷新文件）
     */
    public function reload() {
        $this->callLauncherApp(['reload']);
    }

    /**
     * 查看状态
     */
    public function status() {
        $this->callLauncherApp(['status']);
    }

    /**
     * 进入维护模式
     */
    public function hold() {
        $this->callLauncherApp(['hold']);
    }

    /**
     *
     * @param string $action
     * @return boolean
     */
    private function callLauncherApp($action) {
        $listen = \Swood\Dock::select('instance')['conf']
            ->get('swood', 'swood')['launcher']['listen'];
        $app = new Launcher\App\App([]);

        // 取Request
        $request = $app->buildRequest();
        $request->appendAction(...$action);

        // 发送
        $client = $this->createClient();
        $result = $client->call("$request", $listen['host'], $listen['port']);
        if (!$result) {
            D::ec(">> fail");
            return false;
        }
        $response = $app->buildResponse($result);
        if (isset($response->getAllResult()[0]['msg'])) {
            D::ec(">> ". $response->getAllResult()[0]['msg']);
        } else {
            D::ec(">> response is not valid!");
        }
        return true;
    }

    /**
     *
     * @throws \DomainException
     * @throws \RuntimeException
     */
    public function call() {
        // 创建app
        $app_name   = $this->params->app;
        $apps_conf  = \Swood\Dock::select('instance')['conf']->get('swood', 'apps');
        if ($app_name) {
            // 确认有没有
            if (!isset($apps_conf[$app_name])) {
                throw new \DomainException("app [$app_name] is not registered");
            }
        } else {
            // 默认取第一个
            $app_name = array_shift(array_keys($apps_conf->toArray()));
        }

        $class_name = "\\$app_name\App";
        $app = new $class_name($apps_conf[$app_name]);
        /* @var $app App\App */

        // 拿到端口配置
        $listen = $app->getListenConf($this->params->port);
        if (!$listen) {
            throw new \RuntimeException("port id is not valid");
        }

        // 取Request
        $actions = $this->params->getCallActions();
        $request = $app->buildRequest();
        $request->setActions($actions);

        // 在request中处理header
        $header = $request->getHeader();
        foreach ($this->params->getHeader() as $key => $value) {
            $header[$key] = $value;
        }

        // 调数据
        $client = $this->createClient();
        $result = $client->call("$request", $listen['host'], $listen['port']);
        if ($result) {
            $response = $app->buildResponse($result);
            $this->echoResponse($response);
        } else {
            D::ec(">> fail");
        }
    }

    /**
     *
     * @param \Swood\Protocol\Response $response
     */
    private function echoResponse(Protocol\Response $response) {
        $error    = $response->hasError();
        if ($error) {
            D::ec(">> fail: " . json_encode($error));
        } else {
            $header = $response->getHeader();
            D::ec(">> response header: " . json_encode($header->toArray()));
            D::ec(">> response result:");
            D::ec(json_encode($response->getAllResult()));
            D::ec(">> response channel:");
            foreach ($response->getChannelList() as $channel_name) {
                $data = $response->getChannel($channel_name);
                D::ec("--- $channel_name");
                D::ec("    " . json_encode($data));
            }
        }

    }

    /**
     * 执行单个action
     */
    public function exec() {

    }

    /**
     * 显示帮助
     */
    public function help() {
        $dict = \Swood\Dock::select('swood')['dict']->get('swood', 'message');

        switch ($this->params->getCmd()) {
            case 'call':
                D::ec($dict['launcher_cli_call_example']);
                D::ec($dict['launcher_cli_options']);
                D::ec($dict['launcher_cli_call_options']);
                break;
            case 'start':
            default:
                D::ec($dict['launcher_cli_example']);
                D::ec($dict['launcher_cli_command']);
                D::ec($dict['launcher_cli_options']);
                break;
        }
    }

}
