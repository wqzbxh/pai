<?php
/**
 * Created by PhpStorm.
 * User: wanghaiyang
 * Date: 2018/10/10
 * Time: 16:14
 */
namespace app\common\model;

use think\Model;

Class Serverproductdata extends Model{


     /*
     * 添加绑定记录到数据库
     */
    public function addServerproductBindingRecord($productid,$serverid,$status)
    {
        $data = array();
        $returnArray = array();
        $data['product_id'] = $productid;
        $data['serverid'] = $serverid;
        $data['status'] = $status;
        $data['createtime'] = time();
        $checkStatusResult = self::checkStatus($serverid,$productid,$status);
        $errorModel = new \app\common\model\Error();
        if($checkStatusResult > 0){
            $returnArray = array(
                'code' => 50017,
                'msg' => $errorModel::ERRORCODE[50017],
                'data' => array()
            );
        }else{
            $result = self::insert($data);
            if($result == 1){
                $returnArray = array(
                    'code' => 0,
                    'msg' => $errorModel::ERRORCODE[0],
                    'data' => $result
                );

            }else{
                $returnArray = array(
                    'code' => 50001,
                    'msg' => $errorModel::ERRORCODE[50001],
                    'data' => $result
                );
            }
        }

        return $returnArray;
    }

    /****
     * 接触服务器与产品绑定
     * 思路直接删除记录即可
     */

    public function delBindingRecord($data)
    {

        $returnArray = array();
        $errorModel = new \app\common\model\Error();
        if(!empty($data)){
            $result = self::where($data)->delete();
            if($result == 1){
                $returnArray = array(
                    'code' => 0,
                    'msg' => $errorModel::ERRORCODE[0],
                    'data' => $result
                );

            }else{
                $returnArray = array(
                    'code' => 50001,
                    'msg' => $errorModel::ERRORCODE[50001],
                    'data' => $result
                );
            }
        }else{
            $returnArray = array(
                'code' => 50003,
                'msg' => $errorModel::ERRORCODE[50003],
                'data' => array()
            );
        }
        return $returnArray;
    }

    /**
     * 检验是否有添加绑定的数据
     * @param $serverid 服务器id
     * @param $product_id 产品的id
     * @param $status 绑定的状态
     * 返回查到的条数int型
     */
    public function checkStatus($serverid,$product_id,$status)
    {
        $where = array();
        $where['serverid'] = $serverid;
        $where['product_id'] = $product_id;
        $where['status'] = 1;
        $result = self::where($where)->count();
        return $result;
    }


}