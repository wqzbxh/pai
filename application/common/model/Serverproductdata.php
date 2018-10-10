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

        $result = self::insert($data);
        $errorModel = new \app\common\model\Error();
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
        return $returnArray;
    }

    /****
     * 接触服务器与产品绑定
     * 思路直接删除记录即可
     */

    public function delBindingRecord($id)
    {
        $returnArray = array();
        $errorModel = new \app\common\model\Error();
        if(empty($id) == false){
            $result = self::where('id',$id)->delete();
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
}