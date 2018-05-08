<?php
/**
 * Created by PhpStorm.
 * User: shawn
 * Date: 2018/4/27
 * Time: 19:08
 */

namespace Home\Model;
use Think\Model;

class ContainerTypeModel extends Model
{
    protected static $table_name = 'container_type';

    /**
     * 插入数据
     * @param $info
     * @return mixed
     */
    public static function insertCommand( $data = [])
    {
        $model = M(self::$table_name);
        $data = $model->data($data)->add();
        return $data;
    }

}