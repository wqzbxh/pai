<?php
/**
 * Created by PhpStorm.
 * User: waanghaiyang
 * Date: 2018/10/11
 * Time: 11:46
 */
namespace app\common\model;

use think\Model;

Class Serverchildruledata extends Model{
    /*
    * 添加绑定记录到数据库
    */
    public function addServerchildruleBindingRecord($child_rule_id,$ruleid,$serverid,$productid,$status)
    {
        $data = array();
        $returnArray = array();
        $data['product_id'] = $productid;
        $data['child_rule_id'] = $child_rule_id;
        $data['serverid'] = $serverid;
        $data['rule_id'] = $ruleid;
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
                'code' => 50006,
                'msg' => $errorModel::ERRORCODE[50006],
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
                    'code' => 50007,
                    'msg' => $errorModel::ERRORCODE[50007],
                    'data' => $result
                );
            }
        }else{
            $returnArray = array(
                'code' => 50008,
                'msg' => $errorModel::ERRORCODE[50008],
                'data' => array()
            );
        }
        return $returnArray;
    }


    /**批量删除子规则绑定
     * @param $serverid 服务器id
     * @param $product_id 产品id
     * @param $rule_id  规则id
     */
    public function delListRule($serverid = 0 ,$product_id = 0 ,$rule_id = 0)
    {
        $where = array();
        if($serverid != 0){
            $where['serverid'] = $serverid;
        }
        if($product_id != 0){
            $where['product_id'] = $product_id;
        }
        if($rule_id != 0){
            $where['rule_id'] = $rule_id;
        }
        if(!empty($where)){
            $result = self::where($where)->delete();
//            返回删除的行数
            return $result;
        }
    }


    /** 批量添加自规则绑定
     * @param $data 绑定数据的二维数组
     */
    public function addListRule($data)
    {
        $errorModel = new \app\common\model\Error();
        $returnArray = array();
        if(is_array($data)){
            $result = self::insertAll($data);
            if($result > 0){
                $returnArray = array(
                    'code' => 0,
                    'msg' => $errorModel::ERRORCODE[0],
                    'data' => $result
                );
            }else{
                $returnArray = array(
                    'code' => 50011,
                    'msg' => $errorModel::ERRORCODE[50011],
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


    public function updateRocode($data,$id){
        $errorModel = new \app\common\model\Error();
        $returnArray = array();
        if(is_array($data)){
            $result = self::where('id',$id)->update($data);
            if($result > 0){
                $returnArray = array(
                    'code' => 0,
                    'msg' => $errorModel::ERRORCODE[0],
                    'data' => $result
                );
            }else{
                $returnArray = array(
                    'code' => 50011,
                    'msg' => $errorModel::ERRORCODE[50011],
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

    public function getServerchildOne($id)
    {
        if(!empty($id)){
            $errorModel = new \app\common\model\Error();
            $returnArray = array();
            $result = self::where('id',$id)->select()->toArray();
            if($result){
                $returnArray = array(
                    'code' => 0,
                    'msg' => $errorModel::ERRORCODE[0],
                    'data' => $result[0]
                );
            }

        }
        return $returnArray;
    }
}