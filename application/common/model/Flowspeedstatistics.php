<?php
/**
 * Created by PhpStorm.
 * User: wanghaiyang
 * Date: 2018/10/16
 * Time: 17:54
 */
namespace app\common\model;

use think\Model;

Class Flowspeedstatistics extends Model{

    const EveryHourFlowField = 'SUM(mbps),htime';
    /**计算当天的每小时的时间和流量
     * @param $dateTimeResult 当天凌晨的时间戳
     * @param $nowDateTimeResult 今天凌晨的时间戳
     * @param $serverid 服务器ID
     */

    public function getEveryHourFlow($dateTimeResult,$nowDateTimeResult,$serverid)
    {
        $returnArray = array();
        $errorModel = new \app\common\model\Error();
        if($dateTimeResult < $nowDateTimeResult){

            $endTime = $dateTimeResult + 86400; //计算当天的结束时间

        }else{
//             点击的是当天的数据流量
            $dateTimeResult = $nowDateTimeResult;
            $endTime = time() ;//计算当天的结束时间

        }
        $returnResult = self::field(self::EveryHourFlowField)
            ->where(array('serverid'=> $serverid))
            ->where('htime','egt',$dateTimeResult)
            ->where('htime','elt',$endTime)
            ->group('htime')
            ->select()
            ->toArray();
        if($returnResult){
            $returnArray = array(
                'code' => 0,
                'msg' => $errorModel::ERRORCODE[0],
                'data' => array()
            );
        }else{

        }
        var_dump($returnResult);
    }
}