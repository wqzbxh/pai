<?php
/**
 * Created by PhpStorm.
 * User: wanghaiyang
 * Date: 2018/10/16
 * Time: 16:30
 */
namespace app\admin\controller;

use think\Controller;

Class Flowspeedstatistics extends Common{



    /**
     * @return mixed
     *峰值流速页面渲染
     */
    public function preciseVmax()
    {
        if(!empty($_GET['id']) && !empty($_GET['time'])){
            $serverid = $_GET['id'];
            $time = $_GET['time'];
            $this->assign('serverid',$serverid);
            $this->assign('time',$time);
            return $this->fetch('precise_vmax');
        }

    }


    /**
     * @return mixed
     *峰值流速页面渲染
     * 返回当天整点时间，及其流量
     */
    public function vmax()
    {
        if(!empty($_GET['id']) && !empty($_GET['time'])){
            $serverid = $_GET['id'];
            $time = $_GET['time'];

            $this->assign('serverid',$serverid);
            $this->assign('time',$time);
            return $this->fetch('vmax');
        }
    }

    public function getFlowspeedstatistics()
    {

        if(!empty($_GET['id']) && !empty($_GET['time'])){
            $serverid = $_GET['id'];
            $time = $_GET['time'];
            // 获取当天的零点的时间戳
            $commonController = new \app\common\controller\Common();
            $flowspeedstatisticsModel = new \app\common\model\Flowspeedstatistics();
            $dateTimeResult = $commonController->zeroTimestamp($time);
            $nowDateTimeResult = $commonController->zeroTimestamp(time());
            $result = $flowspeedstatisticsModel->getEveryHourFlow($dateTimeResult,$nowDateTimeResult,$serverid);
            return $result;
        }

    }


    public function getFlowspeedstatisticsChild()
    {

        if(!empty($_GET['id']) && !empty($_GET['time'])){
            $where = array();
            $where['serverid'] = $_GET['id'];
            $where['htime'] = $_GET['time'];
            $flowspeedstatisticsModel = new \app\common\model\Flowspeedstatistics();
            $result = $flowspeedstatisticsModel->getEveryHourFlowChild($where);
            return $result;
        }
    }
}