<?php
/**
 * Created by PhpStorm.
 * User: wanghaiyang
 * Date: 2018/10/12
 * Time: 9:18
 */

namespace app\admin\controller;

use app\common\model\Operationlog;
use app\common\model\Userpermission;
use think\captcha\Captcha;
use think\Controller;
use think\Request;

Class Login extends Controller{

    /**
     * @return \think\Response验证码
     */

    public function verify()
    {
        $captcha = new Captcha();
        return $captcha->entry();
    }

    /**
     * 登陆处理
     */
        public function loginAction()
        {
            $errorModel = new \app\common\model\Error();
            $returnArray = array();
            $captcha = new Captcha();

            if(!empty($_POST['code']) && $captcha->check($_POST['code']))
            {
                if(!empty($_POST['access']) && !empty($_POST['passwd'])){
                    $userDataModel = new \app\common\model\Userdata();
                    $result = $userDataModel->loginSin($_POST['access'],$_POST['passwd']);
                    if($result['code'] == 0){
                        $userInfo = $result['data'][0];
                        $userInfo['permission'] = Userpermission::getList(array('user_id'=>$userInfo['id']));
                        unset($userInfo['passwd']);
                        session('userInfo',$userInfo);
                        Operationlog::addOperation($userInfo['id'],Request::instance()->module(),Request::instance()->controller(),Request::instance()->action(),7,'执行登陆');
                        $returnArray = array(
                            'code' => 0,
                            'msg' => $errorModel::ERRORCODE[0],
                            'data' => array(),
                        );
                    }else{
                        $returnArray = array(
                            'code' => 10003,
                            'msg' => $errorModel::ERRORCODE[10003],
                            'data' => array()
                        );
                    }
                }else{
                    $returnArray = array(
                        'code' => 10004,
                        'msg' => $errorModel::ERRORCODE[10004],
                        'data' => array(),
                    );
                }
            }else{
                $returnArray = array(
                    'code' => 10006,
                    'msg' => $errorModel::ERRORCODE[10006],
                    'data' => array(),
                );
            }


            return $returnArray;
        }

        public function exitAction()
        {
            Operationlog::addOperation(session('userInfo')['id'],Request::instance()->module(),Request::instance()->controller(),Request::instance()->action(),7,'执行登出');
            session('userInfo',null);
            return 0;
        }
}