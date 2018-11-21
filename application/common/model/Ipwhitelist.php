<?php
/**
 * Created by PhpStorm.
 * User: k
 * Date: 2018/10/15
 * Time: 16:55
 */
namespace app\common\model;

use think\Model;

Class Ipwhitelist extends Model{

    /**
     * @param $limit 限制几条
     * @param $offset 从第几条开始
     * @param int $serveruserid 服务器id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList($limit,$offset,$serveruserid = 0,$content)
    {

        $criteria = array();
        $returnArray = array();
        $errorModel = new \app\common\model\Error();
        if($serveruserid != 0){
            $criteria['serverid'] = $serveruserid;
        }
        if(!empty($content)){
            $result = self::where($criteria)
                ->limit($offset,$limit)
                ->where('content','like','%'.$content.'%')
                ->select()
                ->toArray();
            $count = self::where($criteria)
                ->where('content','like','%'.$content.'%')
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
            $result = self::insert($data);
            if($result == 1){
                $returnArray = array(
                    'code' => 0,
                    'msg' => $errorModel::ERRORCODE[0],
                    'data' => array()
                );
            }else{
                $returnArray = array(
                    'code' => 60006,
                    'msg' => $errorModel::ERRORCODE[60006],
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

    /**修改黑名单记录
     * @param $data 修改记录的参数数组
     * @return array
     */
    public function upDateRecode($where,$data)
    {
        $returnArray = array();
        $errorModel = new \app\common\model\Error();
        if(is_array($data)){
            $result = self::where($where)->update($data);
            if($result == 1){
                $returnArray = array(
                    'code' => 0,
                    'msg' => $errorModel::ERRORCODE[0],
                    'data' => array()
                );
            }else{
                $returnArray = array(
                    'code' => 60008,
                    'msg' => $errorModel::ERRORCODE[60008],
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
                    'code' => 60002,
                    'msg' => $errorModel::ERRORCODE[60002],
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
            if($result > 0){
                $returnArray = array(
                    'code' => 0,
                    'msg' => $errorModel::ERRORCODE[0],
                    'data' => array()
                );
            }else{
                $returnArray = array(
                    'code' => 60009,
                    'msg' => $errorModel::ERRORCODE[60009],
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
     * @param $data获取名单的条件升级
     */
    public static function getListUpgrade($limit = 0,$offset = 0 ,$iptype = 9 ,$serveruserid = 0)
    {
        $criteria = array();
        $returnArray = array();
        $errorModel = new \app\common\model\Error();
        if($serveruserid != 0){
            $criteria['serverid'] = $serveruserid;
        }
        if($iptype != 9){
            $criteria['iptype'] = $iptype;
        }
        if($limit != 0 && $offset != 0){
            $result = self::where($criteria)->limit($offset,$limit)->select()->toArray();
        }else{
            $result = self::where($criteria)->select()->toArray();
        }
        $count = self::where($criteria)
            ->count();

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

    /**
     * @param $serverid服务器id
     * 复制黑名单记录
     */
    public static function copy($serverid,$newServerid)
    {
        if(!empty($serverid)){
            $result = self::where(array('serverid'=>$serverid))
                ->field('serverid,iptype,content,format')
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