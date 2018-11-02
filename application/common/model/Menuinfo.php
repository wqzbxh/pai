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
        $menuList = self::select()->toArray();
        return $menuList;
    }

    /**
     *
     */
//    子账号获取列表
    public static function sonGetList($userId)
    {
        $allSonmenuRelation = Usermenuinfo::getUsermenuinfoList($userId);
        $menlist = array_map();
        var_dump($menlist);exit;
    }
}