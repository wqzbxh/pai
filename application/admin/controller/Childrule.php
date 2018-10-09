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
}
