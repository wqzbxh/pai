<?php
namespace app\admin\controller;

use think\Controller;


class Product extends  Common{

    /**
     * 继承父类自动加载
     */
    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * @return mixed
     * 渲染策略模式主题下产品管理首页
     */
    public function index()
    {
        return $this->fetch('index');
    }

    /**
     * 获取产品列表
     * @param page 页码
     * @param limit 限制条数
     */
    public function getProduct()
    {
        $productDataModel = new \app\common\model\Productdata();

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

        if(isset($_GET["product_name"])){
            $product_name = $_GET["product_name"];
        }else{
            $product_name = '';
        }

        $result = $productDataModel->getProductList(9,9,$product_name,0,$offset,$limit);
        if($result['code'] == 0) {
            return $result;
        }
    }


    /**
     * 渲染策略模式主题下产品添加页面
     */
    public function add()
    {
        return $this->fetch('add');
    }


    /**
     * 添加产品动作
     * @param product_name 产品名称
     * @param product_type 产品类型：0为通匹，1为基本，默认值为1
     * @param match_type 通匹类型：0为APK，1为EXE，默认值为0
     */
    public function addAction()
    {
        $data = array();
        $data['product_name'] = isset($_POST['product_name']) ? $_POST['product_name'] : '';
        $data['product_type'] = isset($_POST['product_type']) ? $_POST['product_type'] : 1;
        $data['match_type'] = isset($_POST['match_type']) ? $_POST['match_type'] : 0;
        $data['createtime'] = time();
        if(!empty($data['product_name'])){
            $productDataModel = new \app\common\model\Productdata();
            $result = $productDataModel->addProduct($data);
        }else{
            $errorModel = new \app\common\model\Error();
            $result = array(
                'code' => 20002,
                'msg' => $errorModel::ERRORCODE[20002],
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
            $productDataModel = new \app\common\model\Productdata();
            $result = $productDataModel->getProductOne($_GET['id']);
            $this->assign('product',$result['data'][0]);
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
            $productDataModel = new \app\common\model\Productdata();
            $result = $productDataModel->updateProduct($_POST['data']);
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
            $productDataModel = new \app\common\model\Productdata();
            $result = $productDataModel->delProduct($_POST['id']);
            if($result['code'] == 0){
                //删除产品下的规则
                $ruleDataModel = new \app\common\model\Ruledata();
                $ruleResultData = $ruleDataModel->conditionDel(array('productid' => $_POST['id']));

                if($ruleResultData['code'] == 0 || $ruleResultData['code'] == 20011 ){
                    $SreverProductdataModel = new \app\common\model\Serverproductdata();
                    $serverRuletModel = new \app\common\model\Serverruledata();
                    $serverChildruletModel = new \app\common\model\Serverchildruledata();
                    $SreverProductdataModel->delBindingRecord(array('product_idd' => $_POST['id']));

                    $serverRuletModel->unbundle(array('product_id' => $_POST['id']));

                    $serverChildruletModel->delListRule(0,$_POST['id'],0);

                    $result = array(
                        'code' => 0,
                        'msg' => $errorModel::ERRORCODE[0],
                        'data' => array()
                    );
                }
            }
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
     * 产品邦定页面
     **/
    public function binding()
    {

        $serverDataModel = new \app\common\model\Serverdata();
        $productDataModel = new \app\common\model\Productdata();
//        获取服务器列表
        $result = $serverDataModel->getServerList('',0,1000,$this->userId);
//        获取产品列表（分清与服务器绑定状态）getProductBindingList
        if($result['code'] == 0){
            $this->assign('serverList',$result['data']);
            $this->assign('serverDefault',$result['data'][0]['id']);
            return $this->fetch('binding');
        }
    }

    /**
     * @return array获取绑定列表
     *
     */
    public function getProductBindingList()
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
        if(isset($_GET["serverid"])){
            $serverid = $_GET["serverid"];
        }else{
            $serverid = 0;
        }
        if(isset($_GET["status"])){
            $status = $_GET["status"];
        }else{
            $status = 9;
        }

        if(isset($_GET["product_name"])){
            $product_name = $_GET["product_name"];
        }else{
            $product_name = '';
        }


        $productDataModel = new \app\common\model\Productdata();
        $result = $productDataModel->getProductBindingList(9,9,$product_name,0,$offset,$limit,$serverid,$status);
//        var_dump($result);
        return $result;
    }

}
