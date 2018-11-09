<?php
/**
 * Created by PhpStorm.
 * User: wanghaiyang
 * Date: 2018/10/11
 * Time: 10:36
 */
namespace app\admin\controller;

use think\Controller;

class Serverruledata extends  Common{

    /**
     * 继承父类自动加载
     */
    public function _initialize()
    {
        parent::_initialize();
    }


    /**
     * 更改规则服务器产品绑定状态
     *status1 绑定， 0 解绑 ，2删除
     */
    public function changeStatus()
    {
        $returnArray = array();
        if(!empty($_POST['rule_id']) && !empty($_POST['serverid']) && !empty($_POST['product_id'])){
            $serverruletModel = new \app\common\model\Serverruledata();
//        如果没有status的值表明邦定表中没有这个规则和这个服务器产品之间进行邦定，因此进行邦定操作

            $data = [
                'rule_id'=> $_POST['rule_id'],
                'serverid'=> $_POST['serverid'],
                'product_id'=> $_POST['product_id'],
            ];

            if(empty($_POST['status'])){
                $checkCode = $serverruletModel::getOne($data);
                if($checkCode['code'] == 0){//说明已经存在，则改变状态
                    $data['status'] = 1;
                    $result = $serverruletModel->updateRocode($data,$checkCode['data']['id']);
                }else{
                    $result = $serverruletModel->addServerruleBindingRecord($_POST['rule_id'],$_POST['serverid'],$_POST['product_id'],1);
                }
            }else if($_POST['status'] == 1 && !empty($_POST['spid'])){//                进行解绑操作
                $data['status'] = 0;
                $result = $serverruletModel->updateRocode($data,$_POST['spid']);
            }else if($_POST['status'] == 2 && !empty($_POST['spid'])){
//                进行删除操作
                $result = $serverruletModel->delBindingRecord($_POST['spid'],$_POST['serverid'],$_POST['product_id'],$_POST['rule_id']);
            }
        }else{
            $errorModel = new \app\common\model\Error();
            $returnArray = array(
                'code' => 50002,
                'msg' => $errorModel::ERRORCODE[50002],
                'data' => array()
            );
        }
        return $result;
    }


}