<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * @author andares
 */

// 自动载入
require "/home/worker/local/lib/swood/classes/Swood/Autoload.php";
$autoload = new \Swood\Autoload();
$autoload->import("/home/worker/local/lib/swood/classes/");
$autoload->register();
\Swood\Dock::select('app')['autoload'] = $autoload;

\Swood\Debug::setLevel(2);
