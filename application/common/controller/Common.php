<?php
namespace app\common\controller;

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



   public static function requestGet($url = '') {
       $curl = curl_init();//初始化
       curl_setopt($curl, CURLOPT_URL, $url); //设置抓取的url
       curl_setopt($curl, CURLOPT_HEADER, 1);  //设置头文件的信息作为数据流输出
       curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); //设置获取的信息以文件流的形式返回，而不是直接输出。
       $data = curl_exec($curl); //执行命令
       curl_close($curl); //关闭URL请求
       return $data;//显示获得的数据
    }
}
