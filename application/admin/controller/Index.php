<?php
namespace app\admin\controller;

use think\Controller;

class Index extends  Common{

    /**
     * 继承父类自动加载
     */
    public function _initialize()
    {
        parent::_initialize();
    }

    public function index()
    {
        return $this->fetch('index');
    }


    /**
     * 白名单
     * @return Request
     */
    public function whiteIndex()
    {
        $serverDataModel = new \app\common\model\Serverdata();
//        获取服务器列表
        $result = $serverDataModel->getServerList('',0,1000,0);
        if($result['code'] == 0){
            $this->assign('serverList',$result['data']);
            $this->assign('serverDefault',$result['data'][0]['id']);
            return $this->fetch('whitelist/index');
        }

    }

    /**
     * 黑名单
     * @return Request
     */
    public function blackIndex()
    {
        $serverDataModel = new \app\common\model\Serverdata();
//        获取服务器列表
        $result = $serverDataModel->getServerList('',0,1000,0);
        if($result['code'] == 0){
            $this->assign('serverList',$result['data']);
            $this->assign('serverDefault',$result['data'][0]['id']);
            return $this->fetch('blacklist/index');
        }

    }





    /**
     * @return mixed
     * 服务器管理首页
     */
    public function serverIndex()
    {
        return $this->fetch('server/index');
    }




    /**
     * @return mixed|string
     * 渲染采集信息首页模块
     * 返回数据库表名 为服务器名
     * 返回默认数据
     */

    public function httpdatacollectServeridIndex()
    {
        $httpDataCollectServerModel = new \app\common\model\HttpdatacollectServerid();
        $result = $httpDataCollectServerModel->getTables();
        if($result['code'] == 0){
            $this->assign('tables',$result['data']);
            $this->assign('defaultTables',key($result['data']));
            return $this->fetch('httpdatacollect_serverid/index');
        }else{
            return '分库中暂无数据表';
        }

    }

    /**
     * @return mixed
     * 渲染策略模式主题下产品管理首页
     */
    public function productIndex()
    {
        return $this->fetch('product/index');
    }


    /**
     * @return mixed
     * 加载推送策略资源
     */

    public function pushpolicyIndex()
    {
        return $this->fetch('pushpolicy/index');
    }



    /**
     * 产品邦定页面
     **/
    public function binding()
    {

        $serverDataModel = new \app\common\model\Serverdata();
        $productDataModel = new \app\common\model\Productdata();
//        获取服务器列表
        $result = $serverDataModel->getServerList('',0,1000,0);
//        获取产品列表（分清与服务器绑定状态）getProductBindingList
        if($result['code'] == 0){
            $this->assign('serverList',$result['data']);
            $this->assign('serverDefault',$result['data'][0]['id']);
            return $this->fetch('product/binding');
        }
    }


    public function test()
    {
    var_dump(APP_PATH);
    }
}
