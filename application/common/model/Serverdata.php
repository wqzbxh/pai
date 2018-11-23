<?php
/**
 * Created by PhpStorm.
 * User: wanghaiyang
 * Date: 2018/10/10
 * Time: 11:15
 */
namespace app\common\model;

use app\common\controller\Common;
use think\Cache;
use think\Config;
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

//        检查服务器的状态
        self::checkServerStatus();

        $criteria = array();
        $returnArray = array();
        $errorModel = new \app\common\model\Error();
        $criteria['is_del'] = 0;
        $userdata = Userdata::getOne(array('id'=> $serveruserid));

        $userflag = $userdata['data']['userflag'];

        if($serveruserid != 1 && $userflag != 1){
            $criteria['serveruserid'] = $serveruserid;
        }
        if(!empty($servername)){
            $result = self::where($criteria)
                ->limit($offset,$limit)
                ->where('servername','like','%'.$servername.'%')
                ->select()
                ->toArray();

            $count = self::where($criteria)
                ->where('servername','like','%'.$servername.'%')
                ->count();
        }else{
            $result = self::where($criteria)
                ->limit($offset,$limit)
                ->select()
                ->toArray();

            $count = self::where($criteria)
                ->count();
        }


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
        self::checkServerStatus($id);
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
//        -收发网卡列表XML 服务器
        $NetworkCard = $doc->createElement('NetworkCard');
        $serverData['ReceveCard'] = $data['inputcard'];//数据接收网卡
        $serverData['SendCard'] = $data['outcard'];//数据接收网卡
        $serverData['IpIpTunnel'] = $data['ipiptunnel'];//数据接收网卡
        $serverData['SrcMAC'] = $data['srcmacaddress'];//数据接收网卡
        $serverData['MAC'] = $data['macaddress'];//数据接收网卡
        $serverData['SrcIp'] = $data['srcip'];//数据接收网卡
        $serverData['DstIp'] = $data['dstip'];//数据接收网卡
        $serverData['DataCenter'] = $data['datacenter'];//数据接收网卡
        if($data['hostcollect'] != 0){
            $serverData['HostCollect'] = $data['hostcollect'];//开启Host统计（0否1是）
            $serverData['CollectType'] = $data['collecttype'];//Host统计方式(1表示JS统计 / 2表示微信流量统计 / 3表示未命中APK统计)
        }

        $serverData = array_filter($serverData);
        if(count($serverData) > 0){
            foreach ($serverData as $key => $value){
                $networkResultLabel =$doc->createElement("Device");
                $networkResultLabel->setAttribute($key,$value);
                $NetworkCard->appendChild($networkResultLabel);
            }

            $flowRuleConvert  -> appendChild($NetworkCard);
        }

        /*策略组*/
        $pushpolicyData = Pushpolicy::getTactics('','seq,time',0,0);
        if($pushpolicyData['code'] == 0){
            $userPushTimePolicyLabel = $doc->createElement("UserPushTimePolicy");
            if(count($pushpolicyData['data']) > 0 ){
                foreach ($pushpolicyData['data'] as $key=>$value){
                    $groupLabel =$doc->createElement("Group");
                    $groupLabel->setAttribute('Seq',$value['seq']);
                    $groupLabel->setAttribute('LimitTime',$value['time']);
                    $userPushTimePolicyLabel->appendChild($groupLabel);
                }
            }
            $flowRuleConvert  -> appendChild($userPushTimePolicyLabel);
        }


        //源IP用户白名单列表 + Radius用户白名单列表
        $srcIPWhiteList = $doc->createElement("SrcIPWhiteList");
        $radiusList = $doc->createElement("RadiusList");
        $srcIPWhiteListResult = Ipwhitelist::getListUpgrade(0,0,9,$serverid);

        if($srcIPWhiteListResult['code'] == 0 && count($srcIPWhiteListResult['data']) > 0){
            $wr = 0;
            $wl = 0;
            foreach ($srcIPWhiteListResult['data'] as $srcIPWhiteListvalue){
                if($srcIPWhiteListvalue['iptype'] == 1){//源IP用户白名单列表
                    $wl++;
                    $srcIPWhiteListLabel = $doc->createElement("IP");
                    $srcIPWhiteListLabel->setAttribute("address",$srcIPWhiteListvalue['content']);
                    $srcIPWhiteListLabel->setAttribute("format",$srcIPWhiteListvalue['format']);
                    $srcIPWhiteList->appendChild($srcIPWhiteListLabel);
                }else if ($srcIPWhiteListvalue['iptype'] == 0){//Radius用户白名单列表
                    $wr++;
                    $radiusListLabel = $doc->createElement("Radius");
                    $radiusListLabel->setAttribute("Account",$srcIPWhiteListvalue['content']);
                    $radiusList->appendChild($radiusListLabel);
                }
            }
            if($wl>0){
                $flowRuleConvert  -> appendChild($srcIPWhiteList);
            }
            if($wr>0){
                $flowRuleConvert  -> appendChild($radiusList);
            }
          }
        //源IP用户黑名单列表 + Radius用户黑名单列表
        $srcIPBlackList = $doc->createElement("SrcIPBlackList");
        $radiusBlackList = $doc->createElement("RadiusBlackList");
        $srcIPBlackListResult = Ipblacklist::getListUpgrade(0,0,9,$serverid);
        if($srcIPBlackListResult['code'] == 0 && count($srcIPBlackListResult['data']) > 0){
            $br = 0;
            $bl = 0;
            foreach ($srcIPBlackListResult['data'] as $srcIPWhiteListvalue){
                if($srcIPWhiteListvalue['iptype'] == 1){//源IP用户白名单列表
                    $bl++;
                    $srcIPBlackListLabel = $doc->createElement("IP");
                    $srcIPBlackListLabel->setAttribute("address",$srcIPWhiteListvalue['content']);
                    $srcIPBlackListLabel->setAttribute("format",$srcIPWhiteListvalue['format']);
                    $srcIPBlackList->appendChild($srcIPBlackListLabel);
                }else if ($srcIPWhiteListvalue['iptype'] == 0){//Radius用户白名单列表
                    $br++;
                    $radiusBlackLisLabel = $doc->createElement("Radius");
                    $radiusBlackLisLabel->setAttribute("Account",$srcIPWhiteListvalue['content']);
                    $radiusBlackList->appendChild($radiusBlackLisLabel);
                }
            }
            if($bl>0){
                $flowRuleConvert  -> appendChild($srcIPBlackList);
            }
            if($br>0){
                $flowRuleConvert  -> appendChild($radiusBlackList);
            }
        }

        //源禁止推送
        $userPushIpExclude = $doc->createElement("UserPushIpExclude");
        $userPushIpExcludeResult = Prohibitpush::getListUpgrade(0,0,9,$serverid);
        if($userPushIpExcludeResult['code'] == 0 && count($userPushIpExcludeResult['data']) > 0){
            $pr = 0;
            $pl = 0;
            foreach ($userPushIpExcludeResult['data'] as $userPushIpExcludeResultvalue){
                if($userPushIpExcludeResultvalue['iptype'] == 1){//源IP用户白名单列表
                    $pl++;
                    $srcIPBlackListLabel = $doc->createElement("IP");
                    $srcIPBlackListLabel->setAttribute("address",$userPushIpExcludeResultvalue['content']);
                    $srcIPBlackListLabel->setAttribute("format",$userPushIpExcludeResultvalue['format']);
                    $userPushIpExclude->appendChild($srcIPBlackListLabel);
                }
            }
            if($pl>0){
                $flowRuleConvert  -> appendChild($userPushIpExclude);
            }
        }



        //域名规则
        $hostRuleList = $doc->createElement("HostRuleList");
        $generalRuleList = $doc->createElement("GeneralRuleList");
        $apkLabel = $doc->createElement('APK');//创建APK节点
        $exeLabel = $doc->createElement('EXE');//创建EXE节点
        $ruleAllData = Serverchildruledata::ruleXmlsData($serverid);//五表联查所有的规则
        $res = array(); //创建三维数组
        foreach ($ruleAllData as $k => $v) {//按照三维数组进行分型
            $res[$v['ruleid']][] = $v;
        }
        $hosti = 0;
        $generali = 0;
        foreach($res as $key=>$value){
           if(is_array($value) && count($value) > 0){
               if($value[0]['product_type'] == 1){//基本
                   $hosti++;
                   $hostLabel = $doc ->createElement('HOST');
                   if(!empty($value[0]['rule_host'])){
                       $hostLabel->setAttribute("domain",$value[0]['rule_host']);
                   }
                   if(!empty($value[0]['rule_exhost'])){
                       $hostLabel->setAttribute("HostFilter",$value[0]['rule_exhost']);
                   }
                   foreach ($value as $ruleAllDataValue){
                       $ieLabelArray = array();
                       $ieLabelArray['Exclude'] = self::compare($ruleAllDataValue['rule_exuri'],$ruleAllDataValue['childrule_exuri']);
                       $ieLabelArray['Exclude'] = self::compare($ieLabelArray['Exclude'],$ruleAllDataValue['update_childrule_exuri']);
                       $ieLabelArray['UaFilter'] = self::compare($ruleAllDataValue['rule_exua'],$ruleAllDataValue['childrule_exua']);
                       $ieLabelArray['UaWholeFilter'] = self::compare($ruleAllDataValue['rule_precise_exua'],$ruleAllDataValue['childrule_precise_exua']);
                       $ieLabelArray['CookieFilter'] = self::compare($ruleAllDataValue['rule_excookie'],$ruleAllDataValue['childrule_excookie']);
                       $ieLabelArray['UaInclude'] = $ruleAllDataValue['childrule_inua'];//
                       if($ruleAllDataValue['childrule_match'] == 1){
                           $ieLabelArray['Include'] = $ruleAllDataValue['childrule_uri'];//来源排除字段
                       }else{
                           $ieLabelArray['Include'] = $ruleAllDataValue['childrule_inuri'];//来源排除字段
                           $ieLabelArray['UriPrefix'] = $ruleAllDataValue['childrule_uri_start'];//URI前缀匹配
                       }

                       $ieLabelArray['ReferInclude'] = $ruleAllDataValue['childrule_inreferer'];//来源包含字段
                       $ieLabelArray['ReferExclude'] = $ruleAllDataValue['childrule_exreferer'];//来源排除字段
                       $ieLabelArray = array_filter($ieLabelArray);
                       if($ruleAllDataValue['childrule_process_mode'] != 0){
                           $ieLabelArray['ProcessMode'] = $ruleAllDataValue['childrule_process_mode'];
                           $ieLabelArray['CollectTime'] = $ruleAllDataValue['childrule_collect_time'];//采集时间
                       }
                        $ruleLabel = $doc ->createElement('Rule');
                        $ruleLabel->setAttribute("id",$ruleAllDataValue['childruleid']);
                        $ruleLabel->setAttribute("productid",$ruleAllDataValue['productid']);
                        $ruleLabel->setAttribute("ruleid",$ruleAllDataValue['ruleid']);
                        if($ruleAllDataValue['childrule_type'] == 1 || $ruleAllDataValue['childrule_type'] == 2){
                           $ruleLabel->setAttribute("type","200");
                            $ieLabelArray['Content'] = $ruleAllDataValue['childrule_type'];
                            if(!empty($ruleAllDataValue['update_childrule_push_content'])){
                                $ieLabelArray['RespContent'] = $ruleAllDataValue['update_childrule_push_content'];//来源排除字段
                            }elseif (!empty($ruleAllDataValue['model_childrule_push_content'])){
                                $ieLabelArray['RespContent'] = $ruleAllDataValue['model_childrule_push_content'];//来源排除字段
                            }
                        }else{
                           $ruleLabel->setAttribute("type",$ruleAllDataValue['childrule_type']);
                            if(!empty($ruleAllDataValue['update_childrule_push_content'])){
                                $ieLabelArray['DstHost'] = $ruleAllDataValue['update_childrule_push_content'];//来源排除字段
                            }elseif (!empty($ruleAllDataValue['model_childrule_push_content'])){
                                $ieLabelArray['DstHost'] = $ruleAllDataValue['model_childrule_push_content'];//来源排除字段
                            }
                        }
                        $ruleLabel->setAttribute("match",$ruleAllDataValue['childrule_match']);
                        $ruleLabel->setAttribute("ratio",$ruleAllDataValue['binding_childrule_ratio']);
                        $ruleLabel->setAttribute("combine",$ruleAllDataValue['childrule_match_type']);
                       if($ruleAllDataValue['autoexclude'] != 0){
                           $ruleLabel->setAttribute("AutoExclude",$ruleAllDataValue['autoexclude']);
                       }
                        if(!empty($ruleAllDataValue['userpushtimepolicy']) && $ruleAllDataValue['userpushtimepolicy'] != 0){
                            $ruleLabel->setAttribute("UserPushTimePolicy",$ruleAllDataValue['userpushtimepolicy']);
                        }

                       if($ruleAllDataValue['pushexcloud'] == 1 && $userPushIpExcludeResult['code'] == 0){//是否过滤
                           $ruleLabel->setAttribute("PushIpExcludeFlag",$ruleAllDataValue['pushexcloud']);
                       }

                        foreach ($ieLabelArray as $key => $value){
                            $ieLabel = $doc ->createElement('IE');
                            $ieLabel->setAttribute($key,$value);
                            $ruleLabel->appendChild($ieLabel);
                        }
                        $hostLabel->appendChild($ruleLabel);
                        $hostRuleList->appendChild($hostLabel);
                   }

               }else if($value[0]['product_type'] == 0){//通匹
                   $generali ++;
                    foreach ($value as $ruleAllDataValue ){
                        $iApk = 0 ;
                        $iExe = 0 ;
                        $ieLabelArray = array();
                        $ieLabelArray['Exclude'] = self::compare($ruleAllDataValue['rule_exuri'],$ruleAllDataValue['childrule_exuri']);
                        $ieLabelArray['Exclude'] = self::compare($ieLabelArray['Exclude'],$ruleAllDataValue['update_childrule_exuri']);
                        $ieLabelArray['UaFilter'] = self::compare($ruleAllDataValue['rule_exua'],$ruleAllDataValue['childrule_exua']);
                        $ieLabelArray['UaWholeFilter'] = self::compare($ruleAllDataValue['rule_precise_exua'],$ruleAllDataValue['childrule_precise_exua']);
                        $ieLabelArray['CookieFilter'] = self::compare($ruleAllDataValue['rule_excookie'],$ruleAllDataValue['childrule_excookie']);
                        $ieLabelArray['UaInclude'] = $ruleAllDataValue['childrule_inua'];//ua包含
                        if($ruleAllDataValue['childrule_match'] == 1){
                            $ieLabelArray['Include'] = $ruleAllDataValue['childrule_uri'];//来源排除字段
                        }else{
                            $ieLabelArray['Include'] = $ruleAllDataValue['childrule_inuri'];//来源排除字段
                            $ieLabelArray['UriPrefix'] = $ruleAllDataValue['childrule_uri_start'];//URI前缀匹配
                        }

                        $ieLabelArray['ReferInclude'] = $ruleAllDataValue['childrule_inreferer'];//来源包含字段
                        $ieLabelArray['ReferExclude'] = $ruleAllDataValue['childrule_exreferer'];//来源排除字段
                        $ieLabelArray = array_filter($ieLabelArray);
                        if($ruleAllDataValue['childrule_process_mode'] != 0){
                            $ieLabelArray['ProcessMode'] = $ruleAllDataValue['childrule_process_mode'];
                            $ieLabelArray['CollectTime'] = $ruleAllDataValue['childrule_collect_time'];//采集时间
                        }
                        $apkRuleLabel = $doc->createElement('Rule');//创建Rule节点
                        $apkRuleLabel->setAttribute("id",$ruleAllDataValue['childruleid']);
                        $apkRuleLabel->setAttribute("productid",$ruleAllDataValue['productid']);
                        $apkRuleLabel->setAttribute("ruleid",$ruleAllDataValue['ruleid']);
                        if($ruleAllDataValue['childrule_type'] == 1 || $ruleAllDataValue['childrule_type'] == 2){
                            $apkRuleLabel->setAttribute("type","200");
                            $ieLabelArray['Content'] = $ruleAllDataValue['childrule_type'];
                            if(!empty($ruleAllDataValue['update_childrule_push_content'])){
                                $ieLabelArray['RespContent'] = $ruleAllDataValue['update_childrule_push_content'];//来源排除字段
                            }elseif (!empty($ruleAllDataValue['model_childrule_push_content'])){
                                $ieLabelArray['RespContent'] = $ruleAllDataValue['model_childrule_push_content'];//来源排除字段
                            }
                        }else{
                            $apkRuleLabel->setAttribute("type",$ruleAllDataValue['childrule_type']);
                            if(!empty($ruleAllDataValue['update_childrule_push_content'])){
                                $ieLabelArray['DstHost'] = $ruleAllDataValue['update_childrule_push_content'];//来源排除字段
                            }elseif (!empty($ruleAllDataValue['model_childrule_push_content'])){
                                $ieLabelArray['DstHost'] = $ruleAllDataValue['model_childrule_push_content'];//来源排除字段
                            }
                        }
                        $apkRuleLabel->setAttribute("match",$ruleAllDataValue['childrule_match']);

                        $apkRuleLabel->setAttribute("ratio",$ruleAllDataValue['binding_childrule_ratio']);
                        $apkRuleLabel->setAttribute("combine",$ruleAllDataValue['childrule_match_type']);
                        $ruleAllDataValue['rule_exhost'] =  self::compare($ruleAllDataValue['rule_exhost'],$ruleAllDataValue['binding_childrule_host']);
                        if(!empty($ruleAllDataValue['rule_exhost'])){
                            $apkRuleLabel->setAttribute("HostFilter",$ruleAllDataValue['rule_exhost']);
                        }
                        if($ruleAllDataValue['autoexclude'] != 0){
                            $apkRuleLabel->setAttribute("AutoExclude",$ruleAllDataValue['autoexclude']);
                        }
                        if($ruleAllDataValue['pushexcloud'] == 1 && $userPushIpExcludeResult['code'] == 0){
                            $apkRuleLabel->setAttribute("PushIpExcludeFlag",$ruleAllDataValue['pushexcloud']);
                        }
                        if(!empty($ruleAllDataValue['userpushtimepolicy']) && $ruleAllDataValue['userpushtimepolicy'] != 0){
                            $apkRuleLabel->setAttribute("UserPushTimePolicy",$ruleAllDataValue['userpushtimepolicy']);
                        }
                        if($ruleAllDataValue['match_type'] == 0){//APK类型 $iexe++;
                            if($iApk < 1){
                                $generalRuleList->appendChild($apkLabel);
                                $iApk++;
                            }
                            foreach ($ieLabelArray as $key => $value){
                                $ieLabel = $doc ->createElement('IE');
                                $ieLabel->setAttribute($key,$value);
                                $apkRuleLabel->appendChild($ieLabel);
                            }
                            $apkLabel->appendChild($apkRuleLabel);
                        }elseif ($ruleAllDataValue['match_type'] == 1){//EXE
                            if($iExe < 1){
                                $generalRuleList->appendChild($exeLabel);
                                $iExe++;
                            }
                            foreach ($ieLabelArray as $key => $value){
                                $ieLabel = $doc ->createElement('IE');
                                $ieLabel->setAttribute($key,$value);
                                $apkRuleLabel->appendChild($ieLabel);
                            }
                            $exeLabel->appendChild($apkRuleLabel);
                        }
                    }
               }
           }
        }
        if($hosti > 0){//如果$hostRuleList有内容就追加到父节点中
            $flowRuleConvert  -> appendChild($hostRuleList);
        }
        if($generali > 0){//如果$generalRuleList有内容就追加到父节点中
            $flowRuleConvert  -> appendChild($generalRuleList);
        }
        $doc->appendChild($flowRuleConvert);//添加父节点
        $result = $doc->save("rulefile/rule_".$serverid.".xml");//保存文件到rulefile文件
        $shellResult = @self::executeShell($serverid);//执行加密操作
        if($shellResult['code'] == 0){ // 生成加密文件后去get方式请求服务器发送通知  例如：192.168.7.250:8080?1  地址由后台配置
            Cache::rm('code'.$serverid);//再次清除缓存的该服务器提示代码
            $serverIp = Config::get('server_ip');//获取服务器地址IP及其端口
            $url = $serverIp.'/?9&id='.$serverid;//串接地址
            $data = Common::requestGet($url);//发送GET请求
            if($data){//第二步向服务器ip端口发送请求成功，把服务器的200 rerun出去
                return $data;
            }
        }else{
            return $shellResult['code'];//加密失败返回程序
        }
    }


    /**
     * 比较两个值，相等
     * @param $parameter1参数1
     * @param $parameter2
     */
    public static function compare($parameter1,$parameter2)
    {
        if(!empty($parameter1) && !empty($parameter2) && $parameter1 !== $parameter2){
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

    /**
     * 生成加密XML
     * @param $serverid 服务器ID
     * @return array[shellResult] 执行linux命令结果，成功 0，
     *
     */

    public static function executeShell($serverid)
    {

        $shellCommand = 'cd rulefile;./encryptionRule '.$serverid;
       //$shellCommand = 'mkdir test';
        system($shellCommand,$shellResult);//成功以后这一步会将加密程序返回的0赋值给shellResult，对shellResult进行判断
        if($shellResult == 0){
            $returnArray = array(
                'code' => 0,
                'msg' => Error::ERRORCODE[0],
                'shellResult' => $shellResult,
            );
        }else{
            $returnArray = array(
                'code' => 12001,
                'msg' => Error::ERRORCODE[12001],
                'shellResult' => $shellResult,//返回加密失败的程序报的错误
            );;
        }
        $returnArray = array(
            'code' => 0,
            'msg' => Error::ERRORCODE[0],
            'shellResult' => $shellResult,
        );
        return $returnArray;
    }

     public static function update($data = [], $where = [], $field = null)
     {
         return parent::update($data, $where, $field); // TODO: Change the autogenerated stub
     }


    /**
     * 操作服务器各种
     * @param $serverid操作的id
     * @param $opcode操作码
     */
     public static function operateServer($serverid,$opcode)
     {
         $returnArray = [];
         if(!empty($serverid) &&  !empty($opcode)){
             $serverIp = Config::get('server_ip');//获取服务器地址IP及其端口
             $url = $serverIp.'/?'.$opcode.'&id='.$serverid;//串接地址
             if($opcode == 7){
                 $data = Common::otherRequestGet($url);//发送GET请求
             }elseif ($opcode == 10){
                 $data = Common::otherRequestGet($url);//发送GET请求
             }
             $data = Common::requestGet($url);//发送GET请求
             if($data = 200){//第二步向服务器ip端口发送请求成功，把服务器的200 rerun出去
                 return $data;
             }else{
                 return $data;//返回的是直接错误结果解析
             }
         }else{
            $returnArray = [
                'code' => 40012,
                'msg' => Error::ERRORCODE[40012],
                'data' => []
            ];
         }
         return $returnArray;
     }

    /**
     * 根据时间去更新状态
     * @param array $data
     * @param array $where
     *
     */
    public function checkServerStatus($id = 0)
    {
//        1540434900 当前时间
//        1540434600 差
        $reference = time(); //当前时间
        if($id != 0){
            $result = self::field('updatetime,id')->where('id',$id)->find()->toArray();
            if($result){
                if($result['updatetime'] != null){
                    $mistiming = $reference - $result['updatetime'];
                    if ($mistiming > 300){
                        self::update(array('serverstatus' => 0),array('id'=>$id));
                        $resultArray = array(
                            'code' => 40009,
                            'msg' => Error::ERRORCODE[40009],
                            'data' => array()
                        );
                    }else{
                        $resultArray = array(
                            'code' => 0,
                            'msg' => Error::ERRORCODE[0],
                            'data' => array()
                        );
                    }
                }else{
                    $resultArray = array(
                        'code' => 40008,
                        'msg' => Error::ERRORCODE[40008],
                        'data' => array()
                    );
                }
            }
        }else{
            $resultList = self::field('updatetime,id')->select()->toArray();
            $info = array();
            $i = 0;
            foreach ($resultList as $value){
                if($value['updatetime'] == null ){
                    $info[$i]['id'] = $value['id'];
                    $info[$i]['serverstatus'] = 0;
                }else{
                    $mistiming = $reference - $value['updatetime'];
                    if ($mistiming > 300){
                        $info[$i]['id'] = $value['id'];
                        $info[$i]['serverstatus'] = 0;
                    }
                }
                $i++;
            }

            $resultArray = array();
            if(count($info) > 0){
                $serverUpdateResult = self::saveAll($info);
                $resultArray = array(
                    'code' => 0,
                    'msg' => Error::ERRORCODE[0],
                    'data' => array()
                );


            }else{
                $resultArray = array(
                    'code' => 40007,
                    'msg' => Error::ERRORCODE[40007],
                    'data' => array()
                );
            }
        }

        return $resultArray;
    }

    public static  function upgradeGetOne($where,$filed = null)
    {
        if(!empty($where)){
            $result = self::where($where)->field($filed)->find();
            if($result){
                $returnArray  = $result->toArray();
                return $returnArray['servername'];
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
}


