<?php
/**
 * Created by PhpStorm.
 * User: k
 * Date: 2018/11/7
 * Time: 17:57
 */
namespace app\admin\controller;

use app\common\model\Error;
use app\common\model\Shortlinkset as ShortlinkModel;
use think\Controller;
use think\Request;

/**
 * Class Shortlinkset
 * @package app\admin\controller
 * 链接检查表
 */
Class Shortlinkset extends Common{

    /**
     * 继承父类自动加载
     */
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
    }

    /**
     * 加载添加link页面
     */
    public function add()
    {
        return $this->view->fetch('add');
    }

    /**
     * 执行添加行为
     */
    public function addAction()
    {
       $returnArray = [];
       $data = Request::instance()->param();
       if(!empty($data['link'])){
           $result =  \app\common\model\Shortlinkset::create($data,'link');
           if($result){
               $returnArray = [
                    'code' => 0,
                   'msg' => Error::ERRORCODE[0],
                   'data' => []
               ];
           }else{
               $returnArray = [
                   'code' => 16001,
                   'msg' => Error::ERRORCODE[16001],
                   'data' => []
               ];
           }
       }else{
           $returnArray = [
               'code' => 10005,
               'msg' => Error::ERRORCODE[10005],
               'data' => []
           ];
       }

       return $returnArray;

    }
    /**
     * 加载修改页面资源
     */
    public function edit()
    {
        if(!empty($_GET['id'])){
            $result = ShortlinkModel::getOne(array('id' => $_GET['id']));
            if($result['code'] == 0){
                $this->assign('info',$result['data']);
                return $this->view->fetch('edit');
            }else{
                return $result;
            }
        }else{
            $returnArray = [
                'code' => 10005,
                'msg' => Error::ERRORCODE[10005],
                'data' => []
            ];
            return $returnArray;
        }

    }


    /**
     * 执行修改行为
     */
    public function editAction()
    {
        $returnArray = [];
        $data = Request::instance()->param();
        if(!empty($data['link'])){
            $result =  \app\common\model\Shortlinkset::update($data,array('id'=>$data['id']),'link');
            if($result){
                $returnArray = [
                    'code' => 0,
                    'msg' => Error::ERRORCODE[0],
                    'data' => []
                ];
            }else{
                $returnArray = [
                    'code' => 16001,
                    'msg' => Error::ERRORCODE[16001],
                    'data' => []
                ];
            }
        }else{
            $returnArray = [
                'code' => 10005,
                'msg' => Error::ERRORCODE[10005],
                'data' => []
            ];
        }

        return $returnArray;

    }
    /**
     * 获取短链列表
     */
    public function getShortlinksetList()
    {
        if(isset($_GET["limit"])){
            $limit = $_GET["limit"];
        }else{
            $limit = 15;
        }
        if(isset($_GET["page"])){
            $offset = ($_GET["page"] -1) * $limit;
        }else{
            $offset = 0;
        }

        if(isset($_GET["link"])){
            $link = $_GET["link"];
        }else{
            $link = '';
        }

        $result = \app\common\model\Shortlinkset::getShortLink($link,$offset,$limit);
        if($result) {
            return $result;
        }
    }

    /**
     * 执行删除行为
     */
    public function delAction()
    {
        if(!empty($_POST['id'])){
            $result = ShortlinkModel::destroy(array('id'=>$_POST['id']));
            if($result){
                $returnArray = [
                    'code' => 0,
                    'msg' => Error::ERRORCODE[0],
                    'data' => []
                ];
            }else{
                $returnArray = [
                    'code' => 16003,
                    'msg' => Error::ERRORCODE[16003],
                    'data' => []
                ];
            }
        }else{
            $returnArray = [
                'code' => 10005,
                'msg' => Error::ERRORCODE[10005],
                'data' => []
            ];
        }

        return $returnArray;
    }

    public function updateLink()
    {
        if(!empty($_POST['serverid'])){
            $serverid = implode(',',$_POST['serverid']);
            $result = ShortlinkModel::update(array('serverid'=>$serverid),'1=1');
            if($result){
                  $serverid = $result->toArray();
                  $serverid = $serverid['serverid']; //服务器id集合
                  $linkResults = ShortlinkModel::getShortLink('',0,0);
                  if($linkResults['code'] == 0){
                      $linkResult = '';
                      foreach ($linkResults['data'] as $link){
                            if(!empty($linkResult)){
                                $linkResult = $linkResult .','.$link['link'];
                            }else{
                                $linkResult = $link['link'];
                            }
                      }
                      //              生成txt文件
                      $myfile = fopen("linkfile/checklink.txt", "w") or die("Unable to open file!");
                      $linkResult = "checkVerList = ".$linkResult."\n";
                      fwrite($myfile, $linkResult);
                      fflush($myfile);
                      $serverid = "serverIdList = ".$serverid;
                      fwrite($myfile, $serverid);
                      fflush($myfile);
                      fclose($myfile);

                      //调接口地址
                      $url = 'http://47.100.222.239:6565/?opc=1';
                      $result = \app\common\controller\Common::otherRequestGet($url);
                      if($result){
                          return $result;
                      }
                  }
            }
        }else{
            $returnArray = [
                'code' => 10005,
                'msg' => Error::ERRORCODE[10005],
                'data' => []
            ];
        }
    }

    public function uodateServerid()
    {
        $resurnArray = [];
        if(!empty($_POST['serverid'])){
            $serverid = implode(',',$_POST['serverid']);
            $result = ShortlinkModel::update(array('serverid'=>$serverid),'1=1');
            if($result){
                $resurnArray = [
                    'code' => 0,
                    'msg' => Error::ERRORCODE[0],
                    'data' => $result
                ];
            }else{
                $resurnArray = [
                    'code' => 16008,
                    'msg' => Error::ERRORCODE[16008],
                    'data' => []
                ];
            }
        }else{
            $result = ShortlinkModel::update(array('serverid'=>''),'1=1');
            $resurnArray = [
                'code' => 0,
                'msg' => Error::ERRORCODE[0],
                'data' => $result
            ];
        }
        return $resurnArray;
    }

}