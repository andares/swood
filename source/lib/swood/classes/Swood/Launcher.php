<?php
namespace Swood;
use Swood\Debug as D;

/**
 * 启动器
 *
 * @note test: swtry --debug call Poi/GetById 'bbb[]=22&id=111' -H 'token=wwwwww'
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
    private $work_dir;

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

        // 设置debug level
        Debug::setLevel($this->params->debug);

        // 取cmd
        $this->cmd = $this->params->getCmd();

        if (!$this->cmd) {
            throw new \BadMethodCallException(\Swood\Dock::select('swood')['dict']
                ->get('swood', 'message')['launcher_cli_example']);
        }

        // 取工作目录
        $this->work_dir = $this->params->getWorkDir();
        $autoload_path  = $this->work_dir . DIRECTORY_SEPARATOR . "classes";
        $conf_path      = $this->work_dir . DIRECTORY_SEPARATOR . "conf" .
            DIRECTORY_SEPARATOR. $this->params->conf;

        // init.php
        require "$this->work_dir/init.php";

        $result = $this->checkEnv([
            $autoload_path,
            $conf_path,
        ]);
        if (!$result) {
            throw new \RuntimeException('env check fail');
        }

        // autoload
        $autoload = \Swood\Dock::select('swood')['autoload'];
        $autoload->import($autoload_path);
        \Swood\Dock::select('app')['autoload'] = $autoload;

        // 载入配置
        $conf = new Conf($conf_path);
        \Swood\Dock::select('app')['conf'] = $conf;
        $this->conf = $conf->get('swood', 'swood');
    }

    /**
     * 基础环境检测
     *
     * @todo checkEnv完善
     */
    private function checkEnv($check_path) {
        if (!function_exists('\swoole_version')) {
            throw new \RuntimeException("Swood is depend swoole module");
        }

        if (PHP_VERSION_ID < 50600) {
            D::ec(" * Check PHP version ......... " . phpversion() . " >> Fail! (require 5.6)");
            return false;
        } else {
            D::ec(" * Check PHP version ......... " . phpversion() . " - OK.");
        }

        $version = explode('.', \swoole_version());
        $require = [
            1, 7, 21,
        ];
        foreach ($require as $key => $value) {
            if ($value[$key] > $version[$key]) {
                D::ec(" * Check Swoole version ...... " . \swoole_version() . " >> Fail! (require 1.7.6)");
                return false;
            }
        }
        D::ec(" * Check Swoole version ...... " . \swoole_version() . " - OK.");

        // 检查目录
        foreach ($check_path as $path) {
            if (!file_exists($path)) {
                throw new \RuntimeException("work dir [$this->work_dir] is incomplete");
            }
        }

        D::ec('');
        return true;
    }

    /**
     *
     * @return Server
     */
    private function createServer() {
        $swoole_conf = \Swood\Dock::select('app')['conf']->get('swood', 'swoole_conf');
        $server      = new Server($this->conf['launcher']['listen'],
            $this->conf['server'], $swoole_conf->toArray());
        \Swood\Dock::select('app')['server'] = $server;
        return $server;
    }

    /**
     *
     * @return Client
     */
    private function createClient() {
        $swoole_conf = \Swood\Dock::select('app')['conf']->get('swood', 'swoole_client');
        $client      = new Client($this->conf['client'], $swoole_conf->toArray());
        \Swood\Dock::select('app')['client'] = $client;
        return $client;
    }

    public function run() {
        return $this->{$this->cmd}();
    }

    public function start() {
        // 创建服务
        $server = $this->createServer();

        // 添加app
        $apps_conf    = \Swood\Dock::select('app')['conf']->get('swood', 'apps');
        foreach ($apps_conf as $app_name => $app_conf) {
            $server->setApp($app_name, $app_conf);
        }


        $server->bindCallback();

//        $app = new \Gis2\App([]);
//        D::du($app);
//        D::du($app->createWorker());
//        D::du($app->createTaskWorker());

        // TODO 暂时server start时没有回调，只能先在start前输出一下
        D::ec('server start..');

        $server->start();
    }

    public function stop() {
        $this->callLauncherApp(['stop']);
    }

    public function reload() {
        $this->callLauncherApp(['reload']);
    }

    public function status() {
        $this->callLauncherApp(['status']);
    }

    public function hold() {
        $this->callLauncherApp(['hold']);
    }

    private function callLauncherApp($action) {
        $listen = \Swood\Dock::select('app')['conf']
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
    }

    public function call() {
        // 创建app
        $app_name   = $this->params->app;
        $apps_conf  = \Swood\Dock::select('app')['conf']->get('swood', 'apps');
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
            $error    = $response->hasError();
            if ($error) {
                D::ec(">> fail: " . json_encode($error));
            } else {
                $header = $response->getHeader();
                D::ec(">> response header: " . json_encode($header->toArray()));
                D::ec(">> response result:");
                D::ec(json_encode($response->getAllResult()));
            }
        } else {
            D::ec(">> fail");
        }
    }

    public function exec() {

    }

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

    /**
     * 临时使用的测试方法
     */
    public function play() {
    }
}
