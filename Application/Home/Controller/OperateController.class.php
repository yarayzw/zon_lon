<?php
/**
 * Created by PhpStorm.
 * User: shawn
 * Date: 2018/4/27
 * Time: 18:54
 */

namespace Home\Controller;


use Home\Model\EquipmentListModel;

class OperateController extends PublicController
{
    public static function login($string) {
        $address = base_convert(substr($string, 6, 4), 16, 10);
        EquipmentListModel::getModelByAddressNo($address);
    }

}