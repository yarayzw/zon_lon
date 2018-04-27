<?php
/**
 * Created by PhpStorm.
 * User: shawn
 * Date: 2018/4/27
 * Time: 19:08
 */

namespace Home\Model;
use Think\Model;

class ErrorListModel extends Model
{
    /**
     * 插入数据
     * @param $info
     * @return mixed
     */
    public static function insertInformation($info)
    {
        $model = M('error_list');
        $data = $model->data(['error_info' => $info, 'error_time' => date('Y-m-d H:i:s')])->add();
        return $data;
    }


}