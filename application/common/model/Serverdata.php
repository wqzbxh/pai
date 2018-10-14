<?php
/**
 * Created by PhpStorm.
 * User: wanghaiyang
 * Date: 2018/10/10
 * Time: 11:15
 */
namespace app\common\model;

use think\Model;

Class Serverdata extends Model{

    /**
     * 添加数据
     * @param data 添加数组参数
     */
    public function addServer($data)
    {
        $errorModel = new \app\common\model\Error();
        $returnArray = array();
        if(is_array($data)){
            $checkResult = self::checkServer($data['servername']);
            if($checkResult > 0){
                $returnArray = array(
                    'code' => 40005,
                    'msg' => $errorModel::ERRORCODE[40005],
                    'data' => array()
                );
            }else{
                $result = self::insert($data);
                if($result == 1){
                    $returnArray = array(
                        'code' => 0,
                        'msg' => $errorModel::ERRORCODE[0],
                        'data' => $result
                    );
                }else{
                    $returnArray = array(
                        'code' => 40002,
                        'msg' => $errorModel::ERRORCODE[40002],
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



    /**校验重复的名称
     * @param $data
     */
    public function checkServer($name,$id = 0)
    {
        if($id == 0){
//        对新增数据进行名称查重 返回0/1
            $result = self::where(array('servername'=>$name))->count();
        }else{
//            对修改数据进行查重
            $result = self::where(array('servername'=>$name))->select()->toArray();

            if($result){
                if($result[0]['id'] == $id){
                    $result = 0 ;
                }else{
                    $result = 1;
                }
            }
        }
        return $result;
    }

    /**
     * 查询产品方法
     * @param $serveruserid 用户ID 前期默认为0；
     * @param $limit 限制多少条
     * @param $offset 从低第几条调开始
     * @param string $servername 产品名称
     */


    public function getServerList($servername = '',$offset,$limit,$serveruserid)
    {
        $criteria = array();
        $returnArray = array();
        $errorModel = new \app\common\model\Error();
        $criteria['is_del'] = 0;
        $criteria['serveruserid'] = $serveruserid;
        $result = self::where($criteria)
            ->limit($offset,$limit)
            ->select()
            ->toArray();

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
     * 获取单个产品信息
     * @param id 产品的自增ID
     */
    public function getServerOne($id)
    {
        $errorModel = new \app\common\model\Error();
        $returnArray = array();
        $result = self::where(array('id' => $id))->select()->toArray();
        if(!empty($result)){
            $returnArray = array(
                'code' => 0,
                'msg' => $errorModel::ERRORCODE[0],
                'data' => $result,
            );
        }else{
            $returnArray = array(
                'code' => 20004,
                'msg' => $errorModel::ERRORCODE[20004],
                'data' => array(),
            );
        }
        return $returnArray;
    }


    /**
     * 修改产品信息
     * @param Data 修改的数据集合 注释：data中必须含有产品的id
     *
     */
    public function updateServer($data)
    {
        $errorModel = new \app\common\model\Error();
        $returnArray = array();
        if(!empty($data['id'])){
            $checkResult = self::checkServer($data['servername'],$data['id']);
            if($checkResult > 0){
                $returnArray = array(
                    'code' => 40005,
                    'msg' => $errorModel::ERRORCODE[40005],
                    'data' => array()
                );
            }else{
                $result = self::where('id', $data['id'])->update($data);
                if($result == 1){
                    $returnArray = array(
                        'code' => 0,
                        'msg' => $errorModel::ERRORCODE[0],
                        'data' => $result,
                    );
                }else{
                    $returnArray = array(
                        'code' => 20008,
                        'msg' => $errorModel::ERRORCODE[20008],
                        'data' => $result,
                    );
                }
            }
        }else{
            $returnArray = array(
                'code' => 20006,
                'msg' => $errorModel::ERRORCODE[20006],
                'data' => array(),
            );
        }
        return $returnArray;
    }

    /**
     *
     */


    /**
     * 删除产品操作
     * @param id 产品的自增ID
     * Tue Oct 09 2018 15:10:18 GMT+0800 (中国标准时间)
     */
    public function delServerData($id)
    {
        $errorModel = new \app\common\model\Error();
        $returnArray = array();
        if(!empty($id)){
            $result = self::where('id', $id)->delete();
            if($result == 1){
                $returnArray = array(
                    'code' => 0,
                    'msg' => $errorModel::ERRORCODE[0],
                    'data' => $result,
                );
            }else{
                $returnArray = array(
                    'code' => 40004,
                    'msg' => $errorModel::ERRORCODE[40004],
                    'data' => $result,
                );
            }
        }else{
            $returnArray = array(
                'code' => 40003,
                'msg' => $errorModel::ERRORCODE[40003],
                'data' => array(),
            );
        }
        return $returnArray;
    }
}