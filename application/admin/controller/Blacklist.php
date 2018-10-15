<?php
/**
 * Created by PhpStorm.
 * User: wanghaiyang
 * Date: 2018/10/15
 * Time: 14:23
 */
namespace app\admin\controller;

use think\Controller;

Class Blacklist extends Common{
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
    public function getBlacklist()
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


        $ipBlackListDataModel = new \app\common\model\Ipblacklist();
        $result = $ipBlackListDataModel->getList($offset,$limit,$serverid);
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
            $ipBlackListModel = new \app\common\model\Ipblacklist();
            $result = $ipBlackListModel->addAction($_POST['data']);
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
            $ipBlackListModel = new \app\common\model\Ipblacklist();
            $result = $ipBlackListModel->getListONe(array('id' => $_GET['id']));
            if($result['code'] == 0){
                $this->assign('blacListDetails',$result['data'][0]);
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
            $ipBlackListModel = new \app\common\model\Ipblacklist();
            $where = array();
            $where['id'] = $_POST['data']['id'];
            $result = $ipBlackListModel->upDateRecode($where,$_POST['data']);
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
            $ipBlackListModel = new \app\common\model\Ipblacklist();
            $result = $ipBlackListModel->delRecode(array('id'=>$_POST['id']));
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