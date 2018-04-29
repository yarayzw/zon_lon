<?php
/**
 * Created by PhpStorm.
 * User: yara
 * Date: 2017/5/24
 * Time: 9:35
 */
namespace Admini\Model;

use Think\Model;

class OperationlogModel extends Model
{

    public function getLogListAjax(){

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

}