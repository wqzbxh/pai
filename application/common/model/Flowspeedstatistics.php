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

    protected $connection = 'db_config_cards3';

    const EveryHourFlowField = 'MAX(mbps) as mbps_all,htime';
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
            ->where('htime','LT',$endTime)
            ->group('htime')
            ->select()
            ->toArray();
        if($returnResult){
            $returnArray = array(
                'code' => 0,
                'msg' => $errorModel::ERRORCODE[0],
                'data' => $returnResult
            );
        }else{
              $returnArray = array(
                  'code' => 70001,
                  'msg' => $errorModel::ERRORCODE[70001],
                  'data' => array()
              );
        }

        return $returnArray;
    }

    /**
     * @param $where 查询条件
     */
    public function getEveryHourFlowChild($where)
    {
        $returnArray = array();
        $errorModel = new \app\common\model\Error();
        if(is_array($where)){
            $result = self::where($where)
                ->select()
                ->toArray();
            if($result){
                $returnArray = array(
                    'code' => 0,
                    'msg' => $errorModel::ERRORCODE[0],
                    'data' => $result
                );
            }else{
                $returnArray = array(
                    'code' => 70001,
                    'msg' => $errorModel::ERRORCODE[70001],
                    'data' => array()
                );
            }
        }else{
            $returnArray = array(
                'code' => 10002,
                'msg' => $errorModel::ERRORCODE[10002],
                'data' => array()
            );
        }

        return $returnArray;
    }
}