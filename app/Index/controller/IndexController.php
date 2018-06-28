<?php

namespace app\Index\controller;

use lib\controller\Controller;
use lib\model\Model;
class IndexController extends Controller
{
    public function index() {
        new Model('test');
    }
}
