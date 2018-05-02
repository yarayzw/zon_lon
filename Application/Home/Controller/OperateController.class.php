<?php
/**
 * Created by PhpStorm.
 * User: shawn
 * Date: 2018/4/27
 * Time: 18:54
 */

namespace Home\Controller;


use Home\Model\EquipmentListModel;
use Home\Model\ErrorListModel;
use Home\Model\CommandListModel;
use Home\Model\EquipmentStatisticsModel;

class OperateController extends PublicController
{
    /**
     * 登录 对时
     * @param $string
     * @param $accept_resource
     * @return null
     */
    public function login($string, $accept_resource)
    {
        // 地址的四位的16进制数据
        $address_six_teen = substr($string, 8, 4);
        $address = base_convert($address_six_teen, 16, 10);
        // 判断数据库中是否该设备 没有则报错
        $data = EquipmentListModel::getModelByAddressNo($address);
        if (!$data) {
            ErrorListModel::insertInformation('No address of the device was found. the NO is ' . $address, ErrorListModel::ERROR_LOGIN);
        }
    }

    /**
     * [heartbeat 心跳——数据操作]
     * @param  string $string [description]
     * @return [type]         [description]
     */
    public function heartbeat($string = ''){
        /**
         * 字符串是否为空
         */
        $address = base_convert(substr($string, 6, 4), 16, 10);
        /**
         * crc验证是否正确
         */
        // $crc = base_convert(substr($string, 0, -4), 16, 10);
        /**
         * 通过装备地址标识获取对应的Id
         */
        $info = M('equipment_list')->where(['equipment_address' => $address, 'is_del' => 1])->field('id')->find();
        /**
         * 标识不存在的
         */
        if(empty($info)) ErrorListModel::insertInformation('No address of the device was found！', 2);
        else{
            $data['equipment_id'] = (int)$info['id'];//设备Id
            $data['createtime'] = (int)time();//创建时间
            $data['signal_percentage'] = base_convert(substr($string, 14, 2), 16, 10);//信号百分比
            $data['signal_percentage'] = empty($data['signal_percentage']) ? '' : $data['signal_percentage'];
            $data['container_type'] = base_convert(substr($string, 16, 2), 16, 10);//容器星号
            $data['container_type'] = empty($data['container_type']) ? '' : $data['container_type'];
            $data['container_use_percentage'] = base_convert(substr($string, 18, 2), 16, 10);//容器使用百分比
            $data['container_use_percentage'] = empty($data['container_use_percentage']) ? '' : (double)$data['container_use_percentage'];//容器使用百分比
            $data['temperature'] = base_convert(substr($string, 20, 4), 16, 10);//温度
            $data['temperature'] = empty($data['temperature']) ? '' : $data['temperature'];
            $data['humidity'] = base_convert(substr($string, 24, 4), 16, 10);//湿度
            $data['humidity'] = empty($data['humidity']) ? '' : (double)$data['humidity'];
            $data['dip_angle'] = base_convert(substr($string, 28, 4), 16, 10);//倾角
            $data['dip_angle'] = empty($data['dip_angle']) ? '' : (double)$data['dip_angle'];
            $data['latitude'] = (int)substr($string, -22, -14) . '.' . substr($string, -14, -6);//经度
            $data['longitude'] = (int)substr($string, -40, -32) . '.' . substr($string, -32, -24);//维度
            $data['heartbeat'] = $string;//心跳包数据

            /**
             * 检测该设备Id一小时之内是否插入过数据
             * 插入过：更新产生量
             * 未插入：插入数据
             */
            EquipmentStatisticsModel::createData($data['equipment_id'], $data['container_use_percentage']);

            /**
             * 插入数据包记录
             */
            CommandListModel::insertCommand($data);
        }
    }   
}