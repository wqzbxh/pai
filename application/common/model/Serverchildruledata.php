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

    const XMLFILED = "c.*,scr.*,c.id as childruleid,p.product_type,c.childrule_exuri as update_childrule_exuri,p.match_type,scr.childrule_push_content as update_childrule_push_content,r.*,sr.status as serverrule_status,c.childrule_push_content as model_childrule_push_content";
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
                    'code' => 50018,
                    'msg' => $errorModel::ERRORCODE[50018],
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

    public static function getOne($data)
    {
        if(!empty($data)){
            $errorModel = new \app\common\model\Error();
            $returnArray = array();
            $result = self::where($data)->select()->toArray();

            if(!empty($result)){
                $returnArray = array(
                    'code' => 0,
                    'msg' => $errorModel::ERRORCODE[0],
                    'data' => $result[0]
                );
            }else{
                $returnArray = array(
                    'code' => 50019,
                    'msg' => $errorModel::ERRORCODE[50019],
                    'data' => []
                );
            }

        }
        return $returnArray;
    }

    /***
     * @param $serverid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */

    public static function ruleXmlsData($serverid)
    {

        $where = array(
            'scr.serverid' => $serverid,
            'scr.status' => 1,
            'r.rule_status' => 1,
            'sr.status' => 1,
            'c.childrule_status' => 1
        );
        if(!empty($serverid)){
//            查询所有绑定的产品的相关信息
            $result = self::alias('scr')
                ->join('productdata p','p.id = scr.product_id','LEFT')
                ->join('ruledata r','r.id = scr.rule_id','LEFT')
                ->join('childruledata c','c.id = scr.child_rule_id','LEFT')
                ->join('serverruledata sr','sr.rule_id = scr.rule_id and sr.serverid = scr.serverid','LEFT')
                ->order('r.order desc')
                ->where($where)
                ->field(self::XMLFILED)
                ->select()
                ->toArray();
            return $result;
        }
    }



    /**
     * @param $serverid服务器id
     * 复制产品绑定记录
     */
    public static function copy($serverid,$newServerid)
    {
        if(!empty($serverid)){
            $result = self::where(array('serverid'=>$serverid))
                ->field('serverid,product_id,rule_id,child_rule_id,status,childrule_exuri,createtime,childrule_push_content,binding_childrule_host,binding_childrule_ratio')
                ->select()
                ->toArray();
            if(!empty($result)){
                $resultInfo = [];
                $i = 0;
                foreach ($result as $values){
                    $values['serverid'] = $newServerid;
                    $resultInfo[$i] = $values;
                    $i++;
                }
                self::insertAll($resultInfo);
            }
        }
    }
}