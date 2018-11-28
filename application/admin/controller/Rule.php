<?php
namespace app\admin\controller;

use app\common\model\Productdata;
use think\Controller;


class Rule extends  Common{

    /**
     * 继承父类自动加载
     */
    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * @return mixed
     * 渲染规则页面
     */
    public function index()
    {
        $this->assign('productid',$_GET['id']);
        return $this->fetch('index');
    }

    /**
     * 获取产品列表
     * @param page 页码
     * @param limit 限制条数
     */
    public function getRule()
    {
        $ruleDataDataModel = new \app\common\model\Ruledata();
        if(isset($_GET["productid"])){
            $productid = $_GET["productid"];
        }else{
            return false;
        }

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

        if(isset($_GET["rule_name"])){
            $rule_name = $_GET["rule_name"];
        }else{
            $rule_name = '';
        }


        $result = $ruleDataDataModel->getRuleList($rule_name,$offset,$limit,$productid);
        if($result) {
            return $result;
        }
    }


    /**
     * 渲染策略模式主题下产品添加页面
     */
    public function add()
    {
        if(!empty($_GET['productid'])){
            $produrtModel = new Productdata();
            $result = $produrtModel->getProductOne($_GET['productid']);
            if($result['code'] == 0){
                $this->assign('product_type',$result['data'][0]['product_type']);
            }
        }
        $this->assign('productid',$_GET['productid']);
        return $this->fetch('add');
    }


    /**
     * 添加产品动作
     * @param $_POST['data'] 添加的参数 array
     */
    public function addAction()
    {
        $data = array();
        if(!empty($_POST['data'])){
            $data = $_POST['data'];
            $data['createtime'] = time();
            $ruleDataDataModel = new \app\common\model\Ruledata();
            $result = $ruleDataDataModel->addRule($data);
        }else{
            $errorModel = new \app\common\model\Error();
            $result = array(
                'code' => 30001,
                'msg' => $errorModel::ERRORCODE[30001],
                'data' => array()
            );
        }
        return $result;
    }



    /**
     * 修改产品操作
     * @param id 产品的id int
     */
    public function edit()
    {
        $errorModel = new \app\common\model\Error();
        if(!empty($_GET['id'])){
            $ruleDataDataModel = new \app\common\model\Ruledata();
            $result = $ruleDataDataModel->getRuleOne($_GET['id']);
            if(!empty($result['data'][0]['productid'])){
                $produrtModel = new Productdata();
                $resultPro = $produrtModel->getProductOne($result['data'][0]['productid']);
                if($result['code'] == 0){
                    $this->assign('product_type',$resultPro['data'][0]['product_type']);
                }
            }
            $this->assign('rule',$result['data'][0]);
            return $this->fetch('edit');
        }else{
            $result = array(
                'code' => 20003,
                'msg' => $errorModel::ERRORCODE[20003],
                'data' => array()
            );
        }
    }

    /**
     * 修改操作
     * @param data 修改的数据集合 array
     */
    public function editAction()
    {
        $errorModel = new \app\common\model\Error();
        if(!empty($_POST['data'])){
            if(isset($_POST['data']['product_type'])){
                unset($_POST['data']['product_type']);
            }
            $ruleDataDataModel = new \app\common\model\Ruledata();
            $result = $ruleDataDataModel->updateRule($_POST['data']);
        }else{
            $result = array(
                'code' => 20005,
                'msg' => $errorModel::ERRORCODE[20005],
                'data' => array()
            );
        }
        return $result;
    }


    /**
     * 删除操作
     * @param id 产品的id int
     *
     */
    public function delAction()
    {
        $errorModel = new \app\common\model\Error();
        if(!empty($_POST['id'])){
            $ruleDataDataModel = new \app\common\model\Ruledata();
            $result = $ruleDataDataModel->delRule($_POST['id']);
        }else{
            $result = array(
                'code' => 20005,
                'msg' => $errorModel::ERRORCODE[20005],
                'data' => array()
            );
        }
        return $result;
    }


    /**
     * 邦定服务器产品规则页面
     * @param serverid 服务器id
     * @id 产品的ID
     **/
    public function binding()
    {
        if(!empty($_GET['serverid']) && !empty($_GET['id'])){
            $this->assign('id',$_GET['id']);
            $this->assign('serverid',$_GET['serverid']);
            return $this->fetch('binding');
        }
    }

    /**
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */

    public function getRuleBindingList()
    {

        $errorModel = new \app\common\model\Error();
        $returnArray = array();
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
        if(isset($_GET["serverid"])){
            $serverid = $_GET["serverid"];
        }else{
            $returnArray = array(
                'code' => 50005,
                'msg' => $errorModel::ERRORCODE[50005],
                'data' => array()
            );
        }

        if(!empty($_GET["id"])){
            $id = $_GET["id"];
        }else{
            $returnArray = array(
                'code' => 50004,
                'msg' => $errorModel::ERRORCODE[50004],
                'data' => array()
            );
        }

        if(isset($_GET["status"])){
            $status = $_GET["status"];
        }else{
            $status = 9;
        }

        if(isset($_GET["rule_name"])){
            $rule_name = $_GET["rule_name"];
        }else{
            $rule_name = '';
        }




        if(empty($returnArray)){
            $ruleDataModel = new \app\common\model\Ruledata();
            $result = $ruleDataModel->getRuleBindingList($offset,$limit,$serverid,$id,$status,$rule_name);
            if($result) {
                return $result;
            }
        }else{
            return $returnArray;
        }
    }


}
