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
use Home\Model\CommanListModel;

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
        $address_six_teen = substr($string, 6, 4);
        $address = base_convert($address_six_teen, 16, 10);
        // 判断数据库中是否该设备 没有则报错
        $data = EquipmentListModel::getModelByAddressNo($address);
        if (!$data) {
            ErrorListModel::insertInformation('No address of the device was found. the NO is ' . $address, ErrorListModel::ERROR_LOGIN);
            socket_close($accept_resource);
        } else {
            // 有效数据包
            $data_content = dechex(date('Y') % 256) . dechex(floor(date('Y') / 256)) . dechex(date('m')) . dechex(date('d')) . dechex(date('H')) . dechex(date('i')) . dechex(date('s'));
            // 数据长度计算
            $data_length = dechex(strlen($data_content) / 2);
            // 组合对时数据包 地址 功能码 数据长度
            $data_content = $address_six_teen . SocketController::CHECK_TIME . $data_length .  dechex(date('Y') % 256) . dechex(floor(date('Y') / 256)) . dechex(date('m')) . dechex(date('d')) . dechex(date('H')) . dechex(date('i')) . dechex(date('s'));
            $crc_string = self::crc16($data_content);
            // 计算帧长
            $frame_length  = dechex(strlen($data_content . $crc_string) / 2);
            $frame_header = SocketController::FRAME_HEADER;
            // 拼接数据 帧头 帧长 数据包 crc校验码
            $post_data = "<TX{$frame_header}{$frame_length}{$data_content}{$crc_string}>";
            if (socket_write($accept_resource, strtoupper($post_data), strlen($post_data)) === false) {
                ErrorListModel::insertInformation('Sending time to failure. the data is ' . strtoupper($post_data) . ' return message: ' . socket_strerror(socket_last_error()), ErrorListModel::ERROR_CHECK_TIME );
            }
        }
    }

    /**
     * [heartbeat 心跳——数据操作]
     * @param  string $string [description]
     * @return [type]         [description]
     */
    public function heartbeat($string = ''){
        $string = 'EB901A000202xx300300420060000701014E14FB18C4506BE15F0XXXX';
        /**
         * 字符串是否为空
         */
        $address = base_convert(substr($string, 6, 4), 16, 10);
        /**
         * crc验证是否正确
         */
        $crc = base_convert(substr($string, 0, -4), 16, 10);
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
            $data['signal_percentage'] = empty($data['signal_percentage']) ? '' : $data['signal_percentage'];
            $data['container_type'] = base_convert(substr($string, 16, 2), 16, 10);//容器星号
            $data['container_type'] = empty($data['container_type']) ? '' : $data['container_type'];
            $data['container_use_percentage'] = base_convert(substr($string, 18, 4), 16, 10);//容器使用百分比
            $data['container_use_percentage'] = empty($data['container_use_percentage']) ? '' : (double)$data['container_use_percentage'];//容器使用百分比
            $data['temperature'] = base_convert(substr($string, 22, 4), 16, 10);//温度
            $data['temperature'] = empty($data['temperature']) ? '' : $data['temperature'];
            $data['humidity'] = base_convert(substr($string, 26, 4), 16, 10);//湿度
            $data['humidity'] = empty($data['humidity']) ? '' : (double)$data['humidity'];
            $data['dip_angle'] = base_convert(substr($string, 30, 4), 16, 10);//倾角
            $data['dip_angle'] = empty($data['dip_angle']) ? '' : (double)$data['dip_angle'];
            $data['latitude'] = base_convert(substr($string, 45, 8), 16, 10);//经度
            $data['latitude'] = empty($data['latitude']) ? '' : $data['latitude'];
            $data['latitude'] = $this->string_before_add($data['latitude'], -6, '.');
            $data['longitude'] = base_convert(substr($string, 36, 7), 16, 10);//维度
            $data['longitude'] = empty($data['longitude']) ? '' : $data['longitude'];
            $data['longitude'] = $this->string_before_add($data['longitude'], -6, '.');
            $data['heartbeat'] = json_encode($data);//心跳包数据
            CommanListModel::insertCommand($data);
        }
    }

    /**
     * [data_statistics 数据统计]
     * @return [type] [description]
     *
     * 需要展示数据：
     * 容器类型：长宽高
     */
    public function data_statistics(){
        $data = I('request.');
        $where_data['start_time'] = !$this->is_timestamp($data['start_time']) ? 0 : $data['start_time'];
        $where_data['end_time'] = !$this->is_timestamp($data['end_time']) ? 0 : $data['end_time'];
        var_export($where_data);
    }

}