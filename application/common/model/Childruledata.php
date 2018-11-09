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
class Childruledata extends Model
{
    //模糊查询字段
    public $fuzzy_query = '';

    const jionField = "r.*,p.product_type";

    const binDingField = "r.*,s.id as spid,s.status,s.serverid,s.product_id";
    /**
     * 查询产品方法
     * @param $match_type 通匹类型：0为APK，1为EXE，默认值为0  9为全部
     * @param $product_type 产品类型：0为通匹，1为基本，默认值为1 9为全部
     * @param $type 是否开启模糊查询 1 是  0 否
     * @param string $product_name 产品名称
     */


    public function getChildruleRuleList($childrule_name = '',$offset,$limit,$childruleid)
    {
        $criteria = array();
        $returnArray = array();
        $errorModel = new \app\common\model\Error();
        $criteria['is_del'] = 0;
        $criteria['ruleid'] = $childruleid;
        if(!empty($childrule_name)){
            $result = self::where($criteria)
                ->limit($offset,$limit)
                ->where('childrule_name','like','%'.$childrule_name.'%')
                ->select()
                ->toArray();

            $count = self::where($criteria)
                ->count();
        }else{
            $result = self::where($criteria)
                ->limit($offset,$limit)
                ->where('childrule_name','like','%'.$childrule_name.'%')
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


    public function getChildRuleBindingList($offset,$limit,$serverid,$rule_id,$product_id,$status,$childrule_name='')
    {

        $returnArray = array();
        $criteria = array();
        $errorModel = new \app\common\model\Error();
        $criteria['r.ruleid'] = $rule_id;
        $criteria['r.is_del'] = 0;
        if($status != 9 && $status != 0) {
            $criteria['s.status'] = $status;
        }else if($status == 0 && $status != 1 ){
          // $criteria['s.status'] =  array('in',array(0,'null'));
            $criteria['s.status'] = [ [ '<>' , 1] , [ 'NULL' , null ] ,'or' ] ;

        }

        if(!empty($childrule_name)){
            $result = self::alias('r')
                ->join('serverchildruledata s','r.id = s.child_rule_id and s.serverid='.$serverid.' and s.product_id = '.$product_id.' and s.rule_id = '.$rule_id,"LEFT" )
                ->field(self::binDingField)
                ->where($criteria)
                ->where('r.childrule_name','like','%'.$childrule_name.'%')
                ->limit($offset,$limit)
                ->select()
                ->toArray();
            $count = self::alias('r')
                ->join('serverchildruledata s','r.id = s.child_rule_id and s.serverid='.$serverid.' and s.product_id = '.$product_id.' and s.rule_id = '.$rule_id,"LEFT" )
                ->field(self::binDingField)
                ->where('r.childrule_name','like','%'.$childrule_name.'%')
                ->where($criteria)->count();
        }else{
            $result = self::alias('r')
                ->join('serverchildruledata s','r.id = s.child_rule_id and s.serverid='.$serverid.' and s.product_id = '.$product_id.' and s.rule_id = '.$rule_id,"LEFT" )
                ->field(self::binDingField)
                ->where($criteria)
                ->limit($offset,$limit)
                ->select()
                ->toArray();

            $count = self::alias('r')
                ->join('serverchildruledata s','r.id = s.child_rule_id and s.serverid='.$serverid.' and s.product_id = '.$product_id.' and s.rule_id = '.$rule_id,"LEFT" )
                ->field(self::binDingField)
                ->where($criteria)->count();
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
    public function addChildrule($data)
    {
        $errorModel = new \app\common\model\Error();
        $returnArray = array();
        if(is_array($data)){
            $checkResult = self::checkChildRule($data['childrule_name'],$data['ruleid'],$data['productid']);
            if($checkResult > 0){
                $returnArray = array(
                    'code' => 30006,
                    'msg' => $errorModel::ERRORCODE[30006],
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
                        'code' => 30002,
                        'msg' => $errorModel::ERRORCODE[30002],
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


    /**校验名称
     * @param $name 名称
     * @param int $id
     */
    public function checkChildRule($name,$ruleid,$productid,$id = 0)
    {

        if($id == 0){
//        对新增数据进行名称查重 返回0/1
            $result = self::where(array('childrule_name'=>$name,'ruleid'=>$ruleid,'productid'=>$productid))->count();
        }else{
//            对修改数据进行查重
            $result = self::where(array('childrule_name'=>$name,'ruleid'=>$ruleid,'productid'=>$productid))->select()->toArray();

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
    public function getChildruleOne($id)
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
            $checkResult = self::checkChildRule($data['childrule_name'],$data['ruleid'],$data['productid'],$data['id']);
            if($checkResult > 0){
                $returnArray = array(
                    'code' => 30006,
                    'msg' => $errorModel::ERRORCODE[30006],
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
            $result = self::where('id', $id)->delete();
            if($result == 1){
                $returnArray = array(
                    'code' => 0,
                    'msg' => $errorModel::ERRORCODE[0],
                    'data' => $result,
                );
            }else{
                $returnArray = array(
                    'code' => 20009,
                    'msg' => $errorModel::ERRORCODE[20009],
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
     * $data条件的数组
     */
    public function conditionDel($data)
    {
        $errorModel = new \app\common\model\Error();
        $returnArray = array();
        if(is_array($data)){
            $result = self::where($data)->delete();
            if($result>0){
                $returnArray = array(
                    'code' => 0,
                    'msg' => $errorModel::ERRORCODE[0],
                    'data' => $result,
                );
            }else{
                $returnArray = array(
                    'code' => 30007,
                    'msg' => $errorModel::ERRORCODE[30007],
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


    /**
     * @param array $data
     * @param array $where
     * @param null $field
     * @return Model
     */
    public static function update($data = [], $where = [], $field = null)
    {
        return parent::update($data, $where, $field); // TODO: Change the autogenerated stub
    }


}