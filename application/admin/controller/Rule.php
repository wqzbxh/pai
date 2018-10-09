<?php
namespace app\admin\controller;

use think\Controller;


class Rule extends Controller
{
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
        $childruleDataModel = new \app\common\model\Ruledata();
        if(isset($_GET["productid"])){
            $productid = $_GET["productid"];
        }else{
            return false;
        }
        if(isset($_GET["page"])){
            $offset = ($_GET["page"] -1) * 15;
        }else{
            $offset = 0;
        }
        if(isset($_GET["limit"])){
            $limit = $_GET["limit"];
        }else{
            $limit = 15;
        }

        $result = $childruleDataModel->getRuleList('',$offset,$limit,$productid);
        if($result['code'] == 0) {
            return $result;
        }
    }


    /**
     * 渲染策略模式主题下产品添加页面
     */
    public function add()
    {
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
            $childruleDataModel = new \app\common\model\Ruledata();
            $result = $childruleDataModel->addRule($data);
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
            $childruleDataModel = new \app\common\model\Ruledata();
            $result = $childruleDataModel->getRuleOne($_GET['id']);
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
            $childruleDataModel = new \app\common\model\Ruledata();
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
     * @param id 产品的id int
     *
     */
    public function delAction()
    {
        $errorModel = new \app\common\model\Error();
        if(!empty($_POST['id'])){
            $childruleDataModel = new \app\common\model\Ruledata();
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
}
