<?php
/**
 * Created by PhpStorm.
 * User: k
 * Date: 2018/10/12
 * Time: 11:51
 */
namespace  app\admin\controller;

use think\Controller;

/**
 * Class Common
 * @package app\common\controller
 * 验证登陆 权限等等
 */
Class Common extends Controller{
    /**
     * 开始验证
     */
    public function _initialize()
    {
      if(empty(session('userInfo')) || request()->action() == 'login'){
          session('userInfo',null);
          $this->redirect('index/index/login');
      }else{
//          验证权限
          $this->userId = session('userInfo')['userflag'];
      }
    }
}