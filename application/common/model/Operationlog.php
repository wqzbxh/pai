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
    /**
     * 添加操作行为
     */

    public static function addOperation($user_id)
    {
       // $data['user_id'] = $user_id;
    }
}