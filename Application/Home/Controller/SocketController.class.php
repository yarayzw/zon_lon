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
    const FRAME_HEADER = 'EB90';      // 帧头
    const TRASH_TERMINAL = '01';      // 服务id 垃圾
    const LOGIN = '01';               // 登录标识
    const HEART_JUMP = '02';          // 心跳标识
    const CHECK_TIME = '07';          // 对时标识
    const READ_MEASUREMENT = '03';    // 读遥测
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
           ErrorListModel::insertInformation('server bind fail:' . socket_strerror(socket_last_error()));
        }
        // 监听套接流
        if (socket_listen($socket, 4) == false) {
            ErrorListModel::insertInformation('server listen fail:' . socket_strerror(socket_last_error()));
        }
        // 非阻塞
        socket_set_block($socket);
        // 让服务器无限获取客户端传过来的信息
        do {
            $msg = $this->getInformation($socket);
            if ($msg == 'continue') {
                unset($msg);
                continue;
            }
        } while (true);
        /*socket_close的作用是关闭socket_create()或者socket_accept()所建立的套接流*/
        socket_close($socket);
    }


    /**
     * socket获取信息
     * @param $socket
     * @return string
     */
    public function getInformation($socket)
    {
        // socket_select对读写套子节的数字是引用，为了保证clients不被改变，拷贝一份。
        $read = $this->clients;
        $write = null;
        $expect = null;
        // 当没有套字节可以读写继续等待， 第四个参数为null为阻塞， 为0位非阻塞,为>0 为等待时间
        if (socket_select($read, $write, $expect, 0) < 1) return 'continue';
        // 查看是否有新的连接
        if (in_array($socket, $read)) {
            $new_socket = socket_accept($socket);
            $string = socket_read($new_socket, 1024);
            var_dump( date('Y-m-d H:i:s') . ' ' . $string);
            // 判断是后台发送 还是 设备主动发送
            if (strpos($string, '<TX') === false) {
                $this->clients[hexdec(substr($string, 8, 4))] = $new_socket;
            } else {
                $this->clients[substr($string, 0, strpos($string, '<TX'))] = $new_socket;
            }
            $key = array_search($socket, $read);
            unset($read[$key]);
            // 处理数据 后台主动请求和接收数据
            $this->analysisInformation($string, $new_socket);
            return 'continue';
        } else {
            // 便利所有可读取数据套子节然后广播消息
            foreach ($read as $read_sock) {
                $string = socket_read($read_sock, 1024);
                var_dump( date('Y-m-d H:i:s') . ' ' . $string);
                if ($string === false || $string == '') {
                    $key = array_search($read_sock, $this->clients);
                    socket_close($read_sock);
                    unset($this->clients[$key]);
                    continue;
                } else {
                    // 处理数据 后台主动请求和接收数据
                    $this->analysisInformation($string, $read_sock);
                    continue;
                }
            }
        }
    }

    /**
     * 截取16进制功能码 对方法进行分发
     * @param string $string
     * @param $accept_resource
     */
    public function functionHandle($string, $accept_resource)
    {
        $fun_string = substr($string, 12, 2);
        switch ($fun_string) {
            case self::LOGIN:
                (new OperateController)->login($string);
                break;
            case self::HEART_JUMP:
                (new OperateController)->heartbeat($string);
                break;
            case self::READ_MEASUREMENT:
                (new OperateController())->analysisReadMeasurement($string);
                break;
            case self::READ_QUANTITATIVE:
                //TODO::读定值数据
                break;
            default:
                ErrorListModel::insertInformation('Getting the error of the function code. The function code is ' . $fun_string);
                break;
        }
    }

    /**
     * 处理获取到的socket数据
     * @param $string
     * @param $read_sock
     * @return string
     */
    private function analysisInformation($string, $read_sock)
    {
        // 为前端主动发送数据 目的往对应的资源发送数据
        if (strpos($string, '<TX') !== false) {
            $send_socket = null;
            $message = substr($string, strpos($string, '<TX'));
            $address_no = hexdec(substr($message, 11, 4));
            // 获取当前需要的socket资源
            foreach ($this->clients as $k => $v) {
                if ($k == $address_no) $send_socket = $v;
            }
            if (! $send_socket) {
                echo json_encode(array('code' => 10001, 'content' => '', 'msg' => '该设备已掉线'));
                return 'continue';
            }
            if (socket_write($send_socket, $message, strlen($message)) === false) {
                echo json_encode(array('code' => 10001, 'content' => '', 'msg' => '发送请求信息失败'));
                return 'continue';
            }
        }
        // 验证crc校验码
        $crc_string = substr($string, -4);
        $verify_crc = $this->crc16(substr($string, 8, -4));
        if ($crc_string != $verify_crc) {
            ErrorListModel::insertInformation('Incomplete data. the data is ' . $string);
            return 'continue';
        }
        // 对不同功能码进行数据分发
        $this->functionHandle($string, $read_sock);
    }
}