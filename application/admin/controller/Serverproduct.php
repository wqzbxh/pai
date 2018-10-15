<?php
/**
 * Created by PhpStorm.
 * User: wanghaiyang
 * Date: 2018/10/10
 * Time: 17:06
 */
namespace app\admin\controller;

use think\Controller;

Class Serverproduct extends  Common{

    /**
     * 继承父类自动加载
     */
    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 更改状态
     *
     */
    public function changeStatus()
    {
        $returnArray = array();
//        如果没有status的值表明邦定表中没有这个产品和这个服务器进行邦定，因此进行邦定操作
        if(!empty($_POST['productid']) && !empty($_POST['serverid'])){
            $serverproductModel = new \app\common\model\Serverproductdata();
            if(empty($_POST['status'])){
                $result = $serverproductModel->addServerproductBindingRecord($_POST['productid'],$_POST['serverid'],1);
            }else if($_POST['status'] == 1 && !empty($_POST['spid'])){
                $data = array();
                $data['id']  = $_POST['spid'];
                $result = $serverproductModel->delBindingRecord($data);
            }
        }else{
            $errorModel = new \app\common\model\Error();
            $returnArray = array(
                'code' => 50002,
                'msg' => $errorModel::ERRORCODE[50002],
                'data' => array()
            );
        }
        return $result;
    }
}