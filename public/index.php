<?php
// 如果php版本低于7 报错
if (PHP_VERSION < '7.0.0') {
    throw new Exception('版本过低');
    die();
}
define('ROOT_PATH','/public');
define('APP_PATH', 'app');
require '../lib/app.php';