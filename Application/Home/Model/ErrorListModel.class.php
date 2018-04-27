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
     * @param int $type
     * @return mixed
     */
    public static function insertInformation($info, $type = 1)
    {
        $data = 0;
        $model = M('error_list');
        try {
            $data = $model->data(['error_info' => $info, 'error_time' => date('Y-m-d H:i:s'), 'error_type' => $type])->add();
            if (!$data) throw new \PDOException('插入错误信息失败');
        } catch (\PDOException $exception) {
            echo $exception->getMessage();
        }
        return $data;
    }


}