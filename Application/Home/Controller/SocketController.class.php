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
    private $ip = '127.0.0.1';
    private $port = 8888;
    private $heart_time = 5 * 60;
    private $last_time = '';

    // 连接socket
    public function connectSock()
    {
        set_time_limit(0);
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (socket_bind($socket, $this->ip, $this->port) == false) {
           ErrorListModel::insertInformation('server bind fail:' . socket_strerror(socket_last_error()));
        }
        // 监听套接流
        if (socket_listen($socket, 4) == false) {
            ErrorListModel::insertInformation('server listen fail:' . socket_strerror(socket_last_error()));
        }
        $this->last_time = time();
        // 让服务器无限获取客户端传过来的信息
        $this->getInformation($socket);
    }


    public function getInformation($socket)
    {
        do {
            if (time() - $this->last_time >= $this->heart_time) {
                socket_close($socket);
                $error_info = 'Get a heartbeat timeout';
                ErrorListModel::insertInformation($error_info);
                break;
            }
            /*接收客户端传过来的信息*/
            $accept_resource = socket_accept($socket);
            /*socket_accept的作用就是接受socket_bind()所绑定的主机发过来的套接流*/
            if ($accept_resource !== false) {
                /*读取客户端传过来的资源，并转化为字符串*/
                $string = socket_read($accept_resource, 1024);
                /*socket_read的作用就是读出socket_accept()的资源并把它转化为字符串*/
                if ($string != false) {
                    $this->functionHandle($string);
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
     * @return string
     */
    public function functionHandle($string)
    {
        $fun_string = substr($string, 10, 2);
        switch ($fun_string) {
            case '01':
                OperateController::login($string);
                break;
            case '02':
                $this->last_time = time();
                OperateController::fun();
                break;
            default:
                ErrorListModel::insertInformation('Getting the error of the function code');
                return '';
        }

    }

}