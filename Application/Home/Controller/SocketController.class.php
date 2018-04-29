<?php
/**
 * Created by PhpStorm.
 * User: shawn
 * Date: 2018/4/27
 * Time: 17:49
 */

namespace Home\Controller;

use Home\Model\ErrorListModel;

class SocketController extends PublicController
{
    private $ip = '0.0.0.0';//172.18.195.128  120.79.183.103
    private $port = 8792;
    const FRAME_HEADER = 'EB90';
    const LOGIN = '01';
    const HEART_JUMP = '02';
    const CHECK_TIME = '07';
    const READ_MEASUREMENT = '03';
    const READ_QUANTITATIVE = '04';
    const WRITE_QUANTITATIVE = '06';

    // 连接socket
    public function connectSock()
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("Could not create socket.\n");
        if (socket_bind($socket, $this->ip, $this->port) == false) {
           ErrorListModel::insertInformation('server bind fail:' . '（'.json_encode($socket).'）' . socket_strerror(socket_last_error()));
        }
        // 监听套接流
        if (socket_listen($socket, 4) == false) {
            ErrorListModel::insertInformation('server listen fail:' . socket_strerror(socket_last_error()));
        }
        // 让服务器无限获取客户端传过来的信息
        $this->getInformation($socket);
    }


    public function getInformation($socket)
    {
        do {
            /*接收客户端传过来的信息*/
            $accept_resource = socket_accept($socket);
            /*socket_accept的作用就是接受socket_bind()所绑定的主机发过来的套接流*/
            if ($accept_resource !== false) {
                /*读取客户端传过来的资源，并转化为字符串*/
                $string = str_replace('\\x', '', socket_read($accept_resource, 4096));
                // print_r($string);
                // 验证crc校验码是否正确
                $verify_string = substr($string, 6, -4);
                $crc_string = substr($string, -4);
                $verify_crc = $this->crc16($verify_string);
                // if ($crc_string != $verify_crc) ErrorListModel::insertInformation('Incomplete data. the data is ' . $string);
                /*socket_read的作用就是读出socket_accept()的资源并把它转化为字符串*/
                if ($string != false) {
                    $this->functionHandle($string, $socket);
                } else {
                    ErrorListModel::insertInformation('socket_read is fail');
                }
                /*socket_close的作用是关闭socket_create()或者socket_accept()所建立的套接流*/
                socket_close($accept_resource);
            }
        } while (true);
    }

    /**
     * 截取16进制功能码 对方法进行分发
     * @param $string
     * @param $socket
     * @return string
     */
    public function functionHandle(string $string, $socket)
    {
        $fun_string = substr($string, 10, 2);
        switch ($fun_string) {
            case self::LOGIN:
                OperateController::login($string, $socket);
                break;
            case self::HEART_JUMP:
                (new OperateController())->heartbeat($string);
                break;
            case self::READ_MEASUREMENT:
                //TODO::读测量数据
                break;
            case self::READ_QUANTITATIVE:
                //TODO::读定值数据
                break;
            default:
                ErrorListModel::insertInformation('Getting the error of the function code');
                return '';
        }

    }

}