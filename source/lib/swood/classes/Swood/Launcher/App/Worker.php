<?php

/*
 * license less
 */

namespace Swood\Launcher\App;
use Swood\Debug as D;

/**
 * Description of Worker
 *
 * @author andares
 */
class Worker extends \Swood\App\Worker {
    public function __construct(...$parent_args) {
        parent::__construct(...$parent_args);
    }

    public function call(Protocol\Request $request, Protocol\Response $response) {
        foreach ($request->getActions() as $action_id => $action_call) {
            $result = new Result;
            switch ($action_call[0]) {
                case 'stop':
                    $result->msg = "swood server will shutdown..";

                    $swoole = $this->app->server->swoole;
                    $swoole->after(1500, function() use ($swoole) {
                        $swoole->shutdown();
                    });
                    break;
                case 'reload':
                    $result->msg = "swood server will reload..";

                    $swoole = $this->app->server->swoole;
                    $swoole->after(1500, function() use ($swoole) {
                        $swoole->reload();
                    });
                    break;
                case 'status':
                    $result->msg = "status ok";
                    break;
                case 'hold':
                    $result->msg = "hold ok";
                    break;
                default:
                    throw new \BadMethodCallException("launcher command error $action_call[0]");
                    break;
            }
            $response->setResult($action_id, $result);
        }
    }

    public function taskFinish($task_id, Protocol\Response $response) {

    }

    public function start() {
        D::ec(">> worker start");
    }

    public function stop() {
        D::ec(">> worker stop");

    }

    public function error() {
        D::ec(">> worker error");

    }
}
