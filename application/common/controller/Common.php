<?php
namespace app\common\controller;

use app\common\model\Error;
use think\Controller;

//海海自制小工具方法
class Common extends Controller
{
//    根据时间戳获取当天00:00:00的时间戳零点中文时间
    /**
     * @param $time 当天内的任何时间
     * @return false|int
     */
        public function zeroTimestamp($time)
        {
            $chineseHour = date("Y-m-d",$time);
            $wqzbxhResult = strtotime($chineseHour);
            return $wqzbxhResult;
        }

    /**
     * 数字转换为中文
     * @param  integer  $num  目标数字
     */
    public function numberToChinese($num)
    {
        if (is_int($num) && $num < 1000) {
            $char = array('零', '一', '二', '三', '四', '五', '六', '七', '八', '九');
            $unit = ['', '十', '百', '千', '万'];
            $return = '';
            if ($num < 10) {
                $return = $char[$num];
            } elseif ($num%10 == 0) {
                $firstNum = substr($num, 0, 1);
                if ($num != 10) $return .= $char[$firstNum];
                $return .= $unit[strlen($num) - 1];
            } elseif ($num < 20) {
                $return = $unit[substr($num, 0, -1)]. $char[substr($num, -1)];
            } else {
                $numData = str_split($num);
                $numLength = count($numData) - 1;
                foreach ($numData as $k => $v) {
                    if ($k == $numLength) continue;
                    $return .= $char[$v];
                    if ($v != 0) $return .= $unit[$numLength - $k];
                }
                $return .= $char[substr($num, -1)];
            }
            return $return;
        }
    }


    /**
     * 针对开启 停止操作
     * @param string $url 向服务器发出信号 get
     *
     * @return mixed
     */
   public static function requestGet($url = '') {
       $ch = curl_init();
       $header = ['User-Agent: boss']; //设置一个你的浏览器agent的header
       curl_setopt($ch, CURLOPT_HTTPGET, true); //
       curl_setopt($ch, CURLOPT_URL, $url);
       curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
       curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);   //设置等待时间
       curl_setopt($ch, CURLOPT_TIMEOUT, 5);   //设置超时时间
       $response = curl_exec($ch);

       if (curl_errno($ch) != 0) {
           $response = curl_error($ch);
       }

       curl_close($ch);
       return $response;
    }


    /**
     * @param string $url
     * @param 其他请求操作
     * @return mixed|string
     */
    public static function otherRequestGet($url = '') {
        $ch = curl_init();
        $header = ['User-Agent: boss']; //设置一个你的浏览器agent的header
        curl_setopt($ch, CURLOPT_HTTPGET, true); //
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);   //设置等待时间
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);   //设置超时时间
        $response = curl_exec($ch);

        if (curl_errno($ch) != 0) {
            $response = curl_error($ch);
        }

        curl_close($ch);
        return $response;
    }
    /**
     * 指定目录下载文件
     * @param $path 文件路径
     * @param $file 文件名称
     * @param string $zipname 下载以后的压缩包名字
     * @return array
     */
    public function downLoadFile()
    {
        if(!empty($_GET['path']) && !empty($_GET['file']) && !empty($_GET['zipname']) ){
            $file = $_GET['path'].$_GET['file'];
            if(is_file($file)){
                $length = filesize($file);
               // $type = mime_content_type($file);
                $showname =  ltrim(strrchr($file,'/'),'/');
                header("Content-Description: File Transfer");
               // header('Content-type: ' . $type);
                header('Content-Length:' . $length);
                if (preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT'])) { //for IE
                    header('Content-Disposition: attachment; filename="' . rawurlencode($showname) . '"');
                } else {
                    header('Content-Disposition: attachment; filename="' . $showname . '"');
                }
                readfile($file);
            } else {
                $returnArray =[
                    'code' => 14001,
                    'msg' => Error::ERRORCODE[14001],
                    'data' => []
                ];
            }
        }else{
            $returnArray =[
                'code' => 14002,
                'msg' => Error::ERRORCODE[14002],
                'data' => []
            ];
        }
        return;
    }



    /**
     * 查看二级分类
     */
    public function arrayPidProcess($data,$res=array(),$pid='0'){
        foreach ($data as $k => $v){
            if($v['father_id']==$pid){
                $res[$v['id']]['info']=$v;
                $res[$v['id']]['child']=self::arrayPidProcess($data,array(),$v['id']);
            }
        }
        return $res;
    }
}
