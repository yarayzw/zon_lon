<?php
    //socket服务端开启
    curl_url('http://127.0.0.1/index.php/Home/Socket/connectSock.html');

    /**
     * [curl_url curl请求地址操作]
     * @param  string $url [description]
     * @return [type]      [description]
     */
    function curl_url($url = '')
    {
        $curl = curl_init($url);
        
        curl_setopt ( $curl, CURLOPT_CONNECTTIMEOUT, 0 );
        curl_setopt ( $curl, CURLOPT_CONNECTTIMEOUT_MS, 0 );
        curl_setopt ( $curl, CURLOPT_POST, 1 );  
        curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );  
        curl_setopt ( $curl, CURLOPT_TIMEOUT, 0);
        curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, false);  
        curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, false);  
        curl_setopt ( $curl, CURLOPT_HEADER, true);  
                  
        $response = curl_exec($curl);
        $response = false;
        if($response === false)  
        {  
            if(curl_errno($curl) == CURLE_OPERATION_TIMEDOUT) curl_url();//超时处理操作
        }
    }


// //初始化
//    $curl = curl_init();
//    //设置抓取的url
//    curl_setopt($curl, CURLOPT_URL, 'http://127.0.0.1/index.php/Home/Socket/connectSock.html');
//    //设置头文件的信息作为数据流输出
//    curl_setopt($curl, CURLOPT_HEADER, 1);
//    //设置获取的信息以文件流的形式返回，而不是直接输出。
//    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//    //执行命令
//    $data = curl_exec($curl);
//    //关闭URL请求
//    curl_close($curl);
//    //显示获得的数据
//    print_r($data);