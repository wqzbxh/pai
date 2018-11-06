<?php
/**
 * Created by PhpStorm.
 * User: wanghaiyang
 * Date: 2018/10/16
 * Explain: 服务器统计表模型
 * Time: 14:21
 */
namespace app\common\model;

use think\Model;

Class Serverstatistics extends Model{

    protected $connection = 'db_config_cards3';
    /**
     * @param $where array 查询条件
     *
     */
    public function getList($where,$offset,$limit,$startTime,$endTime)
    {
        $errorModel = new \app\common\model\Error();
        $returnArray = array();

        if(is_array($where)){
            if($startTime != 0 && $endTime != 0){
                $endTime = strtotime($endTime);
                $startTime = strtotime($startTime);
                $result = self::where($where)
                    ->where('time','<',$endTime)
                    ->where('time','>',$startTime)
                    ->order('id asc')
                    ->select()
                    ->toArray();
            }else{
                $result = self::where($where)->limit($offset,$limit)->order('id desc')->select()->toArray();
            }
            $count =  self::where($where)->select()->count();
            if(!empty($result)){
                $returnArray = array(
                    'code' => 0,
                    'msg' => $errorModel::ERRORCODE[0],
                    'count' => $count,
                    'of' => $offset,
                    'imit' => $limit,
                    'data' => $result
                );
            }else{
                $returnArray = array(
                    'code' => 70001,
                    'msg' => $errorModel::ERRORCODE[70001],
                    'data' =>array()
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