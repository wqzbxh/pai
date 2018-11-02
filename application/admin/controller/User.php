<?php
/**
 * Created by PhpStorm.
 * User: wanghaiyang
 * Date: 2018/11/1
 * Time: 16:01
 */
namespace app\admin\controller;

use app\common\model\Error;
use app\common\model\Userdata;

use think\Controller;

/**
 *
 * Class User
 * @package app\admin\controller
 * @explain 用户控制器
 */
Class User extends Common{

    /**
     *
     */
    public function add()
    {
        return  $this->fetch('user/add');
    }

    /**
     * @explain添加用户方法
     * @explain接收
     */
    public function addAction()
    {
        $returnArray = [];
        $data = [];
        if(!empty($_POST['data'])){
           $acceptData =  $_POST['data'];
        }else{
            $returnArray = array(
                'code' => 10005,
                'msg' => Error::ERRORCODE[10005],
                'data' => array()
            );
        }
        if($acceptData['username']){
            $data['username'] = $acceptData['username'];
            $result = Userdata::getOne($data);
            if($result['code'] == 0){
                $returnArray = array(
                    'code' => 13008,
                    'msg' => Error::ERRORCODE[13008],
                    'data' => array()
                );
            }
        }else{
            $returnArray = array(
                'code' => 13006,
                'msg' => Error::ERRORCODE[13006],
                'data' => array()
            );
        }

        if($acceptData['passwd']){
            $data['passwd'] = $acceptData['passwd'];
        }else{
            $returnArray = array(
                'code' => 13005,
                'msg' => Error::ERRORCODE[13005],
                'data' => array()
            );
        }
        $data['userflag'] = $acceptData['userflag'];
        $data['createtime'] = time();
        if(empty($returnArray)){
                $addUserResult = Userdata::addUserAction($data);
                if($addUserResult){
                    $returnArray = $addUserResult;
                }
        }

        return $returnArray;
    }

    public function edit()
    {

        if(!empty($_GET['id'])){
            $result = Userdata::getOne(array('id'=>$_GET['id']));
            if($result['code'] == 0){
                $this->assign('userInfo',$result['data']);
                return $this->view->fetch('user/edit');
            }else{
               echo " <script>window.history.back();location.reload();</script>";
            }
        }else{
            echo "<script>window.history.back();location.reload();</script>";
        }
    }

    /**
     * @explain修改用户动作
     * @explain接收
     */


    public function editAction()
    {
        $returnArray = [];
        if(!empty($_POST['data']) && is_array($_POST['data']) && !empty($_POST['data']['id'])){

            $where = array();
            $where['id'] = $_POST['data']['id'];
            $editResult = Userdata::update($_POST['data'],$where)->toArray();
            if($editResult){
                $returnArray = $returnArray = [
                    'code' => 0,
                    'msg' => Error::ERRORCODE[0],
                    'data' => $editResult
                ];
            }else{
                $returnArray = array(
                    'code' => 13002,
                    'msg' => Error::ERRORCODE[13002],
                    'data' => []
                );
            }
        }else{
            $returnArray = array(
                'code' => 10005,
                'msg' => Error::ERRORCODE[10005],
                'data' => []
            );
        }
        return $returnArray;
    }

    /**
     * @return array
     */
    public function delAction()
    {
        $returnArray = [];
        if(!empty($_POST['id'])){
            $delResult = Userdata::destroy(['id'=> $_POST['id']]);
            if($delResult){
                $returnArray = array(
                    'code' => 0,
                    'msg' => Error::ERRORCODE[0],
                    'data' => $delResult
                );
            }else{
                $returnArray = array(
                    'code' => 13003,
                    'msg' => Error::ERRORCODE[13003],
                    'data' => []
                );
            }
        }else{
            $returnArray = array(
                'code' => 10005,
                'msg' => Error::ERRORCODE[10005],
                'data' => []
            );
        }
        return $returnArray;
    }

    /**
     * @return array获取账号列表
     */
    public function getList()
    {
        if(isset($_GET["limit"])){
            $limit = $_GET["limit"];
        }else{
            $limit = 15;
        }
        if(isset($_GET["page"])){
            $offset = ($_GET["page"] -1) * $limit;
        }else{
            $offset = 0;
        }
        $result =  Userdata::getManyList($offset,$limit);
//        var_dump($result);
        return $result;
    }

    /**
     * @return int
     * 验证账号是否存在
     */
    public function verifyName()
    {
        if($_POST['username']){
            $where['username'] = $_POST['username'];
            $result = Userdata::getOne($where);
            if($result['code'] == 0){
                return 1;
            }
        }
    }
}

//        13001 => '查不到该用信息',
//        13002 => '修改用户信息无效',
//        13003 => '删除用户失败',
//        13004 => '用户ID不能为空',
//        13005 => '用户密码不能为空',
//        13006 => '用户账号不能为空',