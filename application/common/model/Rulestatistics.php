<?php
/**
 * Created by PhpStorm.
 * User: wanghaiyang
 * Date: 2018/10/17
 * Time: 9:37
 */
//产品统计模型
namespace app\common\model;

use think\Model;

Class Rulestatistics extends Model{

    protected $connection = 'db_config_cards3';

    const RETURNFeild = 'r.rule_name,rss.rulehitcount,rss.time,rss.id';

    /**
     * @param $startTime 开始时间
     * @param $where 查询条件
     */
    public function getRulestatiscs($startTime,$where,$offset,$limit)
    {
        $returnArray = array();
        $errorModel = new \app\common\model\Error();
        $endTime = $startTime + 86400;
        $result = self::alias('rss')
            ->join('pai.ruledata r','rss.topruleid = r.id','LEFT')
            ->where($where)
            ->where('rss.time','gt',$startTime)
            ->where('rss.time','elt',$endTime)
            ->limit($offset,$limit)
            ->field(self::RETURNFeild)
            ->order('rss.id DESC')
            ->select()
            ->toArray();
        $count = self::alias('rss')
            ->join('pai.ruledata r','rss.productid = r.productid','LEFT')
            ->where($where)
            ->where('rss.time','gt',$startTime)
            ->where('rss.time','elt',$endTime)
            ->field(self::RETURNFeild)
            ->count();

        if(!empty($result))
        {
            $returnArray = array(
                'code' => 0,
                'msg' => $errorModel::ERRORCODE[0],
                'count' => $count,
                'data' => $result
            );
        }else{
            $returnArray = array(
                'code' => 80002,
                'msg' => $errorModel::ERRORCODE[80002],
                'data' => $result
            );
        }
        return $returnArray;
    }

}