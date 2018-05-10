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
     * 登录
     * @param $string
     * @return null
     */
    public function login($string)
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

    /**
     * 发送socket分发处理
     */
    public function sendHandle()
    {
        $code = isset( $_POST['code']) ?  $_POST['code'] : '';
        if (! $code) $this->ajax_return(10001, '', '功能码缺失');
        $address_no = isset( $_POST['address_no']) ?  $_POST['address_no'] : '';
        if (! $address_no) $this->ajax_return(10001, '', '设备号缺失');
        $type = isset( $_POST['type']) ?  $_POST['type'] : '';
        if (! $type && $type != 0) $this->ajax_return(10001, '', '类型缺失');
        switch ($code) {
            case SocketController::READ_MEASUREMENT:
                $this->sendMeasurement($address_no, $type, 'measurement');
                break;
            case SocketController::READ_QUANTITATIVE:
                //TODO::读定值数据
                break;
            default:
                break;
        }
    }

    /**
     * 读遥测
     * @param string | int $address_no  地址
     * @param string | int $type        类型
     * @param string $prefix            前缀
     */
    private function sendMeasurement($address_no, $type, $prefix)
    {
        // 有效数据
        $type = strlen(dechex(floor($type % 256))) < 2  ? '0' . dechex($type % 256)  : dechex($type % 256);
        // 组装数据
        $post_data = $this->montageData($address_no, $type);
        $this->sendSocketCustomer($post_data, $prefix);
    }

    /**
     * 向服务器端发送信息
     * @param $message
     * @param $prefix
     */
    private function sendSocketCustomer($message, $prefix)
    {
        // 创建一个socket套接流
        $socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
        // 连接服务端的套接流，这一步就是使客户端与服务器端的套接流建立联系
        if(socket_connect($socket,'127.0.0.1',8792) == false) {
            $this->ajax_return(10001, '', '连接服务端失败:' . socket_strerror(socket_last_error()));
        } else {
            // 向服务端写入字符串信息
            if (socket_write($socket,$prefix . $message, strlen($prefix . $message)) == false) {
                $this->ajax_return(10001, '', '写入服务端失败');
            } else {
                $this->ajax_return(10000, '', '写入服务端成功,等待数据处理');
            }
        }
        // 工作完毕，关闭套接流
        socket_close($socket);
    }

    /**
     * 数据十进制转换为四位十六进制
     * @param $change_string
     * @return string
     */
    public function changeDecimal($change_string)
    {
        $more_data = strlen(dechex(floor($change_string % 256))) < 2  ? '0' . dechex($change_string % 256)  : dechex($change_string % 256);
        $less_data = strlen(dechex(floor($change_string / 256))) < 2  ? '0' . dechex($change_string / 256)  : dechex($change_string / 256);
        return $more_data . $less_data;
    }

    // 读定值获取到的数据处理
    public function analysisReadMeasurement($string)
    {
        $content = substr($string,  18, -4);
        


    }

    /**
     * 组合发往客户端的数据
     * @param $address_no
     * @param $data
     * @return string
     */
    public function montageData($address_no, $data)
    {
        // 地址10进制转换为四位16进制
        $address_six_teen = $this->changeDecimal($address_no);
        // 数据长度
        $data_length = strlen($data) / 2;
        $data_length = strlen(dechex(floor($data_length % 256))) < 2  ? '0' . dechex($data_length % 256)  : dechex($data_length % 256);
        // 预测数据
        $ready_data = $address_six_teen . SocketController::READ_MEASUREMENT . $data_length . $data;
        $crc_string = self::crc16($ready_data);
        $frame_length = strlen($ready_data . $crc_string) / 2;
        $frame_length = strlen(dechex(floor($frame_length % 256))) < 2  ? '0' . dechex($frame_length % 256)  : dechex($frame_length % 256);
        $post_data = '<TX' . SocketController::FRAME_HEADER . SocketController::TRASH_TERMINAL . $frame_length . $address_six_teen . SocketController::READ_MEASUREMENT . $data_length . $data . $crc_string . '>';
        return $post_data;
    }
}