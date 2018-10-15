<?php
/**
 * Created by PhpStorm.
 * User: wanghaiyang
 * Date: 2018/10/11
 * Time: 11:45
 */
namespace app\admin\controller;

use think\Controller;

Class Serverchildrule extends  Common{

    /**
     * 继承父类自动加载
     */
    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 更改子规则服务器产品绑定状态
     *
     */
    public function changeStatus()
    {
        $returnArray = array();
        if(!empty($_POST['rule_id']) && !empty($_POST['serverid']) && !empty($_POST['product_id']) && !empty($_POST['child_rule_id'])){
            $serverChildRuleDataModel = new \app\common\model\Serverchildruledata();

//        如果没有status的值表明邦定表中没有这个规则和这个服务器产品之间进行邦定，因此进行邦定操作
            if(empty($_POST['status'])){
                $result = $serverChildRuleDataModel->addServerchildruleBindingRecord($_POST['child_rule_id'],$_POST['rule_id'],$_POST['serverid'],$_POST['product_id'],1);
            }else if($_POST['status'] == 1 && !empty($_POST['spid'])){
//                进行解绑操作
                $result = $serverChildRuleDataModel->delBindingRecord($_POST['spid']);
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

    /***更新绑定自规则的推文 url排除
     * @return array
     */
    public function updateServerChildRule()
    {
        $returnArray = array();
        $errorModel = new \app\common\model\Error();
        if($_POST['childrule_push_content']){
            $data['childrule_push_content'] = $_POST['childrule_push_content'];
        }else{
            $returnArray = array(
                'code' => 50013,
                'msg' => $errorModel::ERRORCODE[50013],
                'data' => array()
            );
        }
        if($_POST['childrule_exuri']){
            $data['childrule_exuri'] = $_POST['childrule_exuri'];
        }else{
            $returnArray = array(
                'code' => 50014,
                'msg' => $errorModel::ERRORCODE[50014],
                'data' => array()
            );
        }
        if($_POST['spid']){
            $id = $_POST['spid'];
        }else{
            $returnArray = array(
                'code' => 50015,
                'msg' => $errorModel::ERRORCODE[50015],
                'data' => array()
            );
        }

        if(empty($returnArray)){
            $serverChildRuleDataModel = new \app\common\model\Serverchildruledata();
            $result = $serverChildRuleDataModel->updateRocode($data,$id);
            if($result){
                $returnArray = $result;
            }
        }

        return $returnArray;
    }


    public function getServerchildOne()
    {
        if(!empty($_POST['spid'])){
            $serverChildRuleDataModel = new \app\common\model\Serverchildruledata();
            $result = $serverChildRuleDataModel->getServerchildOne($_POST['spid']);
            return $result;
        }
    }
}