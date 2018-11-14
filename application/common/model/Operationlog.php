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
        self::insert($data);
    }
}