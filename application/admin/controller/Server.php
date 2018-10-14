<?php
/**
 * Created by PhpStorm.
 * User: wanghaiyang
 * Date: 2018/10/10
 * Time: 10:38
 */
namespace app\admin\controller;

use think\Controller;

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
            $data = $_POST['data'];
            $data['createtime'] = time();
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
        $serveruserid = 0;
        $result = $serverDataModel->getServerList('',$offset,$limit,$serveruserid);
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
        if(!empty($_POST['data'])){
            $serverDataModel = new \app\common\model\Serverdata();
            $result = $serverDataModel->updateServer($_POST['data']);
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
}