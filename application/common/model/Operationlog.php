<?php
/**
 * Created by PhpStorm.
 * User: wanghaiyang
 * Date: 2018/11/14
 * Time: 15:45
 */
namespace app\common\model;

use think\Model;

/**
 * Class Operationlog
 * @package app\common\model
 * 操作日志模型
 */

Class Operationlog extends Model
{

    //对一下记录做出日志统计
//    删除链接检查结果

    const fuzzy_query = 'u.username|o.operate_info';
    const returnfiled = 'u.username,u.id as uid,o.*';
    /**
     * 添加操作行为
     * @param int $user_id
     * @param string $module
     * @param string $controller
     * @param string $method
     * @param string $operate_type
     * @param string $operate_info
     */
    public static function addOperation($user_id = 0,$module = '',$controller = '',$method = '',$operate_type = '',$operate_info = '')
    {
        $data = [];
        $data['user_id'] = $user_id;
        $data['module'] = $module;
        $data['controller'] = $controller;
        $data['method'] = $method;
        $data['operate_type'] = $operate_type;
        $data['operate_info'] = $operate_info;
        $data['createtime'] = time();
        self::insert($data);
    }

    /**
     * @param string $content
     * @param $offset
     * @param $limit
     * @param $startTime
     * @param $endTime
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getOperationlog($content = '',$offset,$limit,$startTime,$endTime)
    {
        $criteria = array();
        $returnArray = array();
        $errorModel = new \app\common\model\Error();
        if(!empty($content)){//搜索
            if($startTime != 0 && $endTime != 0){
                $endTime = strtotime($endTime);
                $startTime = strtotime($startTime);
                $result = self::alias('o')
                    ->join('userdata u','o.user_id = u.id','LEFT')
                    ->where('o.createtime','<',$endTime)
                    ->where('o.createtime','>',$startTime)
                    ->where(self::fuzzy_query,'like','%'.$content.'%')
                    ->limit($offset,$limit)
                    ->field(self::returnfiled)
                    ->order('o.id desc')
                    ->select()
                    ->toArray();
                $count = self::alias('o')
                    ->join('userdata u','o.user_id = u.id','LEFT')
                    ->where('o.createtime','<',$endTime)
                    ->where('o.createtime','>',$startTime)
                    ->where(self::fuzzy_query,'like','%'.$content.'%')
                    ->count();
            }else{
                $result = self::alias('o')
                    ->join('userdata u','o.user_id = u.id','LEFT')
                    ->where($criteria)
                    ->where(self::fuzzy_query,'like','%'.$content.'%')
                    ->limit($offset,$limit)
                    ->field(self::returnfiled)
                    ->order('o.id desc')
                    ->select()
                    ->toArray();
                $count = self::alias('o')
                    ->join('userdata u','o.user_id = u.id','LEFT')
                    ->where($criteria)
                    ->where(self::fuzzy_query,'like','%'.$content.'%')
                    ->count();
            }
        }else{
            if($startTime != 0 && $endTime != 0){
                $endTime = strtotime($endTime);
                $startTime = strtotime($startTime);
                $result =self::alias('o')
                    ->join('userdata u','o.user_id = u.id','LEFT')
                    ->where('o.createtime','<',$endTime)
                    ->where('o.createtime','>',$startTime)
                    ->limit($offset,$limit)
                    ->field(self::returnfiled)
                    ->order('o.id desc')
                    ->select()
                    ->toArray();
                $count =self::alias('o')
                    ->join('userdata u','o.user_id = u.id','LEFT')
                    ->where('o.createtime','<',$endTime)
                    ->where('o.createtime','>',$startTime)
                    ->count();
            }else{
                if($limit == 0){
                    $result =self::alias('o')
                        ->join('userdata u','o.user_id = u.id','LEFT')
                        ->order('o.id desc')
                        ->select()
                        ->toArray();
                    $count = self::count();
                }else{
                    $result = self::alias('o')
                        ->join('userdata u','o.user_id = u.id','LEFT')
                        ->limit($offset,$limit)
                        ->field(self::returnfiled)
                        ->order('o.id desc')
                        ->select()
                        ->toArray();
                    $count = self::count();
                }
            }
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