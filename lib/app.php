<?php
    // 拿到自动加载文件，加载文件
    require __DIR__ . "/Loader.php";
    // 初始化目录
    Loader::init(__DIR__ . '/..');
    // 可以自定义的方法，如果没接收到，默认index
    if($_GET) {
        if ($_GET['c']) {
            $controller = $_GET['c'];
        }
        if ($_GET['m']) {
            $model = $_GET['m'];
        }
        if ($_GET['a']) {
            $method = $_GET['a'];
        }
    //    $action = new \Index\controller\IndexController.class();
    }else {
        $controller = 'Index';
        $model = 'Index';
        $method = 'Index';
    }
    // 调用某个类下面的方法。
    $cont = '\\'.APP_PATH.'\\'.$model."\\"."controller" . "\\" . $controller.'Controller';
    $action = new $cont();
    $action->$method();

?>
