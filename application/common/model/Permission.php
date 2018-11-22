<?php
/**
 * Created by PhpStorm.
 * User: wanghaiyang
 * Date: 2018/11/21
 * Time: 16:44
 */

namespace app\common\model;

    use think\Model;

Class Permission extends  Model{

    /**
     * @param $data 获取权限
     */
    public static function getOne($data)
    {
        $returnArray = [];
        if(is_array($data)){
            $result = self::where($data)->find();
            if(!empty($result)){
                $result = $result->toArray();
                $returnArray = [
                    'code' => 0,
                    'msg' => Error::ERRORCODE[0],
                    'data' => $result
                ];
            }else{
                $returnArray = [
                    'code' => 17001,
                    'msg' => Error::ERRORCODE[17001],
                    'data' => []
                ];
            }
        }else{
            $returnArray = [
                'code' => 10002,
                'msg' => Error::ERRORCODE[10002],
                'data' => []
            ];
        }
        return $returnArray;
    }

}