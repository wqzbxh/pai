<?php
/**
 * Created by PhpStorm.
 * User: k
 * Date: 2018/10/12
 * Time: 11:51
 */
namespace  app\admin\controller;

use app\common\model\Error;
use app\common\model\Operationlog;
use app\common\model\Permission;
use think\Controller;
use think\Request;

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
//          验证权限\
          if(session('userInfo')['id'] != 1){
              $Permission = [];
              $Permission['module'] = Request::instance()->module();
              $Permission['controller']  = Request::instance()->controller();
              $Permission['method']  = Request::instance()->action();
              $PermissionResult = Permission::getOne($Permission);
              if($PermissionResult['code'] == 0){
                  $userpermission = session('userInfo')['permission'];
                  $actionPermission[0] = $PermissionResult['data']['id'];
                  $result = array_intersect($userpermission,$actionPermission);
                  if(empty($result)){//交集没有说明用户没有权限
                      Operationlog::addOperation(session('userInfo')['id'],$Permission['module'], $Permission['controller'],$Permission['method'] ,7,Error::ERRORCODE[17002]);
                      $resultArray = [
                          'code' => 17002,
                          'msg' => Error::ERRORCODE[17002],
                          'data' => []
                      ];

                      json($resultArray)->send();
                      exit;
                  }
              }
          }
          $this->userId = session('userInfo')['id'];
      }
    }
}

//登陆的时候获取用户权限集合 ，获取全部权限的集合 存到session，
//如何判断权限：操作后查询操作id，有没有在全部权限集合里面；
//                      如果没在则直接跳过，无权限问题
//                      如果在查看一下用户是否拥有该权限，（根据ID与用户权限集合session存的权限取交集、存在则正常跳过，不存在说明无权限）
//
