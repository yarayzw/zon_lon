<?php
/**
 * Created by PhpStorm.
 * User: shawn
 * Date: 2018/4/27
 * Time: 19:08
 */

namespace Home\Model;
use Think\Model;

class EquipmentListModel extends Model
{
    /**
     * 判断数据是否存在数据库中 不存在则插入错误日志
     * @param $address_no
     * @return mixed
     */
    public static function getModelByAddressNo($address_no)
    {
        $model = M('equipment_list');
        $condition = ['equipment_address' => $address_no];
        $data = $model->where($condition)->find();
        return $data;
    }

    public static function createData($data){
        $model = M('equipment_list');
        $_data = $model->where(['equipment_address' => $data['equipment_address']])->find();
        if(!$_data){
            return $model->add($data);
        }else return 0;
    }
}