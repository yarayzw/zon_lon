<?php
/**
 * Created by PhpStorm.
 * User: dyxdw
 * Date: 2016/12/5
 * Time: 16:52
 */

namespace Admini\Model;
use Think\Model;

class AuthGroupModel extends Model
{
    /**获取角色列表
     * @param int $status
     */
    public function getlist($status=-1,$keyid=true){
        $where=array();
        if($status!=-1){
            $where["status"]=$status;
        }
        $data=$this->where($where)->select();
        if($keyid){
            $temp_arr=array();
            foreach ($data as $key=>$value){
                $temp_arr[$value["id"]]=$value;
            }
            return $temp_arr;
        }
        return $data;
    }
    /** 获取一条角色的信息
     * @param $id
     */
    public function getGroupinfo($id){
        $where["id"]=$id;
        $data=$this->where($where)->find();
        return $data;
    }

    /**
     * 删除角色
     */
    public function delRole($rid){
        $auth_group_access=M("auth_group_access");
        $auth_group_access->where(array("group_id"=>$rid))->delete();

        $where["id"]=$rid;
        $this->where($where)->delete();
        return true;
    }

    /**
     * 返回用户组列表
     * 默认返回正常状态的管理员用户组列表
     * @param array $where   查询条件,供where()方法使用
     */
    public function getGroups(){
        return $this->select();
    }

    /**
     * 添加用户组规则信息
     * 默认返回正常状态的管理员用户组列表
     */
    public function selGroup($gid=''){
        $data  =  array();
        $ruledata  =  array();
        if( $gid ){
            $data['id']  =  $gid;
            return  $this->where($data)->find();
            // $this->gprules  =  explode(',',$gpinfo['rules']);
        }
    }
    /**
     * 返回规则列表,按照条件
     *	@param  string or integer  $auth_id  传值数据
     *	@return  array
     */
    public function getRule(){
        $authrule  =  M('auth_rule');
        $ruledata['status']  =  1;
        return  $authrule->order('sort asc')->where($ruledata)->select();
    }
    /**
     * 把用户添加到用户组,支持批量添加用户到用户组
     * 示例: 把uid=1的用户添加到group_id为1,2的组 `AuthGroupModel->addToGroup(1,'1,2');`
     */
    public function addToGroup($uid,$gid){
        $uid = is_array($uid)?implode(',',$uid):trim($uid,',');
        $gid = is_array($gid)?$gid:explode( ',',trim($gid,',') );

        $Access = M(self::AUTH_GROUP_ACCESS);
        if( isset($_REQUEST['batch']) ){
            //为单个用户批量添加用户组时,先删除旧数据
            $del = $Access->where( array('uid'=>array('in',$uid)) )->delete();
        }

        $uid_arr = explode(',',$uid);
        $uid_arr = array_diff($uid_arr,array(C('USER_ADMINISTRATOR')));
        $add = array();
        if( $del!==false ){
            foreach ($uid_arr as $u){
                //判断用户id是否合法
                if(M('Member')->getFieldByUid($u,'uid') == false){
                    $this->error = "编号为{$u}的用户不存在！";
                    return false;
                }
                foreach ($gid as $g){
                    if( is_numeric($u) && is_numeric($g) ){
                        $add[] = array('group_id'=>$g,'uid'=>$u);
                    }
                }
            }
            $Access->addAll($add);
        }
        if ($Access->getDbError()) {
            if( count($uid_arr)==1 && count($gid)==1 ){
                //单个添加时定制错误提示
                $this->error = "不能重复添加";
            }
            return false;
        }else{
            return true;
        }
    }

}