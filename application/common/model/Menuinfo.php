<?php
/**
 * Created by PhpStorm.
 * User: wanghaiyang
 * Date: 2018/11/2
 * Time: 10:20
 */

namespace app\common\model;

use think\Model;

/**
 * Class Menuinfo
 * @package app\common\model\
 * @expain 菜单表
 */
class Menuinfo extends Model{

    /**
     * 获取所有菜单表
     */
    public static function getMenuList()
    {
        $menuList = self::where('is_show',1)->order('menu_order','asc')->select()->toArray();
        return $menuList;
    }

    /**
     *
     */
//    子账号获取列表
    public static function sonGetList($userId)
    {
        $resultArray = [];
        $allSonmenuRelation = Usermenuinfo::getUsermenuinfoList($userId);

        if($allSonmenuRelation['code'] == 0){
            $menlist = array_map('array_shift',$allSonmenuRelation['data']);
            $result = self::where(array('id'=>array('in',$menlist)))->where('is_show',1)->select()->toArray();
            $resultArray =[
                'code' => 0,
                'msg' => Error::ERRORCODE[0],
                'data' => $result
            ];
        }else{
            $resultArray = $allSonmenuRelation;
        }

        return $resultArray;
    }
}