<?php
namespace app\admin\controller;

use think\Controller;

class Index extends Login
{

    public function _initialize()
    {
        if(session('userInfo')){

        }else{
            $this->redirect('index/index/login');
        }
    }

    public function index()
    {
        return $this->fetch('index');
    }
}
