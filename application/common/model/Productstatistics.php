<?php
/**
 * Created by PhpStorm.
 * User: wanghaiyang
 * Date: 2018/10/17
 * Time: 9:37
 */
//产品统计模型
namespace app\common\model;

use think\Db;
use think\Model;

Class Productstatistics extends Model{

    protected $connection = 'db_config_cards3';




    const RETURNFeild = 'p.id,p.product_name,pss.producthitcount,pss.time';

    /**
     * @param $startTime 开始时间
     * @param $where 查询条件
     */
    public function getProductstatiscs($startTime,$where,$offset,$limit)
    {
        $returnArray = array();
        $errorModel = new \app\common\model\Error();
        $endTime = $startTime + 86400;
        ///select * from database1.table1 as t1 inner join database2.table2 as t2 where t1,id=t2.id

        $result = self::alias('pss')
            ->join('pai.productdata p','pss.productid = p.id','LEFT')
            ->where($where)
            ->where('pss.time','gt',$startTime)
            ->where('pss.time','elt',$endTime)
            ->limit($offset,$limit)
            ->field(self::RETURNFeild)
            ->order('pss.id DESC')
            ->select()
            ->toArray();

        $count = self::alias('pss')
            ->join('pai.productdata p','pss.productid = p.id','LEFT')
            ->where($where)
            ->where('pss.time','gt',$startTime)
            ->where('pss.time','elt',$endTime)
            ->field(self::RETURNFeild)
            ->order('pss.id DESC')
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