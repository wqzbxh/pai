<?php
namespace app\index\controller;

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
        Userdata::update(array('createtime'=>time()),array('userflag'=>2));
        var_dump(long2ip(1917900968));
    }
}
