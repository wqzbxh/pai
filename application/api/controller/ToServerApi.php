<?php
/**
 * Created by PhpStorm.
 * User: wanghaiYang
 * Date: 2018/10/29
 * Time: 16:34
 */

namespace app\api\controller;

use think\Cache;
use think\Controller;
//给服务器提供的接口
class ToServerApi extends Controller{
    /**
     * 接受服务器状态
     * 服务器调的接口
     * 得到的数据放在缓存里面，
     * 客户端一直请求lookStatus这个方法；然后对不断地去对应的去缓存的东西，当有只的时候返回出去
     */
    public function acceptingState()
    {
        $code = $_REQUEST;
//        var_dump(session('server_code'));
        Cache::set('name',$code,10);
    }

    public function test()
    {
      //  session('server_code',null);
        var_dump(Cache::get('name0'));
    }
}