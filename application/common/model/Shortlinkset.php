<?php
/**
 * Created by PhpStorm.
 * User: k
 * Date: 2018/11/7
 * Time: 18:26
 */
namespace app\common\model;

use think\Model;

/**
 * Class Shortlinkset
 * @package app\common\model
 * 链接检查模型
 */
Class Shortlinkset extends Model{

    /**
     * @param string $link 链接地址
     * @param $offset 从第几条开始
     * @param $limit 限制几条
     * @return array 返回数据集合
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getShortLink($link = '',$offset,$limit)
    {
        $criteria = array();
        $returnArray = array();
        $errorModel = new \app\common\model\Error();
        if(!empty($product_name)){
            $result = self::where($criteria)->where('link','like','%'.$link.'%')->limit($offset,$limit)->select()->toArray();
            $count = self::where($criteria)->where('link','like','%'.$link.'%')->count();
        }else{
            $result = self::where($criteria)->limit($offset,$limit)->select()->toArray();
            $count = self::where($criteria)->count();
        }

        if(!empty($result)){
            $returnArray = array(
                'code' => 0,
                'msg' => $errorModel::ERRORCODE[0],
                'count' =>$count,
                'data' => $result
            );
        }else{
            $returnArray = array(
                'code' => 10001,
                'msg' => $errorModel::ERRORCODE[10001],
                'data' => $result
            );
        }
        return $returnArray;
    }
}
