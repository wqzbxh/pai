<?php
namespace app\common\model;
use think\Model;
use think\queue\job\Database;

/**
 *
 * @author wanghaiyang
 * @date Tue Oct 09 2018 15:22:14 GMT+0800 (中国标准时间)
 * @version 1.0
 */
class Ruledata extends Model
{
    //模糊查询字段
    public $fuzzy_query = '';

    const jionField = "r.*,p.product_type";


    const binDingField = "r.*,s.id as spid,s.status,s.serverid,s.product_id";
    /**
     * 查询规则方法
     * @param $match_type 通匹类型：0为APK，1为EXE，默认值为0  9为全部
     * @param $product_type 产品类型：0为通匹，1为基本，默认值为1 9为全部
     * @param $type 是否开启模糊查询 1 是  0 否
     * @param string $product_name 产品名称
     */


    public function getRuleList($rule_name = '',$offset,$limit,$productid)
    {
        $criteria = array();
        $returnArray = array();
        $errorModel = new \app\common\model\Error();
        $criteria['r.is_del'] = 0;
        $criteria['productid'] = $productid;
        if(!empty($rule_name)){//搜索
            $result = self::alias('r')
                ->join('productdata p','r.productid = p.id',"LEFT" )
                ->where($criteria)
                ->where('rule_name','like','%'.$rule_name.'%')
                ->order('r.order desc')
                ->field(self::jionField)
                ->limit($offset,$limit)
                ->select()
                ->toArray();

            $count = self::alias('r')
                ->join('productdata p','r.productid = p.id',"LEFT" )
                ->where('rule_name','like','%'.$rule_name.'%')
                ->order('r.order desc')
                ->where($criteria)
                ->count();
        }else{
            $result = self::alias('r')
                ->join('productdata p','r.productid = p.id',"LEFT" )
                ->where($criteria)
                ->order('r.order desc')
                ->field(self::jionField)
                ->limit($offset,$limit)
                ->select()
                ->toArray();

            $count = self::alias('r')
                ->join('productdata p','r.productid = p.id',"LEFT" )
                ->where($criteria)->order('r.order desc')
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
     * @param $offset
     * @param $limit
     * @param $serviceid
     * @param $id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */

    public function getRuleBindingList($offset,$limit,$serviceid,$id,$status,$rule_name)
    {

        $returnArray = array();
        $criteria = array();
        $errorModel = new \app\common\model\Error();
        $criteria['r.productid'] = $id;
        $criteria['r.is_del'] = 0;

        if($status != 9 && $status != 0) {
            $criteria['s.status'] = $status;
        }else if($status == 0 && $status != 1 ){
            $criteria['s.status'] = [ [ '<>' , 1] , [ 'NULL' , null ] ,'or' ] ;
        }
        if(!empty($rule_name)){
            $result = self::alias('r')
                ->join('serverruledata s','r.id = s.rule_id and s.serverid='.$serviceid.' and s.product_id = '.$id,"LEFT" )
                ->field(self::binDingField)
                ->where($criteria)
                ->where('r.rule_name','like','%'.$rule_name.'%')
                ->limit($offset,$limit)
                ->select()
                ->toArray();
            $count = self::alias('r')
                ->join('serverruledata s','r.id = s.rule_id and s.serverid='.$serviceid.' and s.product_id = '.$id,"LEFT" )
                ->field(self::binDingField)->where($criteria)
                ->where('r.rule_name','like','%'.$rule_name.'%')
                ->count();

        }else{
            $result = self::alias('r')
                ->join('serverruledata s','r.id = s.rule_id and s.serverid='.$serviceid.' and s.product_id = '.$id,"LEFT" )
                ->field(self::binDingField)
                ->where($criteria)
                ->limit($offset,$limit)
                ->select()
                ->toArray();

            $count = self::alias('r')->join('serverruledata s','r.id = s.rule_id and s.serverid='.$serviceid.' and s.product_id = '.$id,"LEFT" )->field(self::binDingField)->where($criteria)->count();

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
     * 添加数据
     * @param data 添加数组参数
     */
    public function addRule($data)
    {
        $errorModel = new \app\common\model\Error();
        $returnArray = array();
        if(is_array($data)){
            $checkResult = self::checkRule($data['rule_name'],$data['productid']);
            if($checkResult > 0){
                $returnArray = array(
                    'code' => 30005,
                    'msg' => $errorModel::ERRORCODE[30005],
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
                        'code' => 20001,
                        'msg' => $errorModel::ERRORCODE[20001],
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


    /**
     * @param $data
     */
    public function checkRule($name,$productid,$id = 0)
    {

        if($id == 0){
//        对新增数据进行名称查重 返回0/1
            $result = self::where(array('rule_name'=>$name,'productid'=>$productid))->count();
        }else{
//            对修改数据进行查重
            $result = self::where(array('rule_name'=>$name,'productid'=>$productid))->select()->toArray();
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
     * 获取单个产品信息
     * @param id 产品的自增ID
     */
    public function getRuleOne($id)
    {
        $errorModel = new \app\common\model\Error();
        $returnArray = array();
        $result = self::where(array('id' => $id))->select()->toArray();
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
    public function updateRule($data)
    {
        $errorModel = new \app\common\model\Error();
        $returnArray = array();
        if(!empty($data['id'])){
            $checkResult = self::checkRule($data['rule_name'],$data['productid'],$data['id']);
            if($checkResult > 0){
                $returnArray = array(
                    'code' => 30005,
                    'msg' => $errorModel::ERRORCODE[30005],
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
     * 删除产品操作
     * @param id 产品的自增ID
     * Tue Oct 09 2018 15:10:18 GMT+0800 (中国标准时间)
     */
    public function delRule($id)
    {
        $errorModel = new \app\common\model\Error();
        $returnArray = array();
        if(!empty($id)){
//           删除本身的规则
            $result = self::where('id', $id)->delete();

//          删除规则下的子规则、
            $childRuleDataModel = new \app\common\model\Childruledata();
            $childRuleDataModel->conditionDel(array('ruleid' =>$id));

//            删除本身绑定记录

            $serverRuletModel = new \app\common\model\Serverruledata();
            $serverRuletModel->unbundle(array('rule_id' => $id));

//          删除子规则绑定的记录
            $serverChildRuletModel = new \app\common\model\Serverchildruledata();
            $serverChildRuletModel->delListRule(0,0,$id);

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
     * 根据条件批量删除
     * $data条件的数组 data为产品的id或者服务器的id
     */
    public function conditionDel($data)
    {
        $errorModel = new \app\common\model\Error();
        $returnArray = array();
        if(is_array($data)){
            $result = self::where($data)->delete();
            if($result>0){
                $childRuleDataModel = new\app\common\model\Childruledata();
                $childRuleDataResult = $childRuleDataModel->conditionDel($data);

                if($childRuleDataResult['code'] == 0 || $childRuleDataResult['code'] == 30007){
                    $returnArray = array(
                        'code' => 0,
                        'msg' => $errorModel::ERRORCODE[0],
                        'data' => $childRuleDataResult,
                    );
                }else{
                    $returnArray = array(
                        'code' => 0,
                        'msg' => $errorModel::ERRORCODE[0],
                        'data' => $result,
                    );
                }
            }else{
                $returnArray = array(
                    'code' => 20011,
                    'msg' => $errorModel::ERRORCODE[20011],
                    'data' => $result,
                );
            }
        }else{
            $returnArray = array(
                'code' => 10002,
                'msg' => $errorModel::ERRORCODE[10002],
                'data' => array()
            );
        }
    }

}