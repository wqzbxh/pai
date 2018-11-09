<?php
/**
 * Created by PhpStorm.
 * User: wanghaiyang
 * Date: 2018/10/11
 * Time: 11:45
 */
namespace app\admin\controller;

use app\common\model\Childruledata;
use app\common\model\Error;
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
//                如果不存在记录则为添加，存在则改变状态
                $data = [
                    'child_rule_id'=> $_POST['child_rule_id'],
                    'rule_id'=> $_POST['rule_id'],
                    'serverid'=> $_POST['serverid'],
                    'product_id'=> $_POST['product_id'],
                ];

                $checkCode = $serverChildRuleDataModel::getOne($data);
                if($checkCode['code'] == 0){//说明已经存在，则改变状态
                    $result = $serverChildRuleDataModel->updateRocode(array('status'=>1),$checkCode['data']['id']);
                }else{
                    $result = $serverChildRuleDataModel->addServerchildruleBindingRecord($_POST['child_rule_id'],$_POST['rule_id'],$_POST['serverid'],$_POST['product_id'],1);
                }
            }else if($_POST['status'] == 1 && !empty($_POST['spid'])){//进行解绑操作
//
                $result = $serverChildRuleDataModel->updateRocode(array('status'=>0),$_POST['spid']);
            }else if($_POST['status'] == 2 && !empty($_POST['spid'])){//进行删除操作
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
            $data['childrule_push_content'] = '';
        }
        if($_POST['childrule_exuri']){
            $data['childrule_exuri'] = $_POST['childrule_exuri'];
        }else{
            $data['childrule_exuri'] = '';
        }

        if($_POST['binding_childrule_host']){
            $data['binding_childrule_host'] = $_POST['binding_childrule_host'];
        }else{
            $data['binding_childrule_host'] = '';
        }
        if($_POST['binding_childrule_ratio']){
            $data['binding_childrule_ratio'] = $_POST['binding_childrule_ratio'];
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
//        进行绑定操作 如果没有查到该记录的绑定则添加一条 然后把添加的数据返回
//                   如果有数据则直接返回


        if(!empty($_POST['child_rule_id'])&&!empty($_POST['id'])){
            $data = [
                'child_rule_id'=> $_POST['child_rule_id'],
                'rule_id'=> $_POST['rule_id'],
                'serverid'=> $_POST['serverid'],
                'product_id'=> $_POST['product_id'],
            ];

            $serverChildRuleDataModel = new \app\common\model\Serverchildruledata();
            $chidruleDataModel = new Childruledata();
            $result = $serverChildRuleDataModel::getOne($data);
            if($result['code'] == 0){//说明尊在则直接返回数据
                $resultData =  $result['data'];
            }else{//不存在则创建一条，然后把数据返回
                $resultNew = $serverChildRuleDataModel->addServerchildruleBindingRecord($_POST['child_rule_id'],$_POST['rule_id'],$_POST['serverid'],$_POST['product_id'],0);
                if($resultNew['code'] == 0){
                    $resultNewData = $serverChildRuleDataModel::getOne($data);
                    $resultData = $resultNewData['data'];
                }

            }

            $chidruleResult = $chidruleDataModel->getChildruleOne($_POST['id']);
            $returnArray = [
                'code' => 0,
                'msg' => Error::ERRORCODE[0],
                'data' => [
                    'chidruleResult' => $chidruleResult['data'][0],
                    'chidruleRelations' => $resultData
                ],
            ];

            return $returnArray;
        }
    }
}