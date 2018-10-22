<?php
/**
 * Created by PhpStorm.
 * User: wanghaiyang
 * Date: 2018/10/10
 * Time: 11:15
 */
namespace app\common\model;

use think\Model;

Class Serverdata extends Model{

    /**
     * 添加数据
     * @param data 添加数组参数
     */
    public function addServer($data)
    {
        $errorModel = new \app\common\model\Error();
        $returnArray = array();
        if(is_array($data)){
            $checkResult = self::checkServer($data['servername']);
            if($checkResult > 0){
                $returnArray = array(
                    'code' => 40005,
                    'msg' => $errorModel::ERRORCODE[40005],
                    'data' => array()
                );
            }else{
                $result = self::insert($data);
                if($result == 1){
                    $returnArray = array(
                        'code' => 0,
                        'msg' => $errorModel::ERRORCODE[0],
                        'data' => $result
                    );
                }else{
                    $returnArray = array(
                        'code' => 40002,
                        'msg' => $errorModel::ERRORCODE[40002],
                        'data' => array()
                    );
                }
            }

        }else{
            $returnArray = array(
                'code' => 10002,
                'msg' => $errorModel::ERRORCODE[10002],
                'data' => array()
            );
        }
        return $returnArray;
    }



    /**校验重复的名称
     * @param $data
     */
    public function checkServer($name,$id = 0)
    {
        if($id == 0){
//        对新增数据进行名称查重 返回0/1
            $result = self::where(array('servername'=>$name))->count();
        }else{
//            对修改数据进行查重
            $result = self::where(array('servername'=>$name))->select()->toArray();

            if($result){
                if($result[0]['id'] == $id){
                    $result = 0 ;
                }else{
                    $result = 1;
                }
            }else{
                $result = 0;
            }
        }
        return $result;
    }

    /**
     * 查询产品方法
     * @param $serveruserid 用户ID 前期默认为0；
     * @param $limit 限制多少条
     * @param $offset 从低第几条调开始
     * @param string $servername 产品名称
     */


    public function getServerList($servername = '',$offset,$limit,$serveruserid)
    {
        $criteria = array();
        $returnArray = array();
        $errorModel = new \app\common\model\Error();
        $criteria['is_del'] = 0;
        $criteria['serveruserid'] = $serveruserid;
        $result = self::where($criteria)
            ->limit($offset,$limit)
            ->select()
            ->toArray();

        $count = self::where($criteria)
            ->count();

        if(!empty($result)){
            $returnArray = array(
                'code' => 0,
                'msg' => $errorModel::ERRORCODE[0],
                'count' =>$count,
                'data' => $result
            );
        }else{
            $returnArray = array(
                'code' => 10001,
                'msg' => $errorModel::ERRORCODE[10001],
                'data' => $result
            );
        }
        return $returnArray;
    }

    /**
     * 获取单个产品信息
     * @param id 产品的自增ID
     */
    public function getServerOne($id)
    {
        $errorModel = new \app\common\model\Error();
        $returnArray = array();
        $result = self::where(array('id' => $id))->order('id desc')->select()->toArray();
        if(!empty($result)){
            $returnArray = array(
                'code' => 0,
                'msg' => $errorModel::ERRORCODE[0],
                'data' => $result,
            );
        }else{
            $returnArray = array(
                'code' => 20004,
                'msg' => $errorModel::ERRORCODE[20004],
                'data' => array(),
            );
        }
        return $returnArray;
    }


    /**
     * 修改产品信息
     * @param Data 修改的数据集合 注释：data中必须含有产品的id
     *
     */
    public function updateServer($data)
    {
        $errorModel = new \app\common\model\Error();
        $returnArray = array();
        if(!empty($data['id'])){
            $checkResult = self::checkServer($data['servername'],$data['id']);
            if($checkResult > 0){
                $returnArray = array(
                    'code' => 40005,
                    'msg' => $errorModel::ERRORCODE[40005],
                    'data' => array()
                );
            }else{
                $result = self::where('id', $data['id'])->update($data);
                if($result == 1){
                    $returnArray = array(
                        'code' => 0,
                        'msg' => $errorModel::ERRORCODE[0],
                        'data' => $result,
                    );
                }else{
                    $returnArray = array(
                        'code' => 20008,
                        'msg' => $errorModel::ERRORCODE[20008],
                        'data' => $result,
                    );
                }
            }
        }else{
            $returnArray = array(
                'code' => 20006,
                'msg' => $errorModel::ERRORCODE[20006],
                'data' => array(),
            );
        }
        return $returnArray;
    }

    /**
     *
     */


    /**
     * 删除产品操作
     * @param id 服务器的自增ID
     * Tue Oct 09 2018 15:10:18 GMT+0800 (中国标准时间)
     */
    public function delServerData($id)
    {
        $errorModel = new \app\common\model\Error();
        $returnArray = array();
        if(!empty($id)){

            $result = self::where('id', $id)->delete();
//            删除与之相关绑定的记录
//            删除产品与服务器之间的绑定
            $SreverProductdataModel = new \app\common\model\Serverproductdata();
            $SreverProductdataModel->delBindingRecord(array('serverid' => $id));
//             删除规则与服务器之间的绑定
            $serverRuletModel = new \app\common\model\Serverruledata();
            $serverRuletModel->unbundle(array('serverid' => $id));
//              删除子规则与服务器之间的绑定
            $serverChildruletModel = new \app\common\model\Serverchildruledata();
            $serverChildruletModel->delListRule($id,0,0);
//              删除白名单
            $ipwhiteModel = new \app\common\model\Ipwhitelist();
            $ipwhiteModel->delRecode(array('serverid' => $id));
//              删除黑名单
            $ipblackModel = new \app\common\model\Ipblacklist();
            $ipblackModel->delRecode(array('serverid' => $id));
//              删除分库表
            $tablename = 'httpdatacollect_'.$id;
            $httpDatacollectkModel = new \app\common\model\HttpdatacollectServerid();
            $tablename = $httpDatacollectkModel->delTable($tablename);

            if($result == 1){
                $returnArray = array(
                    'code' => 0,
                    'msg' => $errorModel::ERRORCODE[0],
                    'data' => $result,
                );
            }else{
                $returnArray = array(
                    'code' => 40004,
                    'msg' => $errorModel::ERRORCODE[40004],
                    'data' => $result,
                );
            }
        }else{
            $returnArray = array(
                'code' => 40003,
                'msg' => $errorModel::ERRORCODE[40003],
                'data' => array(),
            );
        }
        return $returnArray;
    }

//    生成XML文件
    public static function RuleXml($data)
    {
        $serverid = $data['id'];//服务器id
        $userid = session('userInfo')['id'];//用户id

//        XML表头
        $doc = new \DOMDocument('1.0','utf-8');
        $doc->formatOutput = true;//格式化输出格式
        $doc->xmlStandalone = true;//格式化输出格式

        $flowRuleConvert = $doc->createElement('FlowRuleConvert');//创建一个节点
        $flowRuleConvert->setAttribute("version","1.0");//设置版本属性
        $flowRuleConvert->setAttribute("reset",$serverid);//设置服务器id
        $flowRuleConvert->setAttribute("userid",$userid);//设置用户ID


//        -收发网卡列表XML
        $NetworkCard = $doc->createElement('NetworkCard');
        $serverData['ReceveCard'] = $data['inputcard'];//数据接收网卡
        $serverData['SendCard'] = $data['outcard'];//数据接收网卡
        $serverData['DataCenter'] = $data['datacenter'];//数据接收网卡
        $serverData['MAC'] = $data['macaddress'];//数据接收网卡

        foreach ($serverData as $key => $value){
            $networkResultLabel =$doc->createElement("Device");
            $networkResultLabel->setAttribute($key,$value);
            $NetworkCard->appendChild($networkResultLabel);
        }



        //源IP用户白名单列表 + Radius用户白名单列表
        $srcIPWhiteList = $doc->createElement("SrcIPWhiteList");
        $radiusList = $doc->createElement("RadiusList");
        $srcIPWhiteListResult = Ipwhitelist::getListUpgrade(0,0,9,$serverid);

        if($srcIPWhiteListResult['code'] == 0 && count($srcIPWhiteListResult['data']) > 0){
            foreach ($srcIPWhiteListResult['data'] as $srcIPWhiteListvalue){
                if($srcIPWhiteListvalue['iptype'] == 1){//源IP用户白名单列表
                    $srcIPWhiteListLabel = $doc->createElement("IP");
                    $srcIPWhiteListLabel->setAttribute("address",$srcIPWhiteListvalue['content']);
                    $srcIPWhiteListLabel->setAttribute("format",$srcIPWhiteListvalue['format']);
                    $srcIPWhiteList->appendChild($srcIPWhiteListLabel);
                }else if ($srcIPWhiteListvalue['iptype'] == 0){//Radius用户白名单列表
                    $radiusListLabel = $doc->createElement("Radius");
                    $radiusListLabel->setAttribute("Account",$srcIPWhiteListvalue['content']);
                    $radiusList->appendChild($radiusListLabel);
                }
            }
        }


        //源IP用户黑名单列表 + Radius用户黑名单列表
        $srcIPBlackList = $doc->createElement("SrcIPBlackList");
        $radiusBlackList = $doc->createElement("RadiusBlackList");
        $srcIPBlackListResult = Ipblacklist::getListUpgrade(0,0,9,$serverid);

        if($srcIPBlackListResult['code'] == 0 && count($srcIPBlackListResult['data']) > 0){
            foreach ($srcIPBlackListResult['data'] as $srcIPWhiteListvalue){
                if($srcIPWhiteListvalue['iptype'] == 1){//源IP用户白名单列表
                    $srcIPBlackListLabel = $doc->createElement("IP");
                    $srcIPBlackListLabel->setAttribute("address",$srcIPWhiteListvalue['content']);
                    $srcIPBlackListLabel->setAttribute("format",$srcIPWhiteListvalue['format']);
                    $srcIPBlackList->appendChild($srcIPBlackListLabel);
                }else if ($srcIPWhiteListvalue['iptype'] == 0){//Radius用户白名单列表
                    $radiusBlackLisLabel = $doc->createElement("Radius");
                    $radiusBlackLisLabel->setAttribute("Account",$srcIPWhiteListvalue['content']);
                    $radiusBlackList->appendChild($radiusBlackLisLabel);
                }
            }
        }

        //域名规则
        $hostRuleList = $doc->createElement("HostRuleList");
        $generalRuleList = $doc->createElement("GeneralRuleList");
        $ruleAllData = Serverchildruledata::ruleXmlsData($serverid);
        if(count($ruleAllData) > 0){
            foreach ($ruleAllData as $ruleAllDataValue){
                if($ruleAllDataValue['product_type'] == 1){//基本类型
                    $hostLabel = $doc ->createElement('HOST');
                    if(!empty($ruleAllDataValue['rule_host'])){
                        $hostLabel->setAttribute("domain",$ruleAllDataValue['rule_host']);
                    }
                    if(!empty($ruleAllDataValue['rule_exhost'])){
                        $hostLabel->setAttribute("HostFilter",$ruleAllDataValue['rule_exhost']);
                    }
                    $ruleLabel = $doc ->createElement('Rule');
                    $ruleLabel->setAttribute("id",$ruleAllDataValue['id']);
                    $ruleLabel->setAttribute("productid",$ruleAllDataValue['productid']);
                    $ruleLabel->setAttribute("ruleid",$ruleAllDataValue['ruleid']);
                    $ruleLabel->setAttribute("type",$ruleAllDataValue['childrule_type']);
                    $ruleLabel->setAttribute("ratio",$ruleAllDataValue['childrule_ratio']);
                    $ruleLabel->setAttribute("combine",$ruleAllDataValue['childrule_match_type']);

                    $ieLabelArray = array();
                    $ieLabel = $doc ->createElement('IE');
                    $ieLabelArray['Exclude'] = self::compare($ruleAllDataValue['rule_exuri'],$ruleAllDataValue['childrule_exuri']);
                    $ieLabelArray['UaFilter'] = self::compare($ruleAllDataValue['rule_exua'],$ruleAllDataValue['childrule_exua']);
                    $ieLabelArray['UaWholeFilter'] = self::compare($ruleAllDataValue['rule_precise_exua'],$ruleAllDataValue['childrule_precise_exua']);
                    $ieLabelArray['CookieFilter'] = self::compare($ruleAllDataValue['rule_excookie'],$ruleAllDataValue['childrule_excookie']);

                    
                    var_dump($ieLabelArray);exit;

                    $ruleLabel->appendChild($ieLabel);
                    $hostLabel->appendChild($ruleLabel);
                    $generalRuleList->appendChild($hostLabel);
                }elseif ($ruleAllDataValue['product_type'] == 0){//通匹类型
                    if($ruleAllDataValue['match_type'] == 0){//APK

                    }elseif ($ruleAllDataValue['match_type'] == 1){//EXE

                    }
                }
            }
        }


        $flowRuleConvert  -> appendChild($NetworkCard);
        $flowRuleConvert  -> appendChild($srcIPWhiteList);
        $flowRuleConvert  -> appendChild($srcIPBlackList);
        $flowRuleConvert  -> appendChild($radiusList);
        $flowRuleConvert  -> appendChild($radiusBlackList);
        $flowRuleConvert  -> appendChild($hostRuleList);
        $flowRuleConvert  -> appendChild($generalRuleList);
        $doc->appendChild($flowRuleConvert);
        $doc->save("rule_".$serverid.".xml");
    }

    /**
     * 比较两个值，相等
     * @param $parameter1参数1
     * @param $parameter2
     */
    public static function compare($parameter1,$parameter2)
    {
        if(!empty($parameter1) && !empty($parameter1) && $parameter1 !== $parameter2){
            $result = $parameter1."$".$parameter2;
        }elseif(empty($parameter1) &&  !empty($parameter2)){
            $result = $parameter2;
        }elseif(empty($parameter2) &&  !empty($parameter1)){
            $result = $parameter1;
        }elseif(!empty($parameter1) && !empty($parameter1) && $parameter1 === $parameter2){
            $result = $parameter1;
        }else{
            $result = '';
        }
        return $result;
    }
}