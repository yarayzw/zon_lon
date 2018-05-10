<?php
/**
 * Created by PhpStorm.
 * User: shawn
 * Date: 2018/5/10
 * Time: 11:07
 */

namespace Home\Model;


use Think\Model;

class MeasurementListModel extends Model
{
    const TYPE_LENGTH = 2;                  // 类型长度
    const ALL = '00';                       // 所有状态
    const BATTERY_PERCENT = '01';           // 电池电量百分比
    const BATTERY_PERCENT_LENGTH = 2;       // 电池电量百分比长度
    const TERMINAL_SIGNAL = '02';           // 终端信号强度
    const TERMINAL_SIGNAL_LENGTH = 2;       // 终端信号强度长度
    const CONTAINER_TYPE = '03';            // 容器型号
    const CONTAINER_TYPE_LENGTH = 2;        // 容器型号长度
    const CONTAINER_PERCENT = '04';         // 容器使用百分比
    const CONTAINER_PERCENT_LENGTH = 2;     // 容器使用百分比长度
    const TEMPERATURE = '05';               // 温度
    const TEMPERATURE_LENGTH = 4;           // 温度长度
    const HUMIDITY_PERCENT = '06';          // 湿度百分比
    const HUMIDITY_PERCENT_LENGTH = 4;      // 湿度百分比长度
    const OBLIQUITY = '07';                 // 倾角
    const OBLIQUITY_LENGTH = 4;             // 倾角长度
    const LATITUDE = '08';                  // 维度
    const LATITUDE_LENGTH = 16;              // 维度长度
    const NORTH_SOUTH_LATITUDE = '09';      // 南北纬
    const NORTH_SOUTH_LATITUDE_LENGTH = 2;  // 南北纬长度
    const LONGITUDE = '0A';                 // 经度
    const LONGITUDE_LENGTH = 16;             // 经度长度
    const EAST_WEST_LONGITUDE = '0B';       // 东西经
    const EAST_WEST_LONGITUDE_LENGTH = 2;   // 东西经长度


    /**
     * 插入数据
     * @param $data
     * @return bool
     */
    public static function insertMeasurementInformation($data)
    {
        $model = M('measurement_list');
        return $model->data($data)->add();
    }

    /**
     * 根据地址获取未读的信息
     * @param $address_no
     * @return mixed
     */
    public static function getModelByAddressAndNoRead($address_no)
    {
        $model = M('measurement_list');
        $condition = [
            'address_no' => $address_no,
            'is_read' => 0,
        ];
        return $model->where($condition)->select();
    }

    /**
     * 将未读改为已读
     * @param $id
     * @return bool
     */
    public static function changeStatusById($id)
    {
        $model = M('measurement_list');
        $condition = ['id' => $id];
        $new_data = ['is_read' => 1];
        return $model->where($condition)->save($new_data);
    }

}