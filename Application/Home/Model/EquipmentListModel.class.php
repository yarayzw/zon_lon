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
     * 插入数据
     * @param $address_no
     * @return mixed
     */
    public static function getModelByAddressNo($address_no)
    {
        $model = M('equipment_list');
        $condition = ['equipment_address' => $address_no];
        $data = $model->where($condition)->find();
        if (!$data) ErrorListModel::insertInformation('No address of the device was found', 2);
        return $data;
    }


}