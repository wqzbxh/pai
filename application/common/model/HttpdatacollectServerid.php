<?php
/**
 * Created by PhpStorm.
 * User: wanghaiyang
 * Date: 2018/10/17
 * Time: 9:37
 */
//信息采集
namespace app\common\model;

use think\Model;

Class HttpdatacollectServerid extends Model{

    protected $connection = 'db_config_cards';

    //获取所有的表名
    public function getTables()
    {
        $returnArray = array();
        $errorModel = new \app\common\model\Error();
        //获取表名
       $result = self::query('show tables');
       if(!empty($result)){

            //得到的表名匹配主数据库中服务器

           $serverDataModel = new \app\common\model\Serverdata();
           $serverNumResult = $serverDataModel->field('id')->select()->toArray();
           $j = 0;
           $k = 0;
           $branchWarehouseNum = array();
           $serverNum = array();
           foreach ($serverNumResult as $value){
               $serverNum[$k] = $value['id'];
               $k++;
           }
           foreach ($result as $value){
               $branchWarehouseNum[$j] = substr($value['Tables_in_pai_test'],16);
               $j++;
           }


           $lastNum = array_intersect($branchWarehouseNum,$serverNum);

//          将表名转换为大写
           $ChineseTable = array();
           $commonController  = new \app\common\controller\Common();
           foreach ($lastNum as $value){
             $key = 'httpdatacollect_'.$value;
             $valueResult = $serverDataModel->field('servername')->where(array('id'=>$value))->find()->toArray();
             $ChineseTable[$key] = $valueResult['servername'];
           }

           $returnArray = array(
               'code' => 0,
               'msg' => $errorModel::ERRORCODE[0],
               'data' => $ChineseTable
           );
       }else{
           $returnArray = array(
               'code' => 90001,
               'msg' => $errorModel::ERRORCODE[90001],
               'data' => array()
           );
       }

        return $returnArray;
    }

    public function getList($tabaleName,$offset,$limit)
    {
        $returnArray = array();
        $errorModel = new \app\common\model\Error();
        if($tabaleName){
            $result =  self::table($tabaleName)
                ->limit($offset,$limit)
                ->select()
                ->toArray();
            $count =  self::table($tabaleName)
                ->limit($offset,$limit)
                ->count();
           if(!empty($result)){
               $returnArray = array(
                   'code' => 0,
                   'msg' => $errorModel::ERRORCODE[0],
                   'count' => $count,
                   'data' => $result
               );
           }else{
               $returnArray = array(
                   'code' => 90003,
                   'msg' => $errorModel::ERRORCODE[90003],
                   'data' => array()
               );
           }
        }else{
            $returnArray = array(
                'code' => 90002,
                'msg' => $errorModel::ERRORCODE[90002],
                'data' => array()
            );
        }

        return $returnArray;
    }

    /**
     * @param $tableName 要删除的表名字
     */
    public function delTable($tableName)
    {
        $returnArray = array();
        $errorModel = new \app\common\model\Error();
        if(!empty($tableName)){
            $sql = 'SHOW TABLES LIKE "'.$tableName.'"';
            $checkTabel =self::query( $sql);
            if(!empty($checkTabel)){
                try {
                    $sql = "drop table ".$tableName;
                    $result = self::execute($sql);
                    $returnArray = array(
                        'code' => 0,
                        'msg' => $errorModel::ERRORCODE[0],
                        'data' => $result
                    );
                } catch(Exception $e) {
                    $returnArray = array(
                        'code' => 90004,
                        'msg' => $errorModel::ERRORCODE[90004],
                        'data' => array()
                    );
                }
            }else{
                $returnArray = array(
                    'code' => 90005,
                    'msg' => $errorModel::ERRORCODE[90005],
                    'data' => array()
                );
            }
        }else{
            $returnArray = array(
                'code' => 90002,
                'msg' => $errorModel::ERRORCODE[90002],
                'data' => array()
            );
        }
        return $returnArray;
    }

    public function getAllList($tableName)
    {
        $result = self::table($tableName)
            ->select()
            ->toArray();

        return $result;
    }


//    海海导出CSV/**

    /**
     * @param $tableName 数据库表
     * @param string $fileName
     * @param $mark 下载的文件名字
     * @param $head CSV的表头
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */

    public function exportCsv($tableName,$fileName='test.csv',$mark,$head)
    {
        set_time_limit(0);
        $data = self::table($tableName);
        $sqlCount = $data->count();
        header('Content-Type: application/vnd.ms-excel;charset=utf-8');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $sqlLimit = 100000;
        // 每隔$limit行，刷新一下输出buffer，不要太大，也不要太小
        $limit = 100000;
        // buffer计数器
        $cnt = 0;
        $fileNameArr = array();
        // 逐行取出数据，不浪费内存
        for ($i = 0; $i < ceil($sqlCount / $sqlLimit); $i++) {
            $fp = fopen($mark . '_' . $i . '.csv', 'w'); //生成临时文件
            //     chmod('attack_ip_info_' . $i . '.csv',777);//修改可执行权限
            $fileNameArr[] = $mark . '_' .  $i . '.csv';
            // 将数据通过fputcsv写到文件句柄
            fputcsv($fp, $head);
            $dataArr = self::table($tableName)->limit($i * $sqlLimit,$sqlLimit)->select()->toArray();

            foreach ($dataArr as $a) {
                $cnt++;
                if ($limit == $cnt) {
                    //刷新一下输出buffer，防止由于数据过多造成问题
                    ob_flush();
                    flush();
                    $cnt = 0;
                }
                fputcsv($fp, $a);
            }
            fclose($fp);  //每生成一个文件关闭
        }
        //进行多个文件压缩
        $zip = new \ZipArchive();
        $filename = $mark . ".zip";
        $zip->open($filename, \ZipArchive::CREATE);   //打开压缩包
        foreach ($fileNameArr as $file) {
            $zip->addFile($file, basename($file));   //向压缩包中添加文件
        }
        $zip->close();  //关闭压缩包
        foreach ($fileNameArr as $file) {
            unlink($file); //删除csv临时文件
        }
        //输出压缩文件提供下载
        header("Cache-Control: max-age=0");
        header("Content-Description: File Transfer");
        header('Content-disposition: attachment; filename=' . basename($filename)); // 文件名
        header("Content-Type: application/zip"); // zip格式的
        header("Content-Transfer-Encoding: binary"); //二进制
        header('Content-Length: ' . filesize($filename)); //
        @readfile($filename);//输出文件;
        unlink($filename); //删除压缩包临时文件
    }


}