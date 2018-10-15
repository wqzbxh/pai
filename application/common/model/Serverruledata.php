<?php
/**
 * Created by PhpStorm.
 * User: wanghaiyang
 * Date: 2018/10/11
 * Time: 9:44
 */
namespace app\common\model;

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
}