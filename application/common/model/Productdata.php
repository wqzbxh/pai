<?php
namespace app\common\model;
use think\Model;
use think\queue\job\Database;

/**
 *
 * @author wqzbxh
 * @date Tue Oct 09 2018 09:39:46 GMT+0800 (中国标准时间)
 * @version 1.0
 */
class Productdata extends Model
{
    //模糊查询字段
    public $fuzzy_query = 'product_name';

    const jionField = "p.*,s.id as spid,s.status,s.serverid";
    /**
     * 查询产品方法
     * @param $match_type 通匹类型：0为APK，1为EXE，默认值为0  9为全部
     * @param $product_type 产品类型：0为通匹，1为基本，默认值为1 9为全部
     * @param $type 是否开启模糊查询 1 是  0 否
     * @param string $product_name 产品名称
     */

    public function getProductList($match_type = 9 ,$product_type = 9 ,$product_name = '',$type = 0,$offset,$limit)
    {
        $criteria = array();
        $returnArray = array();
        $errorModel = new \app\common\model\Error();
        if($match_type != 9 ){
            $criteria['match_type'] = $match_type;
        }
        if($product_type != 9 ){
            $criteria['product_type'] = $product_type;
        }
        $criteria['is_del'] = 0;
        if(!empty($product_name)){
            $result = self::where($criteria)->where('product_name','like','%'.$product_name.'%')->limit($offset,$limit)->select()->toArray();
            $count = self::where($criteria)->where('product_name','like','%'.$product_name.'%')->count();
        }else{
            $result = self::where($criteria)->limit($offset,$limit)->select()->toArray();
            $count = self::where($criteria)->count();
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
     * @param int $match_type
     * @param int $product_type
     * @param string $product_name
     * @param int $type
     * @param $offset
     * @param $limit
     * @param $serviceid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getProductBindingList($match_type = 9 ,$product_type = 9 ,$product_name = '',$type = 0,$offset,$limit,$serviceid,$status)
    {

        $criteria = array();
        $returnArray = array();
        $errorModel = new \app\common\model\Error();

        if($status != 9 && $status != 0) {
            $criteria['s.status'] = $status;
        }else if($status == 0 && $status != 1 ){
            $criteria['s.status'] = null;
        }

        if(!empty($product_name)){
            $result = self::alias('p')
                ->join('serverproductdata s','p.id = s.product_id and s.serverid='.$serviceid,"LEFT" )
                ->field(self::jionField)
                ->limit($offset,$limit)
                ->where($criteria)
                ->where('product_name','like','%'.$product_name.'%')
                ->select()
                ->toArray();


            $count = self::alias('p')
                ->join('serverproductdata s','p.id = s.product_id and s.serverid='.$serviceid,"LEFT" )
                ->field(self::jionField)
                ->limit($offset,$limit)
                ->where($criteria)
                ->where('product_name','like','%'.$product_name.'%')
                ->count();

        }else{
            $result = self::alias('p')
                ->join('serverproductdata s','p.id = s.product_id and s.serverid='.$serviceid,"LEFT" )
                ->field(self::jionField)
                ->limit($offset,$limit)
                ->where($criteria)
                ->select()
                ->toArray();


            $count = self::alias('p')
                ->join('serverproductdata s','p.id = s.product_id and s.serverid='.$serviceid,"LEFT" )
                ->field(self::jionField)
                ->limit($offset,$limit)
                ->where($criteria)
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
     * 添加数据
     * @param data 添加数组参数
     */
    public function addProduct($data)
    {
        $errorModel = new \app\common\model\Error();
        $returnArray = array();
        if(is_array($data)){

            $checkResult = self::checkProduct($data['product_name']);
            if($checkResult > 0){
                $returnArray = array(
                    'code' => 20010,
                    'msg' => $errorModel::ERRORCODE[20010],
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
     * 获取单个产品信息
     * @param id 产品的自增ID
     */
    public function getProductOne($id)
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
    public function updateProduct($data)
    {
        $errorModel = new \app\common\model\Error();
        $returnArray = array();
        if(!empty($data['id'])){
            $checkResult = self::checkProduct($data['product_name'],$data['id']);
            if($checkResult > 0){
                $returnArray = array(
                    'code' => 20010,
                    'msg' => $errorModel::ERRORCODE[20010],
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

    public function delProduct($id)
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
     * 校验有没有该产品
     * @param $data
     */
    public function checkProduct($name,$id = 0)
    {
        if($id == 0){
//        对新增数据进行名称查重 返回0/1
            $result = self::where(array('product_name'=>$name))->count();
        }else{
//            对修改数据进行查重
            $result = self::where(array('product_name'=>$name))->select()->toArray();

            if($result){
                if($result[0]['id'] == $id){
                    $result = 0 ;
                }else{
                    $result = 1;
                }
            }else{
                $result = 0 ;
            }
        }
        return $result;
    }

    public static function getliuliang()
    {
        $result = self::alias('a')
        ->join('flow.productstatistics p','a.id = p.productid','LEFT')
        ->select()
        ->toArray();

        return $result;


    }
}