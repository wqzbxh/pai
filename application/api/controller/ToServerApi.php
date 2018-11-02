<?php
/**
 * Created by PhpStorm.
 * User: wanghaiYang
 * Date: 2018/10/29
 * Time: 16:34
 */

namespace app\api\controller;

use app\common\model\Error;
use app\common\model\Serverdata;
use think\Cache;
use think\Controller;
//给服务器提供的接口
class ToServerApi extends Controller{



    // 操作码定义
   const OP = [
               'UNKNOW = 0' => '未知操作码 UNKNOW = 0',
               'UPDATE_RULE' => '/更新规则文件 UPDATE_RULE',		//
               'RESTART' => '重启程序 RESTART ',			//
               'STATUS' => '上报程序状态 STATUS',			//
               'START_PROC' => '启动程序 START_PROC ',			//
               'STOP_PROC' => '停止程序 STOP_PROC',		//
               'UPDATE_RESOURCE' => '更新资源文件UPDATE_RESOURCE',   //
               'UPLOAD_RESOURCE' => '下载XML文件 UPLOAD_RESOURCE',  //
               'UPLOAD_SHELL' => '下载shell文件 UPLOAD_SHELL',   //
               'UPDATE_RULE_FILE' => '更新规则文件:wget下载文件的方式 UPDATE_RULE_FILE', 	//
               'UPDATE_RADIUS = 101' => '解析Radius协议UPDATE_RADIUS = 101',	//
   ];

    // 返回码定义
   const RT = [
       'UNKNOW_OPRATE = 0' => '未知错误码',            //
       'OPERATION_SUCCESS' => '操作成功',            // 操作成功
       'START_FAILED' => '开启失败',                 // 开启失败
       'STOP_FAILED' => '停止失败',                  // 停止失败
       'RESTART_FAILED' => '重启失败',               // 重启失败
       'GET_RULE_FAILED' => '下发规则失败',              // 下发规则失败
       'UPDATE_RULE_FAILED' => '更新规则失败',           // 更新规则失败
       'EXECUTE_FAILED' => '命令执行失败',               // 命令执行失败
       'CHECK_STATU_FAILED' => '检查状态失败',           // 检查状态失败
       'RULE_FILE_ERROR' => '收到的规则文件错误',              // 收到的规则文件错误
       'SHELL_FILE_ERROR' => '收到的shell脚本错误',             // 收到的shell脚本错误
       'INIT_DECRYPTION_ERROR' => '初始化shell解密器失败',        // 初始化shell解密器失败
       'DECRYPTION_SHELL_ERROR' => '解密shell失败',       // 解密shell失败
       'WRITE_SHELL_ERROR' => '将shell明文写到文件失败',            // 将shell明文写到文件失败
       'EXECUTE_SHELL_ERROR' => '执行shell失败',          // 执行shell失败
       'GET_SHELL_FAILED' => '请求shell文件失败',		      // 请求shell文件失败
       'EXECUTE_COMMAND_FAILED = 255'  => '执行系统命令失败' // 执行系统命令失败
   ];


    /**
     * 接受服务器状态
     * 服务器调的接口
     * 得到的数据放在缓存里面，
     * 客户端一直请求lookStatus这个方法；然后对不断地去对应的去缓存的东西，当有只的时候返回出去
     * 47.100.226.65/index.php/Rule/Configuration/move?id=12&flag=0&op=1&rt=OPERATION_SUCCESS
     *
     */
    public function acceptingState()
    {
        $where = [];
        $data = [];
        $returnArray = [];
        if(isset($_GET['id'])){
            $where['id'] = $_GET['id'];
        }
        if(isset($_GET['flag'])){
            $data['serverstatus'] = $_GET['flag'];
        }
        if(isset($_GET['op'])){
            $op = $_GET['op'];
        }
        if(isset($_GET['rt'])){
            $rt= $_GET['rt'];
        }

        $data['updatetime'] = time();

        if(!empty($rt) && !empty($where)){
            Cache::set('code'.$where['id'] ,$rt,3000);
            Serverdata::update($data,$where);
            $result = Cache::get('code'.$where['id']);
            if(!empty($result)){
                $returnArray = [
                    'code' => 0,
                    'msg' => Error::ERRORCODE[0],
                    'data' => $result
                ];
            }else{
                $returnArray = [
                    'code' => 40011,
                    'msg' => Error::ERRORCODE[40011],
                    'data' => array()
                ];
            }
        }else{
            $returnArray = [
                'code' => 40010,
                'msg' => Error::ERRORCODE[40010],
                'data' => array()
            ];
        }

        return json_encode($returnArray);

    }

    public function test()
    {
      //  session('server_code',null);
        var_dump(Cache::get('name'));
    }



//心跳消息一分钟请求一次 HeartBeat  	GET方式
    public function heartBeat()
    {
        $where = [];
        $data = [];
        $returnArray = [];
        if(isset($_GET['id'])){
            $where['id'] = $_GET['id'];
        }
        if(isset($_GET['p'])){
            $data['serverip'] = $_GET['p'];
        }
        if(isset($_GET['flag'])){
            $data['serverstatus'] = $_GET['flag'];
        }
        $data['updatetime'] = time();
        if(!empty($where) && !empty($data)){
            $result =  Serverdata::update($data,$where);
            if(!empty($result)){
                $returnArray = [
                    'code' => 0,
                    'msg' => Error::ERRORCODE[0],
                    'data' => $where
                ];
            }else{
                $returnArray = [
                    'code' => 40011,
                    'msg' => Error::ERRORCODE[40011],
                    'data' => $where
                ];
            }
        }else{
            $returnArray = [
                'code' => 40010,
                'msg' => Error::ERRORCODE[40010],
                'data' => $where
            ];
        }

        return json_encode($returnArray);
    }
    //

    /**
     * @expain 下载XML方式
     * 接受post数据;服务器id
     * 将如数据写入XML文件里面放在rulefie文件里面 命名方式 如 out_1
     * 切换的该目录执行defile…… C++程序 进行解密 放在指定目录
     * 进行下载操作
     */
    public function downLoadXml()
    {
        $returnApiResult = [];
        if(!empty($_POST['data'] && !empty($_POST['id']))){//接收post数据
            $downXml = fopen('rulefile/out_'.$_POST['id'].'.xml', 'w+');//将数据写入rulefile里面 命名方式
            fwrite($downXml, $_POST['data']);
            fclose($downXml);
            $shellCommand = 'cd rulefile;./decryptRule '.$_POST['id']; //执行解密文件 DecryptFile
            system($shellCommand,$shellResult);
            $shellResult = 0;
            if($shellResult == 0){ //已经执行成功生成解密文件 ，进行监听
                Cache::set('code'.$_POST['id'] ,'7',3000);
                $returnApiResult = [
                    'code' => 0,
                    'msg' => Error::ERRORCODE[0],
                    'data' => []
                ];
            }else{
                $returnApiResult = [
                    'code' => 12002,
                    'msg' => Error::ERRORCODE[12002],
                    'data' => $shellResult
                ];
            }
        }else{
            $returnApiResult = [
                'code' => 10005,
                'msg' => Error::ERRORCODE[10005],
                'data' => []
            ];
        }
        return json_encode($returnApiResult);
    }

}