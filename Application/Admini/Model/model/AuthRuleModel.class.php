<?php
/**
 * Created by PhpStorm.
 * User: dyxdw
 * Date: 2016/12/5
 * Time: 16:53
 */

namespace Admini\Model;
use Think\Model;

class AuthRuleModel extends Model
{
    /**
     * 获取权限列表
     */
    public function GetRuleList(){
        $where=array();
        $where["type"]=1;
        $where["status"]=1;
        $data=$this->where($where)->order("sort asc")->select();
        return $data;
    }

    /**
     * 修改规则状态
     */
    public function amendstatus($aid,$status=1){
        $where["id"]=$aid;
        $r=$this->where($where)->save(array("status"=>$status));
        return $r;
    }
}