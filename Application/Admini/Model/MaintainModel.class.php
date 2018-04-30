<?php
/**
 * Created by PhpStorm.
 * User: yara
 * Date: 2017/5/25
 * Time: 15:22
 */

namespace Admini\Model;

use Think\Model;

class MaintainModel extends Model
{

    public function getMaintainAjax(){

        $where = "";
        $page = I('get.pageNumber','','trim');
        $limit = I('get.pageSize','','trim');
        $sort = I('get.sortName','','trim');
        $sort_by = I('get.sortOrder','','trim');
        if(I('get.tablename')!="") $where.=" and tablename like '%".I('get.tablename','','trim')."%'";
        if(I('get.act_account')!="") $where.=" and act_account like '%".I('get.act_account','','trim')."%'";
        if (I('get.E_time')!="") {
            $where .= " and times < " . trim(strtotime(I('get.E_time')));
        }
        if (I('get.Q_time')!="") {
            $where .= " and times > " . trim(strtotime(I('get.Q_time')));
        }

        $total_arr = $this->where('1=1 '.$where)->count();
        $total = $total_arr;
        $total_page = ceil($total / $limit);//总页数
        if($page <= 1){
            $page = 1;
        }elseif($page >= $total_page){
            $page = $total_page;
        }
        $offset = ($page - 1 ) * $limit;
        $row_log = $this->where('1=1 '.$where)->order("{$sort} {$sort_by}")->limit("{$offset},{$limit}")->select();
        if($row_log!==false){
            $data['data']['total']=$total_arr;
            $data['data']['rows']=$row_log;
            $data['success']=200;
            $data['message']=null;
        }else{
            $data['success']=400;
            $data['message']="暂无数据!";
        }
        return $data;
    }

    /**
     * 处理表单数据
     * */
    public function getFrom(){
        $from = I('post.from');
        if(I('get.act')=='add'||$from['edit_account']!==$from['hidden_account']){
            $row_account = $this->where("account = '".trim($from['edit_account'])."'")->find();
            if($row_account !=false) {echo json_encode(array('code'=>400,'msg'=>'登录用户名已存在！'));die();}
        }
        !empty($from['edit_account']) ? $data['account'] = trim($from['edit_account']) : '';
        !empty($from['edit_password']) ? $data['password'] = mymd5(trim($from['edit_password'])) : '';
        !empty($from['edit_realname']) ? $data['realname'] = trim($from['edit_realname']) : '';
        !empty($from['edit_phone']) ? $data['phone'] = trim($from['edit_phone']) : '';
        return $data;
    }

}