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
    const ERROR_SOCKET = 1;
    const ERROR_LOGIN = 2;
    const ERROR_CHECK_TIME = 3;
    const ERROR_MEASUREMENT = 4;     // 读遥测数据
    /**
     * 插入数据
     * @param $info
     * @param int $type
     * @return mixed
     */
    public static function insertInformation($info, $type = self::ERROR_SOCKET)
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