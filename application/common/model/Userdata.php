<?php
/**
 * Created by PhpStorm.
 * User: wanghaiyang
 * Date: 2018/10/12
 * Time: 9:19
 */

namespace app\common\model;

use think\Model;

Class Userdata extends Model{



    /**
     * @param $access 账号
     * @param $passwd 密码
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function loginSin($access,$passwd){
        $errorModel = new \app\common\model\Error();
        if(!empty($access) && !empty($access)){
            $data = array();
            $data['username'] = $access;
            $data['passwd'] = $passwd;
            $result = self::where($data)->select()->toArray();
            if(!empty($result)){
                    $returnArray = array(
                        'code' => 0,
                        'msg' => $errorModel::ERRORCODE[0],
                        'data' => $result
                    );
            }else{
                $returnArray = array(
                    'code' => 10003,
                    'msg' => $errorModel::ERRORCODE[10003],
                    'data' => array()
                );
            }
        }else{
//            密码账号不能为空
            $returnArray = array(
                'code' => 10004,
                'msg' => $errorModel::ERRORCODE[10004],
                'data' => array()
            );
        }

        return $returnArray;
    }



}