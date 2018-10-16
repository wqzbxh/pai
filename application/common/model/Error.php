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
        10003 => '账号密码错误！',
        10004 => '账号密码不能为空！',
        10005 => '参数必须存在！',

//        策略错误代码集合
        20001 => '添加产品数据失败！',
        20002 => '产品名称不能为空！',
        20003 => '产品id不能为空！',
        20004 => '没有找到该id下的产品信息！',
        20005 => '没有提交修改参数！',
        20006 => 'id不能为空！',
        20007 => '修改产品失败！',
        20008 => '您的修改无效！',
        20009 => '删除失败！',
        20010 => '该产品名称已经存在！',
        20011 => '未删除任何规则！',
//        规则错误代码集合
        30001 => '添加参数不能为空',
        30002 => '添加规则数据失败',
        30004 => '没有找到该id下的规则信息！',
        30003 => '规则id不能为空！',
        30005 => '规则名字已存在!',
        30006 => '子规则名字已存在!',
        30007 => '未删除任何子规则！',
//        服务器管理错误代码集合

        40001 => '服务器配置参数不能为空',
        40002 => '添加服务器配置数据失败',
        40003 => '没有找到该id下的服务器信息',
        40003 => '服务器id不能为空',
        40004 => '删除服务器失败！修改无效！',
        40005 => '服务器名称已经存在！',

//        邦定服务器产品错误提示
        50001 => '添加邦定记录失败！',
        50002 => '添加绑定参数无效！',
        50003 => '绑定ID不能为空！',
        50004 => '绑定的产品id不能为空！',
        50005 => '绑定的服务器id不能为空！',
        50006 => '添加邦定规则记录失败！',
        50007 => '解绑记录失败！',
        50008 => '解绑ID不能为空！',
        50009 => '绑定的规则id不能为空！',
        50010 => '解绑ID不能为空！',
        50011 => '批量添加绑定自规则失败！',
        50012 => '绑定规则失败！',
        50013 => '子定规则推文不能为空！',
        50014 => '子定规则URl不能为空！',
        50015 => '子定规则ID不能为空！',
        50016 => '更新子规则绑定内容失败！',
        50017 => '已添加之间的绑定记录，请刷新查看！',
//        黑名单
        60001 => '添加黑名单记录失败！',
        60002 => '黑名单记录获取有误！',
        60003 => '无效的修改！[黑名单记录]',
        60005 => '无效的删除！[黑名单记录]',
        60006 => '添加记录失败！[白名单记录]',
        60007 => '记录获取有误！[白名单记录]',
        60008 => '无效的修改！[白名单记录]',
        60009 => '无效的删除！[白名单记录]',
//        服务器统计报错代码
        70001 => '查询结果为空！',
    );
}