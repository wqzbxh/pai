<?php
/**
 * Created by PhpStorm.
 * User: wanghaiyang
 * Date: 2018/10/11
 * Time: 9:44
 */
namespace app\common\model;

use think\migration\command\migrate\Status;
use think\Model;

Class Serverruledata extends Model{
    /*
     * 添加绑定记录到数据库
     */
    public function addServerruleBindingRecord($ruleid,$serverid,$productid,$status)
    {
        $data = array();
        $returnArray = array();
        $serverChildRuleDatas = array();

//        开始事物
        self::startTrans();
        try{
//          添加规则、产品、服务器之间的绑定
            $data['product_id'] = $productid;
            $data['serverid'] = $serverid;
            $data['rule_id'] = $ruleid;
            $data['status'] = $status;
            $data['createtime'] = time();

            $result = self::insert($data);

            $ServerchildruleDataModel = new \app\common\model\Serverchildruledata();
            $errorModel = new \app\common\model\Error();
//          删除所有该服务器下该产品该规则下的所有的子规则绑定记录，目的清除完
            $delResult = $ServerchildruleDataModel->delListRule($serverid,$productid,$ruleid);
//          添加该规则下所有自规则绑定
            $childRuleDataModel = new \app\common\model\Childruledata();
            $childRuleIds = $childRuleDataModel->where('ruleid',$ruleid)->field('id as child_rule_id')->select()->toArray();
            $i = 0;
            if(!empty($childRuleIds)){
                foreach ($childRuleIds as $value){
                    $serverChildRuleDatas[$i]['child_rule_id'] = $value['child_rule_id'];
                    $serverChildRuleDatas[$i]['rule_id'] = $ruleid;
                    $serverChildRuleDatas[$i]['product_id'] = $productid;
                    $serverChildRuleDatas[$i]['serverid'] = $serverid;
                    $serverChildRuleDatas[$i]['createtime'] = time();
                    $serverChildRuleDatas[$i]['status'] = 1;
                    $i ++;
                }
                $listResult = $ServerchildruleDataModel->addListRule($serverChildRuleDatas);

                if($listResult['code'] == 0 && $result == 1){
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
            }else{
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
            }
            // 提交事务
            self::commit();
        } catch (\Exception $e) {
            // 回滚事务
            $returnArray = array(
                'code' => 50012,
                'msg' => $errorModel::ERRORCODE[50012],
                'data' => array()
            );
            self::rollback();
        }

        return $returnArray;
    }

    /****
     * 接触服务器与产品绑定
     * 思路直接删除记录即可
     */
    public function delBindingRecord($id,$serverid,$productid,$ruleid)
    {
        $returnArray = array();
        $errorModel = new \app\common\model\Error();
        if(empty($id) == false && !empty($serverid) && !empty($productid) && !empty($ruleid)){

            $ServerchildruleDataModel = new \app\common\model\Serverchildruledata();
//          删除所有该服务器下该产品该规则下的所有的子规则绑定记录，目的清除完
            $delResult = $ServerchildruleDataModel->delListRule($serverid,$productid,$ruleid);
//          删除该服务器下该产品下该规则
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

    /**\
     * 删除优化
     * 根据条件删除
     */
    public function unbundle($data)
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
                    'code' => 50007,
                    'msg' => $errorModel::ERRORCODE[50007],
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
     * @param $data
     * @param $id
     * @return array
    'rule_id'=> $_POST['rule_id'],
    'serverid'=> $_POST['serverid'],
    'product_id'=> $_POST['product_id'],
     */

    public function updateRocode($data,$id){
        $errorModel = new \app\common\model\Error();
        $returnArray = array();
        if(is_array($data)){
           $result = self::where('id',$id)->update($data);
            $ServerchildruleDataModel = new \app\common\model\Serverchildruledata();

            $childRuleDataModel = new \app\common\model\Childruledata();
//          查出所有子规则的id\
            $allChildId = $childRuleDataModel->where('ruleid',$data['rule_id'])->field('id')->select()->toArray();
            $allChildId = array_map('array_shift',$allChildId);
//          查出所有已经绑定的子规则的id
            $allServerChildId = $ServerchildruleDataModel->where(array('rule_id'=>$data['rule_id'],'serverid'=>$data['serverid'],'product_id'=> $data['product_id']))->field('child_rule_id')->select()->toArray();
            $allServerChildId = array_map('array_shift',$allServerChildId);
//           取出交集（取出的交集只做修改状态）
            $allUpdateId = array_intersect($allServerChildId,$allChildId);
            $allUpdateId = implode(',',$allUpdateId);
//           取出差集（取出的差集做新增，状态为$data['status']）
            $allAddId = array_diff($allChildId,$allServerChildId);
//          做修改
            $updateWhere['child_rule_id'] = array('in',$allUpdateId);
            $updateWhere['serverid'] =$data['serverid'];
            $updateWhere['product_id'] = $data['product_id'];
            $updateWhere['rule_id'] = $data['rule_id'];
            $ServerchildruleDataModel->where($updateWhere)->update(['status' => $data['status']]);

//            作新增
//          添加该规则下所有自规则绑定
            $i = 0;
            if(!empty($allAddId)){
                foreach ($allAddId as $key => $value){
                    $serverChildRuleDatas[$i]['child_rule_id'] = $value;
                    $serverChildRuleDatas[$i]['rule_id'] = $data['rule_id'];
                    $serverChildRuleDatas[$i]['product_id'] = $data['product_id'];
                    $serverChildRuleDatas[$i]['serverid'] = $data['serverid'];
                    $serverChildRuleDatas[$i]['createtime'] = time();
                    $serverChildRuleDatas[$i]['status'] = $data['status'];
                    $i ++;
                }
                $listResult = $ServerchildruleDataModel->addListRule($serverChildRuleDatas);

                if($listResult['code'] == 0 && $result == 1){
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
            }else{
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
            }
            if($result > 0){
                $returnArray = array(
                    'code' => 0,
                    'msg' => $errorModel::ERRORCODE[0],
                    'data' => $result
                );
            }else{
                $returnArray = array(
                    'code' => 50019,
                    'msg' => $errorModel::ERRORCODE[50019],
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

    /**
     * @param $data 查询条件
     */
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


    /**
     * @param $serverid服务器id
     * 复制规则绑定记录
     */
    public static function copy($serverid,$newServerid)
    {
        if(!empty($serverid)){
            $result = self::where(array('serverid'=>$serverid))
                ->field('serverid,product_id,rule_id,status,createtime')
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