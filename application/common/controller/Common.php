<?php
namespace app\common\controller;

use think\Controller;

//自制小工具方法
class Common extends Controller
{
//    根据时间戳获取当天00:00:00的时间戳零点中文时间
        public function zeroTimestamp($time)
        {
            $chineseHour = date("Y-m-d",$time);
            $wqzbxhResult = strtotime($chineseHour);
            return $wqzbxhResult;
        }
}
