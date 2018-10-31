<?php
namespace app\index\controller;

use app\common\controller\Common;
use app\common\model\Userdata;
use think\Controller;

class Index extends Controller
{
    public function login()
    {
        session('userInfo',null);
        return $this->fetch('login');
    }


    public function index()
    {

        $file = 'rulefile';
        $mark = 'a';
        if(is_file($file)){
            $zip = new \ZipArchive();
            $filename = $mark . ".zip";
            $zip->open($filename, \ZipArchive::CREATE);   //打开压缩包
            $zip->addFile($file, basename($file));   //向压缩包中添加文件
            $zip->close();  //关闭压缩包
            //输出压缩文件提供下载
            header("Cache-Control: max-age=0");
            header("Content-Description: File Transfer");
            header('Content-disposition: attachment; filename=' . basename($filename)); // 文件名
            header("Content-Type: application/zip"); // zip格式的
            header("Content-Transfer-Encoding: binary"); //二进制
            header('Content-Length: ' . filesize($filename)); //
            @readfile($filename);//输出文件;
            unlink($filename); //删除压缩包临时文件
        } else {
            $this->error('源文件不存在！');
        }

    }

    //测试
    public function test()
    {
        $url = 'http://139.196.91.198:9559/?id=10';
     //   $user_agent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.2; .NET CLR 1.1.4322)";

        // 初始化一个 cURL 对象
//        $curl = curl_init();
//        $curl = curl_init(); // 启动一个CURL会话
//        curl_setopt($curl, CURLOPT_URL, $url);
//        curl_setopt($curl, CURLOPT_HEADER, 0);
//        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
//        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);  // 从证书中检查SSL加密算法是否存在
//        $tmpInfo = curl_exec($curl);
//        var_dump(curl_error($curl)); //返回api的json对象
//        //关闭URL请求
//        curl_close($curl);
        // 显示获得的数据
//        print_r($tmpInfo);


        $fp = fsockopen('139.196.91.198', 9559, $ercode, $ermsg);
        if($ercode !== 0)
            exit ('error:'. $ermsg);
        $st = sprintf("%b", 00100000);
        fread($fp, $st);


    }
}
