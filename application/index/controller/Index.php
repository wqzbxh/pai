<?php
namespace app\index\controller;

use app\common\controller\Common;
use app\common\model\Error;
use app\common\model\Menuinfo;
use app\common\model\Productdata;
use app\common\model\User;
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

        $file = 'rulefile/DecryptFile/out_614.xml';
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

//    请求服务器测试代码
//$ch = curl_init();
//$header = ['User-Agent: boss']; //设置一个你的浏览器agent的header
//curl_setopt($ch, CURLOPT_HTTPGET, true); //
//curl_setopt($ch, CURLOPT_URL, 'http://47.106.124.38:9559/9?id=10');
//curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
//curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);   //设置超时时间
//curl_setopt($ch, CURLOPT_TIMEOUT, 15);   //设置超时时间
//$response = curl_exec($ch);
//
//if (curl_errno($ch) != 0) {
//echo curl_error($ch);
//$response = 0;
//}
//curl_close($ch);
//return $response;

    /**
     * 测试
     */
    public function test()
    {
        return $this->fetch('test');
    }

    public function testSon()
    {
        $result = Menuinfo::sonGetList(3);
        if($result['code'] == 0){
            $data = self::arrayPidProcess($result['data']);
            $resultArray = [
                'code' => 0,
                'msg' => Error::ERRORCODE[0],
                'data' => $data
            ];
        }else{
            $resultArray = $result;
        }
       var_dump($resultArray);
    }

    public function arrayPidProcess($data,$res=array(),$pid='0'){
        foreach ($data as $k => $v){
            if($v['father_id']==$pid){
                $res[$v['id']]['info']=$v;
                $res[$v['id']]['child']=self::arrayPidProcess($data,array(),$v['id']);
            }
        }
        return $res;
    }


    public function testBarcode()
    {
        return $this->fetch('barcode');
    }

    public function BarcodeDispose()
    {
//
//        $pagecontent = file_get_contents($_POST['data']);
//        $str =  htmlspecialchars($pagecontent);//htmlentities 或者htmlspecialcharse
//        var_dump($str);exit;
//        $preg = '/<img[^>]*\/>/';
//        preg_match_all($preg, $str, $matches);
//        exit;
        // maximum execution time in seconds
        set_time_limit (24 * 60 * 60);
//if (!isset($_POST['submit'])) die();
// folder to save downloaded files to. must end with slash
        $destination_folder = 'down/';

        $url=$_POST['data'].urlencode(iconv("GB2312","UTF-8","45.doc"));  //英文名字直接写路径就可以了
        echo $url."<br>";
        $newfname = 'text.txt';
        echo $newfname;

        $file = fopen ($url, "rb");

        if ($file) {
            $newf = fopen ($newfname, "wb");
            if ($newf)
                while(!feof($file)) {
                    fwrite($newf, fread($file, 1024 * 8 ), 1024 * 8 );
                }
        }
        if ($file) {
            fclose($file);
        }
        if ($newf) {
            fclose($newf);
        }
    }
}
