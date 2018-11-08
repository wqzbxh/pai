<?php
/**
 * Created by PhpStorm.
 * User: k
 * Date: 2018/11/8
 * Time: 14:12
 */
namespace app\common\model;

use app\common\controller\Index;
use think\Model;

Class Warningdata extends Model{

    /**
     * @param string $link 内容
     * @param $offset 从第几条开始
     * @param $limit 限制几条
     * @return array 返回
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getContentList($link = '',$offset,$limit,$startTime,$endTime)
    {
        $criteria = array();
        $returnArray = array();
        $errorModel = new \app\common\model\Error();
        if(!empty($link)){//搜索
            if($startTime != 0 && $endTime != 0){
                $endTime = strtotime($endTime);
                $startTime = strtotime($startTime);
                $result = self::where('time','<',$endTime)
                    ->where('time','>',$startTime)
                    ->where('content','like','%'.$link.'%')
                    ->limit($offset,$limit)
                    ->order('is_dispose asc')
                    ->select()
                    ->toArray();
                $count = self::where('time','<',$endTime)
                    ->where('time','>',$startTime)
                    ->where('content','like','%'.$link.'%')
                    ->count();
                }else{
                    $result = self::where($criteria)->where('content','like','%'.$link.'%')->limit($offset,$limit)->order('is_dispose asc')->select()->toArray();
                    $count = self::where($criteria)->where('content','like','%'.$link.'%')->count();
                }
        }else{
            if($startTime != 0 && $endTime != 0){
                $endTime = strtotime($endTime);
                $startTime = strtotime($startTime);
                $result = self::where('time','<',$endTime)
                    ->where('time','>',$startTime)
                    ->limit($offset,$limit)
                    ->order('is_dispose asc')
                    ->select()
                    ->toArray();
                $count = self::where('time','<',$endTime)
                    ->where('time','>',$startTime)
                    ->count();
            }else{
                if($limit == 0){
                    $result = self::select()->order('is_dispose asc')->toArray();
                    $count = self::count();
                }else{
                    $result = self::limit($offset,$limit)->order('is_dispose asc')->select()->toArray();
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


    public static function chanage($id)
    {
        $returnArray = array();
        if(!empty($id)){
            $result = self::update(array('is_dispose'=>1),array('id'=>$id));
            if($result){
                $returnArray = array(
                    'code' => 0,
                    'msg' => Error::ERRORCODE[0],
                    'data' => $result
                );
            }else{
                $returnArray = array(
                    'code' => 16004,
                    'msg' => Error::ERRORCODE[16004],
                    'data' => $result
                );
            }
        }else{
              $returnArray = array(
                  'code' => 10005,
                  'msg' => Error::ERRORCODE[10005],
                  'data' => []
              );
        }
        return $returnArray;
    }


    /**
     *
     */
    public static function insertAllAction($data)
    {
        $returnArray = [];
        if(!empty($data)){
            $result = self::insertAll($data);
            if($result){
                $returnArray = array(
                    'code' => 0,
                    'msg' => Error::ERRORCODE[0],
                    'data' => $result
                );
            }
        }else{
            $returnArray = array(
                'code' => 16005,
                'msg' => Error::ERRORCODE[16005],
                'data' => []
            );
        }
        return $returnArray;
    }

}