<?php
/**
 * Created by PhpStorm.
 * User: wanghaiyang
 * Date: 2018/10/9
 * Time: 17:22
 */
namespace app\admin\controller;

use app\common\model\Operationlog;
use app\common\model\Productdata;
use app\common\model\Ruledata;
use think\Controller;
use think\Request;

class Childrule extends  Common{

    /**
     * 自动加载
     * 继承父类自动加载
     */
    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * @return mixed
     * 渲染子规则页面
     *
     */
    public function index()
    {
        if(!empty($_GET['id'])){

            $ruleDataModel = new \app\common\model\Ruledata();
            $result = $ruleDataModel->getRuleOne($_GET['id']);
            if($result['code'] == 0 ){
                $productid = $result['data'][0]['productid'];
                $this->assign('productid',$productid);
                $this->assign('ruleid',$_GET['id']);
                return $this->fetch('index');
            }
        }
    }

    /**
     * 渲染子规则添加页面
     * @param ruleid 继承父规则的id
     */
    public function add()
    {
        $prohibitpushModel = new \app\common\model\Prohibitpush();
        $pushcount = $prohibitpushModel->count();
        $this->assign('ruleid',$_GET['ruleid']);
        $this->assign('productid',$_GET['productid']);
        $this->assign('pushcount',$pushcount);
        $tactics = \app\common\model\Pushpolicy::getTactics('','seq,name',0,0);
        $this->assign('tactics',$tactics['data']);
        return $this->fetch('add');
    }

    /**
     *子规则添加
     */
    public function addAction()
    {
        $data = array();
        if(!empty($_POST['data'])){
            $data = $_POST['data'];
            $data['createtime'] = time();
            $childruleDataModel = new \app\common\model\Childruledata();
            $result = $childruleDataModel->addChildrule($data);
            $productDataModel = new Productdata();
            $productData = $productDataModel->getProductOne($_POST['data']['productid']);
            $ruledataDataModel = new Ruledata();
            $ruleData = $ruledataDataModel->getRuleOne($_POST['data']['ruleid']);
            Operationlog::addOperation($this->userId,Request::instance()->module(),Request::instance()->controller(),Request::instance()->action(),0,'[子规则管理]添加子规则:'.$_POST['data']['childrule_name'].'【'.$productData['data'][0]['product_name'].'|'.$ruleData['data'][0]['rule_name'].'】');
//
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
     * 获取子规则列表
     * @param page 页码
     * @param limit 限制几个
     * @param ruleid 父级id
     */

    public function getChildrule()
    {
        $childruleDataModel = new \app\common\model\Childruledata();

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
        if(isset($_GET["ruleid"])){
            $childruleid = $_GET["ruleid"];
        }

        if(isset($_GET["childrule_name"])){
            $childrule_name = $_GET["childrule_name"];
        }else{
            $childrule_name = '';
        }

        $result = $childruleDataModel->getChildruleRuleList($childrule_name,$offset,$limit,$childruleid);
        if($result) {
            return $result;
        }
    }


    /**
     * 修改产品操作
     * @param id 产品的id int
     */
    public function edit()
    {
        $errorModel = new \app\common\model\Error();
        if(!empty($_GET['id'])){
            $childruleDataModel = new \app\common\model\Childruledata();
            $result = $childruleDataModel->getChildruleOne($_GET['id']);
            $tactics = \app\common\model\Pushpolicy::getTactics('','seq,name',0,0);
            $this->assign('tactics',$tactics['data']);
            $this->assign('ruleid',$_GET['ruleid']);
            $this->assign('childrule',$result['data'][0]);
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
            $childruleDataModel = new \app\common\model\Childruledata();
            $result = $childruleDataModel->updateRule($_POST['data']);
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
     * @param id 规则的id int
     *
     */
    public function delAction()
    {
        $errorModel = new \app\common\model\Error();
        if(!empty($_POST['id'])){
            $childruleDataModel = new \app\common\model\Childruledata();
            $result = $childruleDataModel->delRule($_POST['id']);
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
        if(!empty($_GET['serverid']) && !empty($_GET['rule_id']) && !empty($_GET['product_id'])){
            $tactics = \app\common\model\Pushpolicy::getTactics('','seq,name',0,0);
            $this->assign('tactics',$tactics['data']);
            $this->assign('rule_id',$_GET['rule_id']);
            $this->assign('serverid',$_GET['serverid']);
            $this->assign('product_id',$_GET['product_id']);
            return $this->fetch('binding');
        }
    }

    public function getChildruleBindingList()
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

        if(!empty($_GET["rule_id"])){
            $rule_id = $_GET["rule_id"];
        }else{
            $returnArray = array(
                'code' => 50009,
                'msg' => $errorModel::ERRORCODE[50009],
                'data' => array()
            );
        }
        if(!empty($_GET["product_id"])){
            $product_id = $_GET["product_id"];
        }else{
            $returnArray = array(
                'code' => 50004,
                'msg' => $errorModel::ERRORCODE[50004],
                'data' => array()
            );
        }

        if(isset($_GET["childrule_name"])){
            $childrule_name = $_GET["childrule_name"];
        }else{
            $childrule_name = '';
        }

        if(isset($_GET["status"])){
            $status = $_GET["status"];
        }else{
            $status = 9;
        }

        if(empty($returnArray)){
            $childRuleDataModel = new \app\common\model\Childruledata();
            $result = $childRuleDataModel->getChildRuleBindingList($offset,$limit,$serverid,$rule_id,$product_id,$status,$childrule_name);

            if($result) {
                return $result;
            }
        }else{
            return $returnArray;
        }

    }

}
