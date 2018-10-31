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
    public function test21()
    {
        $url = 'http://139.196.91.198:/?id=10';
//         初始化一个 cURL 对象
        header('content-type:application:json;charset=utf8');
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:GET');
        header('Access-Control-Allow-Headers:x-requested-with,content-type');

        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);  // 从证书中检查SSL加密算法是否存在
        $tmpInfo = curl_exec($curl);
        var_dump(curl_error($curl));
        curl_close($curl);

        print_r($tmpInfo);




    }


    function test() {
        $url = 'http://139.196.91.198:9559/9?id=10';
        $host = parse_url($url,PHP_URL_HOST);
        $port = parse_url($url,PHP_URL_PORT);
        $port = $port ? $port : 9559;
        $scheme = parse_url($url,PHP_URL_SCHEME);
        $path = parse_url($url,PHP_URL_PATH);
        $query = parse_url($url,PHP_URL_QUERY);

        if($query) $path .= '?'.$query;
        if($scheme == 'https') {
            $host = 'ssl://'.$host;
        }

        $fp = fsockopen($host,$port,$error_code,$error_msg,1);
        var_dump($fp);exit;
        if(!$fp) {
            return array('error_code' => $error_code,'error_msg' => $error_msg);
        }
        else {
            stream_set_blocking($fp,true);//开启了手册上说的非阻塞模式
            stream_set_timeout($fp,1);//设置超时
            $header = "GET $path HTTP/1.1\r\n";
            $header.="Host: $host\r\n";
            $header.="Connection: close\r\n\r\n";//长连接关闭
            fwrite($fp, $header);
            usleep(1000); // 这一句也是关键，如果没有这延时，可能在nginx服务器上就无法执行成功
            fclose($fp);
            var_dump($fp);exit;
            return array('error_code' => 0);
        }
    }


}
