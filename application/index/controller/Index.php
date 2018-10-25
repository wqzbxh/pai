<?php
namespace app\index\controller;

use think\Controller;

class Index extends Controller
{
    public function login()
    {
        session('userInfo',null);
        return $this->fetch('login');
    }


    public function index()
    {
        $userModel = new \app\common\model\User();
        $result = $userModel->select()->toArray();
        var_dump($result);
    }
}
