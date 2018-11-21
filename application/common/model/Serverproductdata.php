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
            if(!empty($data)){
                $result = self::where(array('serverid'=>$serverid,'product_id'=>$productid))->update($data);//修改产品绑定记录

                if($result=1){
                    /////////////////////////////////////////////////////////////////////////////////////////////////////
                    $serverruletModel = new \app\common\model\Serverruledata();
                    $ruledataModel = new \app\common\model\Ruledata();
                    $resultServerRuleID = $serverruletModel->where(array('serverid'=>$serverid,'product_id'=>$productid))->field('rule_id')->select()->toArray();//取出相应的规则记录的规则id
                    $allResultServerRuleID = array_map('array_shift',$resultServerRuleID);

                    $allResultruleId = $ruledataModel->where(array('productid'=>$productid))->field('id')->select()->toArray();//取出规则表里面的该产品的所有的规则
                    $allResultruleId = array_map('array_shift',$allResultruleId);

//                取交集
                    $allUpdateId = array_intersect($allResultServerRuleID,$allResultruleId);
                    $allUpdateId = implode(',',$allUpdateId);
//                取差集
                    $allAddId = array_diff($allResultruleId,$allResultServerRuleID);

//              做修改
                    $updateWhere['rule_id'] = array('in',$allUpdateId);
                    $updateWhere['serverid'] =$data['serverid'];
                    $updateWhere['product_id'] = $data['product_id'];
                    $serverruletModel->where($updateWhere)->update(['status' => $data['status']]);
//              做新增
                    $i = 0;
                    if(!empty($allAddId)){
                        foreach ($allAddId as $key => $value){
                            $serverChildRuleDatas[$i]['rule_id'] = $value;
                            $serverChildRuleDatas[$i]['product_id'] = $data['product_id'];
                            $serverChildRuleDatas[$i]['serverid'] = $data['serverid'];
                            $serverChildRuleDatas[$i]['createtime'] = time();
                            $serverChildRuleDatas[$i]['status'] = $status;
                            $i ++;
                        }
                        $listResult = $serverruletModel->addListRule($serverChildRuleDatas);
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
                    }

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    //对子规则进行操作
                    $serverchildruleDataModel = new \app\common\model\Serverchildruledata();
                    $childDataModel = new \app\common\model\Childruledata();

                    $childResultData = $childDataModel->where(array('productid'=>$productid))->field('id,ruleid')->select()->toArray();//取出规则表里面的该产品的所有的规则

                    $childResultDataInfo = [];
                    foreach ($childResultData as $value){//取出该产品下的全部的子规则
                        $childResultDataInfo[$value['id']] = $value['ruleid'];
                    }

                    $serverChildRuleData = $serverchildruleDataModel->where(array('serverid'=>$serverid,'product_id'=>$productid))->field('rule_id,child_rule_id')->select()->toArray();//取出相应的规则记录的规则id

                    $serverChildRuleDataInfo = [];
                    foreach ($serverChildRuleData as $value){//取出该产品下的全部的已绑定子规则
                        $serverChildRuleDataInfo[$value['child_rule_id']] = $value['rule_id'];
                    }
//                取出差集 新增的

                    $diffData = array_diff_assoc($childResultDataInfo,$serverChildRuleDataInfo);
                    $intersectData = array_intersect_assoc($childResultDataInfo,$serverChildRuleDataInfo);



                    $updateChildId = array_keys($intersectData);
                    $updateRuleId = implode(',',array_unique(array_values($intersectData)));//取出规则的ID
                    $allUpdateId = implode(',',$updateChildId);//子规则的id


//                  做修改
//                    $updateWhere['child_rule_id'] = array('in',$allUpdateId);
//                    $updateWhere['serverid'] =$data['serverid'];
//                    $updateWhere['product_id'] = $data['product_id'];
//                    $updateWhere['rule_id'] = array('in',$updateRuleId);
//                    $serverchildruleDataModel->where($updateWhere)->update(['status' => $data['status']]);


//                   作新增
                    //          添加该规则下所有自规则绑定
                    $i = 0;
                    if(!empty($diffData)){
                        $serverChildRuleDatas = [];
                        foreach ($diffData as $key => $value){
                            $serverChildRuleDatas[$i]['serverid'] = $serverid;
                            $serverChildRuleDatas[$i]['product_id'] = $productid;
                            $serverChildRuleDatas[$i]['rule_id'] = $value;
                            $serverChildRuleDatas[$i]['childrule_exuri'] = '';
                            $serverChildRuleDatas[$i]['childrule_push_content'] = '';
                            $serverChildRuleDatas[$i]['child_rule_id'] = $key;
                            $serverChildRuleDatas[$i]['createtime'] = time();
                            $serverChildRuleDatas[$i]['status'] = $status;
                            $serverChildRuleDatas[$i]['binding_childrule_host'] = '';
                            $i ++;
                        }

                        $listResult = $serverchildruleDataModel->addListRule($serverChildRuleDatas);

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
                    }
                }
            }



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
        if(!empty($data)){//删除服务器
            if(array_key_exists('serverid',$data)){
                self::where(array('serverid'=>$data['serverid']))->delete();
                Serverchildruledata::destroy(array('serverid'=> $data['serverid']));
                Serverruledata::destroy(array('serverid'=> $data['serverid']));
            }elseif(array_key_exists('product_idd',$data)){//产品产品
                self::where(array('product_id'=>$data['product_idd']))->delete();
                Serverchildruledata::destroy(array('product_id'=> $data['product_idd']));
                Serverruledata::destroy(array('product_id'=> $data['product_idd']));
            }else{
                self::where(array('id'=>$data['id']))->delete();
                Serverchildruledata::destroy(array('product_id'=> $data['productid'],'serverid'=> $data['server_id']));
                Serverruledata::destroy(array('product_id'=> $data['productid'],'serverid'=> $data['server_id']));
            }
            $returnArray = array(
                'code' => 0,
                'msg' => $errorModel::ERRORCODE[0],
                'data' => []
            );
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


    public function updateRocode($data)
    {
        $errorModel = new \app\common\model\Error();
        $returnArray = [];
        if(!empty($data)){
            $serverid = $data['serverid'];
            $productid = $data['product_id'];
            $status = $data['status'];
            $result = self::where(array('serverid'=>$serverid,'product_id'=>$productid))->update($data);//修改产品绑定记录

            if($result=1){
                /////////////////////////////////////////////////////////////////////////////////////////////////////
                $serverruletModel = new \app\common\model\Serverruledata();
                $ruledataModel = new \app\common\model\Ruledata();
                $resultServerRuleID = $serverruletModel->where(array('serverid'=>$serverid,'product_id'=>$productid))->field('rule_id')->select()->toArray();//取出相应的规则记录的规则id
                $allResultServerRuleID = array_map('array_shift',$resultServerRuleID);

                $allResultruleId = $ruledataModel->where(array('productid'=>$productid))->field('id')->select()->toArray();//取出规则表里面的该产品的所有的规则
                $allResultruleId = array_map('array_shift',$allResultruleId);

//                取交集
                $allUpdateId = array_intersect($allResultServerRuleID,$allResultruleId);
                $allUpdateId = implode(',',$allUpdateId);
//                取差集
                $allAddId = array_diff($allResultruleId,$allResultServerRuleID);

//              做修改
                $updateWhere['rule_id'] = array('in',$allUpdateId);
                $updateWhere['serverid'] =$data['serverid'];
                $updateWhere['product_id'] = $data['product_id'];
                $serverruletModel->where($updateWhere)->update(['status' => $data['status']]);
//              做新增
                $i = 0;
                if(!empty($allAddId)){
                    foreach ($allAddId as $key => $value){
                        $serverChildRuleDatas[$i]['rule_id'] = $value;
                        $serverChildRuleDatas[$i]['product_id'] = $data['product_id'];
                        $serverChildRuleDatas[$i]['serverid'] = $data['serverid'];
                        $serverChildRuleDatas[$i]['createtime'] = time();
                        $serverChildRuleDatas[$i]['status'] = $status;
                        $i ++;
                    }
                    $listResult = $serverruletModel->addListRule($serverChildRuleDatas);
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
                }
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                //对子规则进行操作
                $serverchildruleDataModel = new \app\common\model\Serverchildruledata();
                $childDataModel = new \app\common\model\Childruledata();

                $childResultData = $childDataModel->where(array('productid'=>$productid))->field('id,ruleid')->select()->toArray();//取出规则表里面的该产品的所有的规则

                $childResultDataInfo = [];
                foreach ($childResultData as $value){
                    $childResultDataInfo[$value['id']] = $value['ruleid'];
                }

                $serverChildRuleData = $serverchildruleDataModel->where(array('serverid'=>$serverid,'product_id'=>$productid))->field('rule_id,child_rule_id')->select()->toArray();//取出相应的规则记录的规则id

                $serverChildRuleDataInfo = [];
                foreach ($serverChildRuleData as $value){
                    $serverChildRuleDataInfo[$value['child_rule_id']] = $value['rule_id'];
                }
//                取出差集 新增的
                $diffData = array_diff_assoc($childResultDataInfo,$serverChildRuleDataInfo);
//                取交集  修改的
                $intersectData = array_intersect_assoc($childResultDataInfo,$serverChildRuleDataInfo);
                $updateChildId = array_keys($intersectData);
                $updateRuleId = implode(',',array_unique(array_values($intersectData)));
                $allUpdateId = implode(',',$updateChildId);//子规则的id

//          做修改
                $updateWhere['child_rule_id'] = array('in',$allUpdateId);
                $updateWhere['serverid'] =$data['serverid'];
                $updateWhere['product_id'] = $data['product_id'];
                $updateWhere['rule_id'] = array('in',$updateRuleId);
                $serverUpdateResult = $serverchildruleDataModel->where($updateWhere)->update(['status' => $data['status']]);
                $returnArray = array(
                    'code' => 0,
                    'msg' => $errorModel::ERRORCODE[0],
                    'data' => $serverUpdateResult
                );

//            作新增
    //          添加该规则下所有自规则绑定
                $i = 0;
                if(!empty($diffData)){
                    $serverChildRuleDatas = [];
                    foreach ($diffData as $key => $value){
                        $serverChildRuleDatas[$i]['child_rule_id'] = $key;
                        $serverChildRuleDatas[$i]['rule_id'] = $value;
                        $serverChildRuleDatas[$i]['product_id'] =$productid;
                        $serverChildRuleDatas[$i]['serverid'] = $serverid;
                        $serverChildRuleDatas[$i]['createtime'] = time();
                        $serverChildRuleDatas[$i]['status'] =$status;
                        $i ++;
                    }
                    $listResult = $serverchildruleDataModel->addListRule($serverChildRuleDatas);

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
                }

            }
        }

        return $returnArray;
    }


    /**
     * @param $serverid服务器id
     * 复制产品绑定记录
     */
    public static function copy($serverid,$newServerid)
    {
        if(!empty($serverid)){
            $result = self::where(array('serverid'=>$serverid))
                ->field('serverid,product_id,status,createtime')
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