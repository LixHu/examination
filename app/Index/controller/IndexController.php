<?php

namespace app\Index\controller;

use lib\controller\Controller;
use lib\model\Model as model;

class IndexController extends Controller
{
    public function index() {
        $user = new model('user');
        $data = $user->find();
        var_dump($data);
    }
}
