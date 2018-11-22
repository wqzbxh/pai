<?php
/**
 * Created by PhpStorm.
 * User: wanghaiyang
 * Date: 2018/11/21
 * Time: 16:50
 */

namespace app\common\model;

use think\Model;

Class Userpermission extends  Model{

    /**
     * @param $data 获取权限
     */
    public static function getList($data)
    {
        $returnArray = [];
        if(is_array($data)){
            $result = self::where($data)->field('permission_id')->select()->toArray();
            if(!empty($result)){
                $permission = array_column($result, 'permission_id');
                $returnArray = $permission;
            }
        }
        return $returnArray;
    }

}