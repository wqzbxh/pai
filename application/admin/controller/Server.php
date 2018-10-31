<?php
/**
 * Created by PhpStorm.
 * User: wanghaiyang
 * Date: 2018/10/10
 * Time: 10:38
 */
namespace app\admin\controller;

use app\common\model\Error;
use think\Cache;
use think\Controller;
use app\common\model\Serverdata;

Class Server extends Common{

    /**
     * 继承父类自动加载
     */
    public function _initialize()
    {
        parent::_initialize();
    }



    /**
     * @return mixed
     * 服务器管理首页
     */
    public function index()
    {
        return $this->fetch('index');
    }

    /**
     * 服务器管理添加页面
     */
    public function add()
    {
        return $this->fetch('add');
    }


    /**
     *服务器添加动作
     */
    public function addAction()
    {
        $data = array();
        if(!empty($_POST['data'])){
            if(isset($_POST['data']['ipiptunnel']) && $_POST['data']['ipiptunnel'] == 'on'){
                $data['ipiptunnel'] = 1;
                unset($_POST['data']['ipiptunnel']);
            }else{
                $data['ipiptunnel'] = 0;
            }
            if(isset($_POST['data']['hostcollect']) && $_POST['data']['hostcollect'] == 'on'){
                $data['hostcollect'] = 1;
                unset($_POST['data']['hostcollect']);
            }else{
                $data['hostcollect'] = 0;
            }
            $data =array_merge($_POST['data'],$data) ;
            $data['serveruserid'] = $this->userId;
            $data['createtime'] = time();
            $data['updatetime'] = time();
            $serverDataModel = new \app\common\model\Serverdata();
            $result = $serverDataModel->addServer($data);
        }else{
            $errorModel = new \app\common\model\Error();
            $result = array(
                'code' => 40001,
                'msg' => $errorModel::ERRORCODE[40001],
                'data' => array()
            );
        }
        return $result;
    }






    /**
     * 获取子规则列表
     * @param page 页码
     * @param limit 限制几个
     * @param serveruserid 用户id
     */

    public function getserver()
    {
        $serverDataModel = new \app\common\model\Serverdata();

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
        $serveruserid = $this->userId;
        $result = $serverDataModel->getServerList('',$offset,$limit,$this->userId);
        if($result['code'] == 0) {
            return $result;
        }
    }


    /**
     * 修改服务器页面渲染
     * @param id 产品的id int
     */
    public function edit()
    {
        $errorModel = new \app\common\model\Error();
        if(!empty($_GET['id'])){
            $serverDataModel = new \app\common\model\Serverdata();
            $result = $serverDataModel->getServerOne($_GET['id']);
            $this->assign('server',$result['data'][0]);
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
        $data = array();
        if(!empty($_POST['data'])){

            if(isset($_POST['data']['ipiptunnel']) && $_POST['data']['ipiptunnel'] == 'on'){
                $data['ipiptunnel'] = 1;
                unset($_POST['data']['ipiptunnel']);
            }else{
                $data['ipiptunnel'] = 0;
            }
            if(isset($_POST['data']['hostcollect']) && $_POST['data']['hostcollect'] == 'on'){
                $data['hostcollect'] = 1;
                unset($_POST['data']['hostcollect']);
            }else{
                $data['hostcollect'] = 0;
            }

            $data =array_merge($_POST['data'],$data);

            $serverDataModel = new \app\common\model\Serverdata();
            $result = $serverDataModel->updateServer($data);
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
            $serverDataModel = new \app\common\model\Serverdata();
            $result = $serverDataModel->delServerData($_POST['id']);
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
     * @return mixed
     * 推送数产品页面渲染
     */
    public  function product_push_the_number()
    {
        return $this->fetch('product_push_the_number');
    }

    /**
     * @return mixed
     * 推送数规则页面渲染
     */
    public  function rule_push_the_number()
    {
        return $this->fetch('rule_push_the_number');
    }


    /**
     * 对应生成XML文件
     */

    public function generateRuleXml()
    {
        $returnArray = array();
        if($_POST){
           $result =  Serverdata::RuleXml($_POST);
           if($result){
               return $result;
           }
        }else{
           $returnArray = array(
                'code' => 10005,
                'msg' => Error::ERRORCODE[10005],
                'data' => array()
            );
        }
    }


//    更新服务器状态
    public function updateServerStatus()
    {
        $serverDataModel = new Serverdata();
        $result = $serverDataModel->checkServerStatus();
        if($result){
            return $result;
        }
    }




    public function lookStatus()
    {
        $returnArray = [];
        $result = Cache::get('code'.$_POST['id']);
        if($result){
            Cache::rm('code'.$_POST['id']);
            if($result == 1){
                $returnArray = array(
                    'code' => 0,
                    'msg' => Error::ERRORCODE[0],
                    'data' => array()
                );
            }
        }
        return $returnArray;
    }
}


