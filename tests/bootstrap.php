<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * @author andares
 */

// 声明测试状态
const IN_TEST = 1;

// 测试时所用的配置场景名
$conf_scene = 'default';

// 自动载入
require "/home/worker/local/lib/swood/classes/Swood/Autoload.php";
$autoload = new \Swood\Autoload();
$autoload->import("/home/worker/local/lib/swood/classes/");
$autoload->register();
\Swood\Dock::select('swood')['autoload'] = $autoload;

\Swood\Debug::setLevel(2);

\Swood\App\Init::init(__DIR__ . '/../sample', IN_TEST);

