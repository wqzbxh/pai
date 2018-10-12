<?php
/**
 * Created by PhpStorm.
 * User: wanghaiyang
 * Date: 2018/10/12
 * Time: 9:18
 */

namespace app\admin\controller;

use think\Controller;

Class Login extends Controller{

    /**
     * 登陆处理
     */
        public function loginAction()
        {
            $errorModel = new \app\common\model\Error();
            $returnArray = array();
            if(!empty($_POST['access']) && !empty($_POST['passwd'])){
                $userDataModel = new \app\common\model\Userdata();
                $result = $userDataModel->loginSin($_POST['access'],$_POST['passwd']);
                if($result['code'] == 0){
                    $userInfo = $result['data'][0];
                    unset($userInfo['passwd']);
                    session('userInfo',$userInfo);

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

            return $returnArray;
        }

        public function exitAction()
        {
            session('userInfo',null);
            return 0;
        }
}