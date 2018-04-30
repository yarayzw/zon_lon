<?php
/**
 * Created by PhpStorm.
 * User: dyxdw
 * Date: 2016/12/5
 * Time: 15:10
 */

namespace Admini\Controller;
use Think\Controller;

class PublicController extends Controller
{
    public function login(){
        if(is_login()){
          //  $this->redirect("Index/index");
        }
        $this->display();
    }
    /**
     * 执行登录动作
     */
    public function do_login(){
        $account=I("account","","trim");
        $password=I("password","");
        $verity=I("verity","");
        $result=array();
        if(empty($account)||empty($password)||empty($verity)){
            $result["code"]=10005;
            $result["message"]="登录信息不完整！";
        }else{
            if(!check_verify($verity,34)){
                $result["code"]=10004;
                $result["message"]="验证码不正确！";
            }else{
                $admin=D("administrator");
                $r=$admin->login($account,$password);
                if(is_array($r)){
                    $data=$r[1];
                    session("admin.id",$data["id"]);
                    session("admin.account",$data["account"]);
                    session("admin.nickname",$data["nickname"]);
                    $time=time();
                    $ip=get_client_ip();
                    $admin->loginlog($data["id"],$time,$ip);
                    $log="管理员[".$account."]登录成功！登录IP：".$ip;
                    systemlog("administrator",$data["id"],$data["account"],$log);
                    $result["code"]=10000;
                    $result["message"]="登录成功，正在为您跳转...";
                    $result["todo"]=U('Index/index');
                }else{
                    if($r==10006){
                        $result["code"]=10006;
                        $result["message"]="管理员状态异常！";
                    }else{
                        $result["code"]=10001;
                        $result["message"]="账号或密码错误！";
                    }
                    $log="管理员[".$account."]登录失败！原因：".$result["message"];
                    systemlog("administrator",0,$account,$log);
                }
            }
        }

        $this->ajaxReturn($result);
    }
    /**
     * 退出登录
     */
    public function loginout(){
        session("admin",null);
        $this->redirect("Public/login");
    }
    /**
     * 公用验证码
     * @return string
     */
    public function verifyimg(){
        ob_clean();
        $config =    array(
            'fontSize'    =>    30,    // 验证码字体大小
            'length'      =>    4,     // 验证码位数
            'useNoise'    =>    true, // 关闭验证码杂点
            'useImgBg'	  =>    false	//背景图
        );
        $Verify = new \Think\Verify($config);
        $Verify->entry(34);
    }
}