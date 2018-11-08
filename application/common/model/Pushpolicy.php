<?php
/**
 * Created by PhpStorm.
 * User: wanghaiyang
 * Date: 2018/10/19
 * Time: 11:58
 */
namespace app\common\model;

use think\Model;

Class Pushpolicy extends Model{

    /**
     * @param $limit 限制几条
     * @param $offset 从第几条开始x
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList($limit,$offset,$name)
    {

        $criteria = array();
        $returnArray = array();
        $errorModel = new \app\common\model\Error();

        if(!empty($name)){
            $result = self::where($criteria)
                ->limit($offset,$limit)
                ->where('name','like','%'.$name.'%')
                ->select()
                ->toArray();

            $count = self::where($criteria)
                ->where('name','like','%'.$name.'%')
                ->count();
        }else{
            $result = self::where($criteria)
                ->limit($offset,$limit)
                ->select()
                ->toArray();

            $count = self::where($criteria)
                ->count();
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

    /**添加黑名单记录
     * @param $data 添加记录的参数数组
     * @return array
     */
    public function addAction($data)
    {
        $returnArray = array();
        $errorModel = new \app\common\model\Error();
        if(is_array($data)){
            $checkResult = self::checkRule($data['seq'],$data['name']);
            if($checkResult == 1){
                $returnArray = array(
                    'code' => 11005,
                    'msg' => $errorModel::ERRORCODE[11005],
                    'data' => array()
                );
            }elseif ($checkResult == 2){
                $returnArray = array(
                    'code' => 11008,
                    'msg' => $errorModel::ERRORCODE[11008],
                    'data' => array()
                );
            }else{
                $result = self::insert($data);
                if($result == 1){
                    $returnArray = array(
                        'code' => 0,
                        'msg' => $errorModel::ERRORCODE[0],
                        'data' => array()
                    );
                }else{
                    $returnArray = array(
                        'code' => 11001,
                        'msg' => $errorModel::ERRORCODE[11001],
                        'data' => array()
                    );
                }
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

    /**修改黑名单记录
     * @param $data 修改记录的参数数组
     * @return array
     */
    public function upDateRecode($where,$data)
    {
        $returnArray = array();
        $errorModel = new \app\common\model\Error();
        if(is_array($data)){
            $checkResult = self::checkRule($data['seq'],$data['name'],$data['id']);
            if($checkResult == 1){
                $returnArray = array(
                    'code' => 11005,
                    'msg' => $errorModel::ERRORCODE[11005],
                    'data' => array()
                );
            }elseif ($checkResult == 2){
                $returnArray = array(
                    'code' => 11008,
                    'msg' => $errorModel::ERRORCODE[11008],
                    'data' => array()
                );
            }else{
                $result = self::where($where)->update($data);
                if($result == 1){
                    $returnArray = array(
                        'code' => 0,
                        'msg' => $errorModel::ERRORCODE[0],
                        'data' => array()
                    );
                }else{
                    $returnArray = array(
                        'code' => 11003,
                        'msg' => $errorModel::ERRORCODE[11003],
                        'data' => array()
                    );
                }
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
     * @param $data获取名单的条件
     */
    public function getListONe($data)
    {
        $errorModel = new \app\common\model\Error();
        $returnArray = array();
        if(is_array($data)){
            $result = self::where($data)->select()->toArray();
            if(!empty($result)){
                $returnArray = array(
                    'code' => 0,
                    'msg' => $errorModel::ERRORCODE[0],
                    'data' => $result
                );
            }else{
                $returnArray = array(
                    'code' => 11004,
                    'msg' => $errorModel::ERRORCODE[11004],
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


    /**删除黑名单记录
     * @param $data 修改记录的参数数组
     * @return array
     */
    public function delRecode($where)
    {
        $returnArray = array();
        $errorModel = new \app\common\model\Error();
        if(is_array($where)){
            $result = self::where($where)->delete();
            Childruledata::update(array('userpushtimepolicy'=>0),array('userpushtimepolicy'=>$where['seq']));
            if($result == 1){
                $returnArray = array(
                    'code' => 0,
                    'msg' => $errorModel::ERRORCODE[0],
                    'data' => array()
                );
            }elseif ($result == 2){
                $returnArray = array(
                    'code' => 0,
                    'msg' => $errorModel::ERRORCODE[0],
                    'data' => array()
                );
            }else{
                $returnArray = array(
                    'code' => 11002,
                    'msg' => $errorModel::ERRORCODE[11002],
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
     * @param $data 校验名称是否存在
     */
    public function checkRule($seq,$name,$id = 0)
    {

        if($id == 0){
//        对新增数据进行名称查重 返回0/1
            $result = self::where(array('name'=>$name))->count();
            if($result > 0){
//                名称已存在
                return 1;
            }else{
                $result = self::where(array('seq'=>$seq))->count();
                if($result > 0){
//                编号已存在
                    return 2;
                }
            }
        }else{
//            对修改数据进行查重
            $result = self::where(array('name'=>$name))->select()->toArray();

            if($result){
                if($result[0]['id'] == $id){//证明修改的记录是当前已经存在数据库 验证编号：
                    $result = self::where(array('seq'=>$seq))->select()->toArray();
                    if($result){
                        if($result[0]['id'] == $id) {//按编号修改的记录是当前已经存在数据
                            $result = 0;
                        }else{
                            $result = 2;
                        }
                    }else{
                        $result = 0;
                    }
                }else{
//                名称已存在
                    $result = 1;
                }
            }else{
                $result = self::where(array('seq'=>$seq))->select()->toArray();
                if($result){
                    if($result[0]['id'] == $id) {//按编号修改的记录是当前已经存在数据
                        $result = 0;
                    }else{
                        $result = 2;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * 获取所有策略组
     */
    /**
     * @param $criteria 查询条件
     * @param string $filed
     * @param $offset
     * @param $limit
     */
    public static function getTactics($criteria,$filed = '*',$offset = 0,$limit = 0)
    {
        $returnArray = array();
        $errorModel = new \app\common\model\Error();
        if($offset == 0 && $limit == 0){
            $result = self::where($criteria)->field($filed)->select()->toArray();
        }else{
            $result = self::where($criteria)->field($filed)->limit($offset,$limit)->select()->toArray();
        }
        $count = self::where($criteria) ->count();
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
