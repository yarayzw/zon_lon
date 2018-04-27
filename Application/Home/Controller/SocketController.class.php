<?php
/**
 * Created by PhpStorm.
 * User: shawn
 * Date: 2018/4/27
 * Time: 17:49
 */

namespace Home\Controller;

class SocketController extends PublicController
{
    private $ip = '127.0.0.1';
    private $port = 8888;
    private $heart_time = 5 * 60;
    private $last_time = '';
    
    public function connectSock()
    {
        set_time_limit(0);
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (socket_bind($socket, $this->ip, $this->port) == false) {
            echo 'server bind fail:'.socket_strerror(socket_last_error());
        }
        //监听套接流
        if(socket_listen($socket,4) == false){
            echo 'server listen fail:' . socket_strerror(socket_last_error());
        }
        $this->last_time = time();
        //让服务器无限获取客户端传过来的信息
        do {
            $this->getInformation($socket);
        } while (true);

    }

    public function getInformation($socket)
    {

        /*接收客户端传过来的信息*/
        $accept_resource = socket_accept($socket);
        /*socket_accept的作用就是接受socket_bind()所绑定的主机发过来的套接流*/
        if ($accept_resource !== false) {
            /*读取客户端传过来的资源，并转化为字符串*/
            $string = socket_read($accept_resource, 1024);
            /*socket_read的作用就是读出socket_accept()的资源并把它转化为字符串*/
            if ($string != false) {
                $fun_string = substr($string, 10, 2);
                switch ($fun_string) {
                    case '01':
                        //TODO:: 登录
                        break;
                    case '02':
                        //TODO::心跳
                    default:
                        return '';
                }
                /*向socket_accept的套接流写入信息，也就是回馈信息给socket_bind()所绑定的主机客户端*/
//                socket_write($accept_resource, $return_client, strlen($return_client));
                /*socket_write的作用是向socket_create的套接流写入信息，或者向socket_accept的套接流写入信息*/
            } else {
                echo 'socket_read is fail';
            }
            /*socket_close的作用是关闭socket_create()或者socket_accept()所建立的套接流*/
            socket_close($accept_resource);
        }
    }

    /**
     * 对数据进行拆分
     * @param $str
     * @param $arr
     * @return array
     */
    function split_str($str, $arr) {
        $new_arr = [];
        $index = 0;
        for ($i = 0; $i < count($arr); $i++) {
            $new_arr[] = hexdec(substr($str, $index, $arr[$i]));
            $index += $arr[$i];
        }
        return $new_arr;
    }
}