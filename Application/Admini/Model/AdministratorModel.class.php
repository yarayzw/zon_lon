<?php
namespace Admini\Model;
use Think\Model;
/**
 * Created by PhpStorm.
 * User: dyxdw
 * Date: 2016/12/5
 * Time: 15:55
 */
class AdministratorModel extends Model
{
    /**
     * 验证账号是否存在
     */
    public function ExistAccount($account){
        $where["account"]=$account;
        $data=$this->where($where)->find();
        if($data)
            return true;
        else
            return false;
    }
    /**删除一个管理员
     * @param array|mixed $id
     */
    public function deladmin($id){
        //清理日志
        M("operationlog")->where(array("act_id"=>$id))->delete();
        //删除权限
        $auth_group_access=M("auth_group_access");
        $auth_group_access->where(array("uid"=>$id))->delete();
        $this->where(array("id"=>$id))->delete();
        return true;
    }
    /**管理员登录
     * @param $accout
     * @param $password
     * @param int $type
     */
    public function login($accout,$password,$type=1){
        $where["account"]=$accout;
        $data=$this->where($where)->find();
        if($data){
            $password=mymd5($password);
            if($password==$data["password"]){
                if($data["status"]==1){
                    if ($_SESSION['user_auth']) {
                        $res = $_SESSION['user_auth'];
                    } else {
                        $tableQ = C('DB_PREFIX');
                        $m = new \Think\Model();
                        $res_sql = "select t2.rules
                        from {$tableQ}administrator t
                        LEFT JOIN {$tableQ}auth_group_access t1
                        ON t.id = t1.uid
                        LEFT JOIN {$tableQ}auth_group t2
                        ON t1.group_id = t2.id
                        WHERE t.id = {$data['id']}";
                        $row = $m->query($res_sql);
                        $res = $row[0];
                    }
                    $_SESSION['user_auth'] = $res;
                    session(array('name'=>'user_auth','expire'=>3800));
                    return array(10000,$data);//登录成功
                }else{
                    return 10006;//状态异常
                }
            }else{
                return 10003;//密码不正确
            }
        }else{
            return 10002;//用户不存在
        }
    }
    /**记录最后一次登录信息
     * @param $id
     * @param $time
     * @param $ip
     */
    public function loginlog($id,$time,$ip){
        $data["lasttime"]=$time;
        $data["lastip"]=$ip;
        $r=$this->where(array("id"=>$id))->save($data);
        if($r)
            return true;
        else
            return false;
    }

    /**
     * 编辑管理员信息
     * */
    public function editUser(){
        $tableQ = C('DB_PREFIX');
        $mysql = new \Think\Model();
        $from = I('post.from');
        $role = trim($from['edit_role']);
        $data['account'] = trim($from['edit_account']);
        $data['nickname'] = trim($from['edit_nickname']);
        $data['realname'] = trim($from['edit_realname']);
        $data['phone'] = trim($from['edit_phone']);
        if($from['edit_district']==-1){
            if($from['edit_city']==-1){
                if($from['edit_province']!=-1){
                    $data['areaid'] = $from['edit_province'];
                }else{
                    $data['areaid'] = 0;
                }
            }else{
                $data['areaid'] = $from['edit_city'];
            }
        }else{
            $data['areaid'] = $from['edit_district'];
        }
        if(I('get.action')=='edit'){
            $id = I('post.id','','trim');
            $edit_sql = "update {$tableQ}administrator t,{$tableQ}auth_group_access t1
                         set
                              t.nickname = '{$data['nickname']}',
                              t.realname = '{$data['realname']}',
                              t.phone = '{$data['phone']}',
                              t.areaid = '{$data['areaid']}',
                              t1.group_id = {$role}
                         WHERE t.id = {$id} and t1.uid = t.id";
            $row = $mysql->execute($edit_sql);
            unset($data);
            $data['code'] = $row!==false ? 200 : 400;
        }else if(I('get.action')=='add'){
            $password = mymd5(111111);
            $add_sql = "insert into {$tableQ}administrator(`account`,`password`,`nickname`,`realname`,`phone`,`status`,`areaid`)
                        VALUES ('{$data['account']}','{$password}','{$data['nickname']}','{$data['realname']}','{$data['phone']}',1,'{$data['areaid']}')";
            $row = $mysql->execute($add_sql);
            if($row!==false){
                $insert_id = $mysql->getLastInsID();
                $add_role_sql  = "insert into {$tableQ}auth_group_access(`uid`,`group_id`) VALUES ({$insert_id},{$role})";
                $row_role = $mysql->execute($add_role_sql);
                $data['code'] = $row_role!==false ? 200 : 400;
            }else{
                $data['code'] = 400;
            }
        }else if(I('get.action')=='del'){
            $id = I('post.id','','trim');
            $del_sql = "delete from {$tableQ}administrator
                        WHERE id = {$id}";
            $row = $mysql->execute($del_sql);
            if($row!==false){
                $del_sql_role = "delete from {$tableQ}auth_group_access
                                 WHERE uid = {$id}";
                $row_role = $mysql->execute($del_sql_role);
                $data['code'] = $row_role!==false ? 200 : 400;
            }else{
                $data['code'] = 400;
            }
        }
        return $data;
    }



}