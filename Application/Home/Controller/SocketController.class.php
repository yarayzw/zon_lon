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
    private $ip = '0.0.0.0';//172.18.195.128  120.79.183.103   127.0.0.1
    private $port = 8792;
    private $clients = [];
    const FRAME_HEADER = 'EB90';
    const TRASH_TERMINAL = '01';
    const LOGIN = '01';
    const HEART_JUMP = '02';
    const CHECK_TIME = '07';
    const READ_MEASUREMENT = '03';
    const READ_QUANTITATIVE = '04';
    const WRITE_QUANTITATIVE = '06';

    // 连接socket
    public function connectSock()
    {
        set_time_limit(0);
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $this->clients[] = $socket;
        if ($socket == false) {
            ErrorListModel::insertInformation('server create fail:' . socket_strerror(socket_last_error()));
        }
        if (socket_bind($socket, $this->ip, $this->port) == false) {
//           ErrorListModel::insertInformation('server bind fail:' . socket_strerror(socket_last_error()));
        }
        // 监听套接流
        if (socket_listen($socket, 4) == false) {
//            ErrorListModel::insertInformation('server listen fail:' . socket_strerror(socket_last_error()));
        }
        // 非阻塞
        socket_set_block($socket);
        // 让服务器无限获取客户端传过来的信息
        $this->getInformation($socket);
    }


    public function getInformation($socket)
    {
        do {
            //socket_select对读写套子节的数字是引用，为了保证clients不被改变，拷贝一份。
            $read = $this->clients;
            $write = null;
            $expect = null;
            // 当没有套字节可以读写继续等待， 第四个参数为null为阻塞， 为0位非阻塞,为>0 为等待时间
            if (socket_select($read, $write, $expect, 0) < 1) continue;
            // 查看是否有新的连接
            if (in_array($socket, $read)) {
                $this->clients[] = $new_socket = socket_accept($socket);
                $key = array_search($socket, $read);
                unset($read[$key]);
            }
            // 便利所有可读取数据套子节然后广播消息
            foreach ($read as $read_sock) {
                $string = socket_read($read_sock, 1024);
                if ($string === false || $string == '') {
                    $key = array_search($read_sock, $this->clients);
                    socket_close($read_sock);
                    unset($this->clients[$key]);
                    continue;
                } else {
                    $crc_string = substr($string, -4);
                    $verify_crc = $this->crc16(substr($string, 8, -4));
                    if ($crc_string != $verify_crc) {
                        ErrorListModel::insertInformation('Incomplete data. the data is ' . $string);
                        continue;
                    }
                    $this->functionHandle($string, $read_sock);
                }
            }
        } while (true);
        /*socket_close的作用是关闭socket_create()或者socket_accept()所建立的套接流*/
        socket_close($socket);
    }

    /**
     * 截取16进制功能码 对方法进行分发
     * @param string $string
     * @param $accept_resource
     */
    public function functionHandle( $string, $accept_resource)
    {
        $fun_string = substr($string, 12, 2);
        switch ($fun_string) {
            case self::LOGIN:
                (new OperateController)->login($string, $accept_resource);
                break;
            case self::HEART_JUMP:
                (new OperateController)->heartbeat($string);
                break;
            case self::READ_MEASUREMENT:
                //TODO::读测量数据
                break;
            case self::READ_QUANTITATIVE:
                //TODO::读定值数据
                break;
            default:
                ErrorListModel::insertInformation('Getting the error of the function code. The function code is ' . $fun_string);
                break;
        }
    }
}