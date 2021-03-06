<?php
/**
 * Created by PhpStorm.
 * User: k
 * Date: 2018/10/15
 * Time: 16:54
 */
namespace app\admin\controller;

use think\Controller;

Class Whitelist extends Common{
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
    }

    /**
     * @return Request
     */
    public function index()
    {
        $serverDataModel = new \app\common\model\Serverdata();
//        获取服务器列表
        $result = $serverDataModel->getServerList('',0,1000,0);
        if($result['code'] == 0){
            $this->assign('serverList',$result['data']);
            $this->assign('serverDefault',$result['data'][0]['id']);
            return $this->fetch('index');
        }

    }


    /**
     * @return array获取绑定列表
     *
     */
    public function getWhitelist()
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

        if(isset($_GET["content"])){
            $content = $_GET["content"];
        }else{
            $content = '';
        }

        $ipWhiteListDataModel = new \app\common\model\Ipwhitelist();
        $result = $ipWhiteListDataModel->getList($offset,$limit,$serverid,$content);
//        var_dump($result);
        return $result;
    }

    /**渲染添加页面
     * @return mixed
     */
    public function add()
    {
        if(!empty($_GET['serverid'])){
            $this->assign('serverid',$_GET['serverid']);
            return $this->fetch('add');
        }

    }

    /**添加动作action
     * @return array
     * @param data 数组
     */
    public function addAction()
    {
        if(!empty($_POST['data']) && is_array($_POST['data'])){
            $ipWhiteListDataModel = new \app\common\model\Ipwhitelist();
            $result = $ipWhiteListDataModel->addAction($_POST['data']);
            if($result){
                return $result;
            }
        }
    }


    public function edit()
    {
        $returnArray = array();
        $errorModel = new \app\common\model\Error();
        if(!empty($_GET['serverid']) &&!empty($_GET['id'])  ){
            $ipWhiteListDataModel = new \app\common\model\Ipwhitelist();
            $result = $ipWhiteListDataModel->getListONe(array('id' => $_GET['id']));
            if($result['code'] == 0){
                $this->assign('whiteListDetails',$result['data'][0]);
                return $this->fetch('edit');
            }else{

            }

        }else{
            $returnArray = array(
                'code' => 10005,
                'msg' => $errorModel::ERRORCODE[10005],
                'data' => array()
            );

            return $returnArray;
        }

    }

    /**
     * @return array修改action
     * data要修改的数组
     */
    public function editAction()
    {
        $returnArray = array();
        $errorModel = new \app\common\model\Error();
        if(!empty($_POST['data']) && is_array($_POST['data']) && !empty($_POST['data']['id'])){
            $ipWhiteListDataModel = new \app\common\model\Ipwhitelist();
            $where = array();
            $where['id'] = $_POST['data']['id'];
            $result = $ipWhiteListDataModel->upDateRecode($where,$_POST['data']);
            if($result){
                return $result;
            }
        }else{
            $returnArray = array(
                'code' => 10005,
                'msg' => $errorModel::ERRORCODE[10005],
                'data' => array()
            );
            return $returnArray;
        }
    }

    /**
     * 删除操作
     */
    public function delAction()
    {
        $returnArray = array();
        $errorModel = new \app\common\model\Error();
        if(!empty($_POST['id'])){
            $ipWhiteListDataModel = new \app\common\model\Ipwhitelist();
            $result = $ipWhiteListDataModel->delRecode(array('id'=>$_POST['id']));
            if($result){
                return $result;
            }
        }else{
            $returnArray = array(
                'code' => 10005,
                'msg' => $errorModel::ERRORCODE[10005],
                'data' => array()
            );

            return $returnArray;
        }
    }
}