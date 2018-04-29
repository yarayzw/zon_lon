<?php

/**
 * 检查权限
 * */
function check_auth($auth){
    if($_SESSION['admin']['id']==1){
        return 1;
    }else{
        $user_auth = $_SESSION['user_auth']['rules'];
        $is_auth = explode(',',$user_auth);
        $data= in_array($auth,$is_auth) ? 1 : 2 ;
        return $data;
    }
}

/**
 * 生成发送命令唯一标识
 */
function CreateIdentifyNum($start="",$end=""){
    $ycode=array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
    $orderSn=$ycode[intval(date("Y"))-2017].strtoupper(dechex(date('m'))).date("d").substr(time(),-5)
        .substr(microtime(),2,5).sprintf("%02d",rand(0,99));
    return $start.$orderSn.$end;
}





/**
 * 获取一段随机的字符串
 * @param type $length 长度
 * @return string
 */
function GetRandChar($length){
    $str = null;
    $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
    $max = strlen($strPol)-1;
    for($i=0;$i<$length;$i++){
        $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
    }
    return $str;
}
/**
 * crypt加密传入字符串
 */
function CreateRandom($str){
    if(empty($str)){
        $str=  getRandChar(15);
    }
    else{
        $str=$str.getRandChar(15);
    }
    return md5(md5($str));
}
/**
 *  将Base64 格式图片 解码为图片文件 并保存
 * @param $pic  图片的base64编码
 * @param string $path 保存图片的主文件夹
 * @param string $param 保存文件名的额外标识 高并发时文件名唯一
 */
function Base64ToImg($pic,$path_root="",$param=""){
    if(strlen($pic)<=0){
        return false;
    }
    if(preg_match('/^(data:\s*image\/(\w+);base64,)/',$pic,$result)){
        $ext=$result[2];
        $root_server="Uploads/";
        if(empty($path_root))
            $path=date("Y/m/");
        else
            $path=$path_root."/".date("Y/m/");
        $filename=time().GetRandChar(8);
        $filename=CreateRandom($filename).".".$ext;
        if(!file_exists($root_server.$path)){
            mkdir($root_server.$path,0777,true);
        }
        $temp=base64_decode(str_replace($result[1],"",$pic));
        $s=file_put_contents($root_server.$path.$filename,$temp);
        return $root_server.$path.$filename;
    }else{
        return false;
    }
}
/**
 * 组合二维数组元素为字符串
 * @param string $separator 分割符
 * @param $arr  要组合的数组
 * @param string $field 多维时，要组合的数组元素
 */
function myjoin($separator="",$arr,$field){
    if($arr){
        $tem=array();
        foreach ($arr as $key=>$value){
            $tem[]=$value[$field];
        }
        return join($separator,$tem);
    }
    return "";
}
/**
 * 验证字符串是否为手机号码
 * @param type $phone
 */
function isMobilePhone($phone){
    $isMatched = preg_match('/(13\d|14[57]|15[^4,\D]|17[678]|18\d)\d{8}|170\d{8}/', $phone);
    return $isMatched;
}
const GLOBAL_SYSTEMCONFIGKEY="global_glsysconfig";
/**
 * 获取系统配置
 * @param string $key 配置键名
 */
function GetConfig($syskey=""){
    $data_config=F(GLOBAL_SYSTEMCONFIGKEY);
    if($data_config){
        if(strlen($syskey)>0)
            return $data_config[$syskey];
        else
            return $data_config;
    }else{
        $db_confg=D("config");
        $data_config=$db_confg->select();
        if($data_config){
            $arr_config=array();
            foreach ($data_config as $key=>$value){
                $arr_config[$value["syskey"]]=$value["sysvalue"];
            }
            F(GLOBAL_SYSTEMCONFIGKEY,$arr_config);
            if(strlen($syskey)>0)
                return $arr_config[$syskey];
            else
                return $arr_config;
        }else{
            return "";
        }
    }
}

/**
 * 设置系统配置
 * @param array $config
 */
function UpdateConfig(){
    $db_confg=D("config");
    $data_config=$db_confg->select();
    if($data_config){
        $data=array();
        foreach ($data_config as $key=>$value){
            $data[$value["syskey"]]=$value["sysvalue"];
        }
        F(GLOBAL_SYSTEMCONFIGKEY,$data);
    }
}

/**
 *删除系统配置缓存
 */
function DeleteConfig(){
    F(GLOBAL_SYSTEMCONFIGKEY,null);
}
/**
 * 字符串截取，支持中文和其他编码
 * @static
 * @access public
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 * @return string
 */
function msubstr($str, $start, $length, $charset="utf-8", $suffix=true) {
    if(function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
    elseif(function_exists('iconv_substr')) {
        $slice = iconv_substr($str,$start,$length,$charset);
        if(false === $slice) {
            $slice = '';
        }
    }else{
        $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("",array_slice($match[0], $start, $length));
    }
    return $suffix ? $slice.'...' : $slice;
}
/**
 *  公用分页数据，总数，每页数，搜索值等
 *  @param  int  $count  总数
 *  @param  int  $mpage  每页数据
 *  @param  array  $array    搜索条件
 *  return  array  返回方法对象数组
 */
function pages($count,$mpage=10,$array=''){
    $Page =  new \Think\Page($count,$mpage);// 实例化分页类 传入总记录数和每页显示的记录数(25)
    //分页跳转的时候保证查询条件
    foreach($array as $key=>$val) {
        $Page->parameter[$key]   =   urlencode($val);
    }
    $Page->setConfig('first','首页');
    $Page->setConfig('last','尾页');
    $Page->setConfig('theme','<ul class="pagination"><li>%FIRST%</li> <li>%UP_PAGE%</li> <li>%LINK_PAGE%</li> <li>%DOWN_PAGE%</li> <li>%END%</li></ul>');
    $show  = $Page->show();// 分页显示输出

    return array($Page,$show);
}
//加密方式
function mymd5($string){
    $one = md5($string);
    $two = md5(substr($one,0,16));
    $three = md5(substr($one,16,16));
    $four = md5($two.$three);
    return $four;
}
/** 加密方式
 * @param  $str   传入字符串
 * @param  $privatekey    私钥字串
 * @param  $publickey   公钥字串，默认为公共 config 配置文件内 DATA_AUTH_KEY
 * @return   String
 */
function base64encrypt($str,$privatekey='',$publickey=PUBLICKEY){
    $info2=new \Think\Crypt\Driver\Base64();
    $tt=$info2->encrypt($str,$privatekey);
    $tts=$info2->encrypt($tt,$publickey);
    return $tts;
}
/** 解密方式
 * @param  $str   传入字符串
 * @param  $privatekey    私钥字串
 * @param  $publickey   公钥字串，默认为公共 config 配置文件内 DATA_AUTH_KEY
 * @return   String
 */
function base64decrypt($str,$privatekey='',$publickey=PUBLICKEY){
    $info2=new \Think\Crypt\Driver\Base64();
    $tt=$info2->decrypt($str,$publickey);
    $tts=$info2->decrypt($tt,$privatekey);
    return $tts;
}


/*
*功能：对字符串进行加密处理
*参数一：需要加密的内容
*参数二：密钥
*/
function passport_encrypt($str,$key){ //加密函数
 srand((double)microtime() * 1000000);
 $encrypt_key=md5(rand(0, 32000));
 $ctr=0;
 $tmp='';
 for($i=0;$i<strlen($str);$i++){
  $ctr=$ctr==strlen($encrypt_key)?0:$ctr;
  $tmp.=$encrypt_key[$ctr].($str[$i] ^ $encrypt_key[$ctr++]);
 }
 return base64_encode(passport_key($tmp,$key));
}
/*
*功能：对字符串进行解密处理
*参数一：需要解密的密文
*参数二：密钥
*/
function passport_decrypt($str,$key){ //解密函数
 $str=passport_key(base64_decode($str),$key);
 $tmp='';
 for($i=0;$i<strlen($str);$i++){
  $md5=$str[$i];
  $tmp.=$str[++$i] ^ $md5;
 }
 return $tmp;
}
/*
* 加密解密辅助函数
*/
function passport_key($str,$encrypt_key){
 $encrypt_key=md5($encrypt_key);
 $ctr=0;
 $tmp='';
 for($i=0;$i<strlen($str);$i++){
  $ctr=$ctr==strlen($encrypt_key)?0:$ctr;
  $tmp.=$str[$i] ^ $encrypt_key[$ctr++];
 }
 return $tmp;
}

/**
 * 验证图片路径
 * @param unknown $str
 */
function checkImgURL($str){
    if(preg_match("/^\/?(\w+\/)*\w+\.(bmp|jpg|gif|png|jpeg)$/", $str)){
        return true;
    }else{
        return false;
    }
}
/**
 * 验证日期格式
 * @param unknown $str
 */
function checkbirthday($str){
    if(preg_match("/[0-9]{4}-(((0[13578]|(10|12))-(0[1-9]|[1-2][0-9]|3[0-1]))|(02-(0[1-9]|[1-2][0-9]))|((0[469]|11)-(0[1-9]|[1-2][0-9]|30)))/",$str)){
        return true;
    }else{
        return false;
    }
}
/**
 * 验证是否为数字
 * @param type $type
 */
function checkNumber($str){
    if(is_numeric($str))
        return true;
    else
        return false;
}
// 检测输入的验证码是否正确，$code为用户输入的验证码字符串
function check_verify($code, $id = '1'){
    $verify = new \Think\Verify();
    return $verify->check($code, $id);
}
/**
+----------------------------------------------------------
 * 将一个字符串部分字符用*替代隐藏
+----------------------------------------------------------
 * @param string    $string   待转换的字符串
 * @param int       $bengin   起始位置，从0开始计数，当$type=4时，表示左侧保留长度
 * @param int       $len      需要转换成*的字符个数，当$type=4时，表示右侧保留长度
 * @param int       $type     转换类型：0，从左向右隐藏；1，从右向左隐藏；2，从指定字符位置分割前由右向左隐藏；3，从指定字符位置分割后由左向右隐藏；4，保留首末指定字符串
 * @param string    $glue     分割符
+----------------------------------------------------------
 * @return string   处理后的字符串
+----------------------------------------------------------
 */
function hideStr($string, $bengin=0, $len = 4, $type = 0, $glue = "@") {
    if (empty($string))
        return false;
    $array = array();
    if ($type == 0 || $type == 1 || $type == 4) {
        $strlen = $length = mb_strlen($string);
        while ($strlen) {
            $array[] = mb_substr($string, 0, 1, "utf8");
            $string = mb_substr($string, 1, $strlen, "utf8");
            $strlen = mb_strlen($string);
        }
    }
    if ($type == 0) {
        for ($i = $bengin; $i < ($bengin + $len); $i++) {
            if (isset($array[$i]))
                $array[$i] = "*";
        }
        $string = implode("", $array);
    }else if ($type == 1) {
        $array = array_reverse($array);
        for ($i = $bengin; $i < ($bengin + $len); $i++) {
            if (isset($array[$i]))
                $array[$i] = "*";
        }
        $string = implode("", array_reverse($array));
    }else if ($type == 2) {
        $array = explode($glue, $string);
        $array[0] = hideStr($array[0], $bengin, $len, 1);
        $string = implode($glue, $array);
    } else if ($type == 3) {
        $array = explode($glue, $string);
        $array[1] = hideStr($array[1], $bengin, $len, 0);
        $string = implode($glue, $array);
    } else if ($type == 4) {
        $left = $bengin;
        $right = $len;
        $tem = array();
        for ($i = 0; $i < ($length - $right); $i++) {
            if (isset($array[$i]))
                $tem[] = $i >= $left ? "*" : $array[$i];
        }
        $array = array_chunk(array_reverse($array), $right);
        $array = array_reverse($array[0]);
        for ($i = 0; $i < $right; $i++) {
            $tem[] = $array[$i];
        }
        $string = implode("", $tem);
    }
    return $string;
}
/**
 * 把返回的数据集转换成Tree
 * @param array $list 要转换的数据集
 * @param string $pid parent标记字段
 * @param string $level level标记字段
 * @return array
 */
function list_to_tree($list, $pk='id', $pid = 'pid', $child = '_child', $root = 0) {
    // 创建Tree
    $tree = array();
    if(is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] =& $list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId =  $data[$pid];
            if ($root == $parentId) {
                $tree[] =& $list[$key];
            }else{
                if (isset($refer[$parentId])) {
                    $parent =& $refer[$parentId];
                    $parent[$child][] =& $list[$key];
                }
            }
        }

    }
    return $tree;
}
/**
 * 对查询结果集进行排序
 * @access public
 * @param array $list 查询结果
 * @param string $field 排序的字段名
 * @param array $sortby 排序类型
 * asc正向排序 desc逆向排序 nat自然排序
 * @return array
 */
function list_sort_by($list,$field, $sortby='asc') {
   if(is_array($list)){
       $refer = $resultSet = array();
       foreach ($list as $i => $data)
           $refer[$i] = &$data[$field];
       switch ($sortby) {
           case 'asc': // 正向排序
                asort($refer);
                break;
           case 'desc':// 逆向排序
                arsort($refer);
                break;
           case 'nat': // 自然排序
                natcasesort($refer);
                break;
       }
       foreach ( $refer as $key=> $val)
           $resultSet[] = &$list[$key];
       return $resultSet;
   }
   return false;
}



/* 除数字过滤，取前20个数字 */
function int_preg($str, $len=20){
	$str=preg_replace('/\D/Uis','',$str);
	$str=substr($str,0,$len);
	return $str;
}

/* 除数字字母过滤，取前20个字串 */
function str_preg($str, $len=20){
	$str=preg_replace('/\s/Uis','',$str);
	$str=preg_replace('/\D\W/Uis','',$str);
	$str=substr($str,0,$len);
	return $str;
}

/* 除数字字母逗号过滤 */
function isd_preg($str, $len=40){
	$str=preg_replace('/[^,\d\w]+/Uis','',$str);
	$str=substr($str,0,$len);
	return $str;
}

/* 除字母逗号空格过滤 */
function ss_preg($str, $len=20){
	$str=preg_replace('/[^,\sA-Za-z_]+/Uis','',$str);
	$str=substr($str,0,$len);
	return $str;
}

/* 汉字长度过滤 */
function hanzi_preg($str, $len=20){
	preg_match_all('/./us', $str, $match);
	$str=implode('',array_slice($match[0],0,$len));
	return $str;
}

function xing_preg($str, $start=3,$re='*'){
	preg_match_all('/./us', $str, $match);
	$pre=array_slice($match[0],$start);
	$pre=str_replace($pre, $re, $str);

	return $pre;
}
