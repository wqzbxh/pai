<?php
/**
 * Created by PhpStorm.
 * User: wanghaiyang
 * Date: 2018/11/14
 * Time: 15:46
 */
namespace app\admin\controller;

use app\common\model\Operationlog;
use think\Controller;
use think\Request;

/**
 * Class Operation
 * @package app\admin\controller
 */
Class Operation extends Common
{
    public function getlist()
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

        if(isset($_GET["startTime"])){
            $startTime = $_GET["startTime"];
        }else{
            $startTime = 0;
        }
        if(isset($_GET["endTime"])){
            $endTime = $_GET["endTime"];
        }else{
            $endTime = 0;
        }

        if(isset($_GET["link"])){
            $link = $_GET["link"];
        }else{
            $link = '';
        }

        $result = Operationlog::getOperationlog($link,$offset,$limit,$startTime,$endTime);
        if($result) {
            return $result;
        }
    }
}

