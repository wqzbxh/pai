<?php
namespace app\admin\controller;

use think\Controller;

class Index extends  Common{

    /**
     * 继承父类自动加载
     */
    public function _initialize()
    {
        parent::_initialize();
    }

    public function index()
    {
        return $this->fetch('index');
    }

    public function test()
    {
    var_dump(APP_PATH);
    }
}
