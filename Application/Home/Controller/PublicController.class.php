<?php
/**
 * Created by PhpStorm.
 * User: shawn
 * Date: 2018/4/27
 * Time: 17:49
 */

namespace Home\Controller;


use Think\Controller;

class PublicController extends Controller
{
    protected function http_post($url, $post_data)
    {
        $post_string = json_encode($post_data);
        $header = array(
            'Content-type: application/json;charset=utf-8',
            'Content-Length: ' . strlen($post_string)
        );
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $data = curl_exec($curl);
        curl_close($curl);
        return $data;
    }

    protected function http_get($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $data = curl_exec($curl);
        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $info = substr($data, $headerSize);
        curl_close($curl);
        return $info;
    }

    protected function ajax_return($status, $data, $msg = '', $type = '')
    {
        if (empty($type)) $type = C('DEFAULT_AJAX_RETURN');
        switch (strtoupper($type)) {
            case 'JSON' :
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                $data = array('code' => $status, 'content' => $data, 'msg' => $msg);
                exit(json_encode($data));
            case 'XML'  :
                // 返回xml格式数据
                header('Content-Type:text/xml; charset=utf-8');
                exit(xml_encode($data));
            case 'JSONP':
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                $handler = isset($_GET[C('VAR_JSONP_HANDLER')]) ? $_GET[C('VAR_JSONP_HANDLER')] : C('DEFAULT_JSONP_HANDLER');
                exit($handler . '(' . json_encode($data) . ');');
            case 'EVAL' :
                // 返回可执行的js脚本
                header('Content-Type:text/html; charset=utf-8');
                exit($data);
        }
    }

    /**
     * 生成数据CRC验证码
     * @param $string
     * @return int
     */
    protected static function crc16($string)
    {
        $string = pack('H*', $string);
        $crc = 0xFFFF;
        for ($x = 0; $x < strlen($string); $x++) {
            $crc = $crc ^ ord($string[$x]);
            for ($y = 0; $y < 8; $y++) {
                if (($crc & 0x0001) == 0x0001) {
                    $crc = (($crc >> 1) ^ 0xA001);
                } else {
                    $crc = $crc >> 1;
                }
            }
        }
        $data = dechex($crc % 256) . dechex(floor($crc / 256));
        return strtoupper($data);
    }

    /**
     * [string_before_add 原字符串中，指定位置插入新的字符串]
     * @param  string $string [原字符串]
     * @param  int $length [指定位置]
     * @param string $add_string
     * @return string [type]            [description]
     */
    protected function string_before_add( $string,  $length,  $add_string){
        /**
         * 如果位置是负数的话，那么找出对应的正整数位置
         */
        if((int)$length < 0) $length = strlen($string) - strlen(substr($string, $length));
        //指定插入位置前的字符串
        $startstr="";
        for($j = 0; $j < $length; $j++) $startstr .= $string[$j];

        //指定插入位置后的字符串
        $laststr="";
        for ($j = $length; $j < strlen($string); $j++) $laststr .= $string[$j];
         
        //将插入位置前，要插入的，插入位置后三个字符串拼接起来
        $new_string = $startstr . $add_string . $laststr;
         
        //返回结果
        return $new_string;
    }

    /**
     * [is_timestamp 时间戳格式是否正确]
     * @param  [type]  $timestamp [description]
     * @return boolean            [description]
     */
    protected function is_timestamp($timestamp) {
        if(strtotime(date('m-d-Y H:i:s',$timestamp)) === $timestamp) return $timestamp;
        else return false;
    }
}