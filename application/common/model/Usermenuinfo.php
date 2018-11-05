<?php
/**
 * Created by PhpStorm.
 * User: wanghaiyang
 * Date: 2018/11/2
 * Time: 10:36
 */

namespace app\common\model;

use think\Model;

/**
 * Class Usermenuinfo
 * @package app\common\model
 * @expain 用户菜单表
 */
Class Usermenuinfo extends Model{
    /**
     * 获取用户的菜单列表关系集合
     */
    public static function getUsermenuinfoList($userId)
    {
        $resultArray = [];
        $result = self::where('user_id',$userId)->field('menu_id')->select()->toArray();
        if(!empty($result)){
            $resultArray = [
                'code' => 0,
                'msg' => Error::ERRORCODE[0],
                'data' => $result
            ];
        }else{
            $resultArray = [
                'code' => 13009,
                'msg' => Error::ERRORCODE[13009],
                'data' => []
            ];
        }
        return $resultArray;
    }


    public static function addAction($data)
    {
        $resultArray = [];
        $result = self::insertAll($data);
        if($result){
            $resultArray = [
                'code' => 0,
                'msg' => Error::ERRORCODE[0],
                'data' => []
            ];
        }else{
            $resultArray = [
                'code' => 0,
                'msg' => Error::ERRORCODE[0],
                'data' => []
            ];
        }
    }

    public static function del($data)
    {
        if(!empty($data)){
            self::where($data)->delete();
        }
    }
}