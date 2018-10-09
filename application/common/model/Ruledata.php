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
    /**
     * 查询产品方法
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
        $result = self::alias('r')
                    ->join('productdata p','r.productid = p.id',"LEFT" )
                    ->where($criteria)
                    ->field(self::jionField)
                    ->limit($offset,$limit)
                    ->select()
                    ->toArray();

        $count = self::alias('r')
                    ->join('productdata p','r.productid = p.id',"LEFT" )
                    ->where($criteria)
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
     * 添加数据
     * @param data 添加数组参数
     */
    public function addRule($data)
    {
        $errorModel = new \app\common\model\Error();
        $returnArray = array();
        if(is_array($data)){
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
            $result = self::where('id', $id)->update(['is_del' => 1]);
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
}