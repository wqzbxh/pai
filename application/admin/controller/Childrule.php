<?php
/**
 * Created by PhpStorm.
 * User: wanghaiyang
 * Date: 2018/10/9
 * Time: 17:22
 */
namespace app\admin\controller;

use think\Controller;

class Childrule extends Controller
{
    /**
     * @return mixed
     * 渲染子规则页面
     *
     */
    public function index()
    {
        $this->assign('ruleid',$_GET['id']);
        return $this->fetch('index');
    }

    /**
     * 渲染子规则添加页面
     * @param ruleid 继承父规则的id
     */
    public function add()
    {
        $this->assign('ruleid',$_GET['ruleid']);
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
        if(isset($_GET["ruleid"])){
            $childruleid = $_GET["ruleid"];
        }
        $result = $childruleDataModel->getChildruleRuleList('',$offset,$limit,$childruleid);
        if($result['code'] == 0) {
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

}
