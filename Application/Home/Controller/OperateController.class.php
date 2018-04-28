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

class OperateController extends PublicController
{
    public static function login($string, $socket) {
        $address_six_teen = substr($string, 6, 4);
        $address = base_convert($address_six_teen, 16, 10);
        EquipmentListModel::getModelByAddressNo((string)$address);
        $year = date('Y');
        $data_content = $address_six_teen . SocketController::CHECK_TIME . dechex($year % 256) . dechex(floor($year / 256)) . dechex(date('m')) . dechex(date('d')) . dechex(date('H')) . dechex(date('i')) . dechex(date('s'));
        $crc_string = self::crc16($data_content);
        $frame_length  = dechex(strlen($data_content . $crc_string) / 2);
        $post_data = "<TXEB90{$frame_length}{$data_content}{$crc_string}>";
        if (socket_write($socket, $post_data, strlen($post_data)) === false) {
            ErrorListModel::insertInformation('Sending time to failure. the data is ' . $post_data, 3);
        }
    }

}