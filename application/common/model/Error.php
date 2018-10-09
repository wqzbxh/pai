<?php
namespace app\common\model;
use think\Model;
/**
 * Class Errors
 * @package app\common\model
 * @author wanghaiyang
 *
 */
/**
 * Created by PhpStorm.
 * User: wanghaiyang
 * Date: 2018/10/9
 * Time: 10:01
 */
class Error extends Model
{

    /**
     * 错误返回代码
     */
    const ERRORCODE = array(
//        公用错误代码集合
        0 => 'SUCCESS',
        10001 => '查询结果为空',
        10002 => '参数必须为数组',

//        策略错误代码集合
        20001 => '添加产品数据失败！',
        20002 => '产品名称不能为空！',
        20003 => '产品id不能为空！',
        20004 => '没有找到该id下的产品信息！',
        20005 => '没有提交修改参数！',
        20006 => 'id不能为空！',
        20007 => '修改产品失败！',
        20008 => '您的修改无效！',
//        规则错误代码集合
        30001 => '添加参数不能为空',
        30002 => '添加规则数据失败',
        20004 => '没有找到该id下的规则信息！',
        20003 => '规则id不能为空！',
    );
}