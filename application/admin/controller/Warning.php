<?php
namespace app\admin\controller;

use app\common\model\Error;
use app\common\model\Warningdata;
use think\Controller;
/**
 * Class Warning
 * @package app\admin\controller
 * 预警
 */
Class Warning extends Common{

    /**
     * @return mixe 获取有问题链接结果集
     */
    public function getWarningList()
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

        $result = Warningdata::getContentList($link,$offset,$limit,$startTime,$endTime);
        if($result) {
            return $result;
        }
    }


    /**
     *设为处理
     */
    public function dispose()
    {
        $returnArray =[];
        if(!empty($_POST['id'])){
            $result = Warningdata::chanage($_POST['id']);
            $returnArray = $result;
        }else{
            $returnArray = [
                'code' => 0,
                'msg' => Error::ERRORCODE[0],
                'data' => []
            ];
        }

        return $returnArray;
    }

    /**
     * 执行删除行为
     */
    public function delAction()
    {
        if(!empty($_POST['id'])){
            $result = Warningdata::destroy(array('id'=>$_POST['id']));
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
}
