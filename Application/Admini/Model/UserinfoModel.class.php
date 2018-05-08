<?php
/**
 * Created by PhpStorm.
 * User: yara
 * Date: 2017/6/14
 * Time: 14:52
 */

namespace Admini\Model;

use Think\Model;

class UserInfoModel extends Model
{
    public function getAllUserInfo($table_Q){
        $mysql = new \Think\Model();
        $where = "";
        $page = I('get.pageNumber','','trim');
        $limit = I('get.pageSize','','trim');
        $sort = I('get.sortName','','trim');
        $sort_by = I('get.sortOrder','','trim');
        if(I('get.name')!="") $where.=" and t.name like '%".I('get.name','','trim')."%'";
        if(I('get.nickname')!="") $where.=" and t.nickname like '%".I('get.nickname','','trim')."%'";
        if(I('get.phone')!="") $where.=" and t.phone like '%".I('get.phone','','trim')."%'";
        $where_city = $this->getCity();
        $where .= !empty($where_city) ? $where_city : '';
        $user_count = "select COUNT(1)  AS `numrows`
                         from {$table_Q}userinfo t
                         LEFT JOIN {$table_Q}area t1
                         ON t.areaid = t1.id
                         WHERE 1=1 {$where}";
        $total_arr =  $mysql->query($user_count);
        $total = $total_arr[0]['numrows'];
        $total_page = ceil($total / $limit);//总页数
        if($page <= 1){
            $page = 1;
        }elseif($page >= $total_page){
            $page = $total_page;
        }
        $offset = ($page - 1 ) * $limit;
        if (!empty($sort)) $where .= " order by {$sort} {$sort_by}";
        if (!empty($page)) $where .= " LIMIT  {$offset},{$limit} ";
        $sql_user = "select t.*,t1.areaname as district,t2.areaname as city,t3.areaname as province
                     from {$table_Q}userinfo t
                     LEFT JOIN {$table_Q}area t1
                     ON t.areaid = t1.id
                     LEFT JOIN {$table_Q}area t2
                     ON t1.parentid = t2.id
                     LEFT JOIN {$table_Q}area t3
                     ON t2.parentid = t3.id
                     WHERE 1=1 {$where}";

        $row_user = $mysql->query($sql_user);
        $count_id = implode(',',array_column($row_user,'id'));
        $statistics_week = M('statistics_week');
        $row_count_sql = "select t.*
                          from {$table_Q}statistics_week t
                          RIGHT JOIN (
                            SELECT t1.user_id,MAX(t1.addtime) as max_addtime
                            from {$table_Q}statistics_week t1
                            GROUP BY t1.user_id
                          ) t2
                          ON t.user_id = t2.user_id AND t.addtime = t2.max_addtime
                          WHERE t.user_id in({$count_id})
                          ";
        $count_row = $statistics_week->query($row_count_sql);
        foreach($count_row as $k=>$v)
            $count_row_new[$v['user_id']] = $v;
        //查询设备面积
        $aere_sql = "select sum(t.area) as sum,COUNT(1) as count,t2.id
                     from {$table_Q}facilities t
                     LEFT JOIN {$table_Q}user_faci_mapping t1
                     ON t.id = t1.faciid
                     LEFT JOIN {$table_Q}userinfo t2
                     ON t2.id = t1.uid
                     WHERE t2.id in({$count_id})
                     GROUP BY t2.id
                    ";
        $area_row = $this->query($aere_sql);
        foreach($area_row as $k=>$v)
            $area_row_new[$v['id']] = $v;
        foreach($row_user as $k=>$v){
            $row_user[$k]['mean_consumption'] = $count_row_new[$v['id']]['mean_consumption'];
            $row_user[$k]['mean_temperature'] = $count_row_new[$v['id']]['mean_temperature'];
            $row_user[$k]['mean_area'] = round(intval($area_row_new[$v['id']]['sum'])/intval($area_row_new[$v['id']]['count']),2);
        }

        if($row_user!==false){
            $data['data']['total']=$total;
            $data['data']['rows']=$row_user;
            $data['success']=200;
            $data['message']=null;
        }else{
            $data['success']=400;
            $data['message']="暂无数据!";
        }
        unset($count_row,$row_user,$count_id,$count_row_new);
        return $data;
    }

    /**
     * 根据管理员id查询用户信息
     * */
    public function getUserInfo($table_Q){
        $mysql = new \Think\Model();
        $where = "";
        $page = I('get.pageNumber','','trim');
        $limit = I('get.pageSize','','trim');
        $sort = I('get.sortName','','trim');
        $sort_by = I('get.sortOrder','','trim');
        if(I('get.name')!="") $where.=" and t5.name like '%".I('get.name','','trim')."%'";
        if(I('get.nickname')!="") $where.=" and t5.nickname like '%".I('get.nickname','','trim')."%'";
        if(I('get.phone')!="") $where.=" and t5.phone like '%".I('get.phone','','trim')."%'";
//        $where .= $_SESSION['admin']['id'] == 1 ? '' : ' and t.areaid = '.$_SESSION['admin']['areaid'];
        $where_city = $this->getCity();
        $where .= !empty($where_city) ? $where_city : '';
        if (!empty($sort)) $where .= " group by t5.id  order by t5.{$sort} {$sort_by}";
        $sql_facility = "
            select COUNT(*) as numrows
            from {$table_Q}facilities t 
            LEFT JOIN {$table_Q}user_faci_mapping t4
            ON t.id = t4.faciid
            LEFT JOIN {$table_Q}userinfo t5 
            ON t5.id = t4.uid
            LEFT JOIN {$table_Q}area t1
            ON t1.id = t5.areaid
            WHERE 1 = 1 {$where}
        ";
        $total_arr =  $mysql->query($sql_facility);
        $total = $total_arr[0]['numrows'];
        $total_page = ceil($total / $limit);//总页数
        if($page <= 1){
            $page = 1;
        }elseif($page >= $total_page){
            $page = $total_page;
        }
        $offset = ($page - 1 ) * $limit;

        if (!empty($page)) $where .= " LIMIT  {$offset},{$limit} ";
        $sql_user = "
            select t5.*,t1.areaname as district,t2.areaname as city,t3.areaname as province
            from {$table_Q}facilities t 
            LEFT JOIN {$table_Q}user_faci_mapping t4
            ON t.id = t4.faciid
            LEFT JOIN {$table_Q}userinfo t5 
            ON t5.id = t4.uid
            LEFT JOIN {$table_Q}area t1
            ON t1.id = t5.areaid
            LEFT JOIN {$table_Q}area t2
            ON t1.parentid = t2.id
            LEFT JOIN {$table_Q}area t3
            ON t2.parentid = t3.id
            WHERE 1 = 1 {$where}
    
        ";
        $row_user = $mysql->query($sql_user);
        $row = [];
        foreach ($row_user as $k=>$v)
            if($v['id']!=null) $row[] = $v;
        $count_id = implode(',',array_column(array_filter($row),'id'));
        $row_info = array_filter($row);
        $row_count_sql = "select t.*
                          from {$table_Q}statistics_week t
                          RIGHT JOIN (
                            SELECT t1.user_id,MAX(t1.addtime) as max_addtime
                            from {$table_Q}statistics_week t1
                            GROUP BY t1.user_id
                          ) t2
                          ON t.user_id = t2.user_id AND t.addtime = t2.max_addtime
                          WHERE t.user_id in({$count_id})
                          ";
        $statistics_week = M('statistics_week');
        $count_row = $statistics_week->query($row_count_sql);
        foreach($count_row as $k=>$v)
            $count_row_new[$v['user_id']] = $v;
        //查询设备面积
        $aere_sql = "select sum(t.area) as sum,COUNT(1) as count,t2.id
                     from {$table_Q}facilities t
                     LEFT JOIN {$table_Q}user_faci_mapping t1
                     ON t.id = t1.faciid
                     LEFT JOIN {$table_Q}userinfo t2
                     ON t2.id = t1.uid
                     WHERE t2.id in({$count_id})
                     GROUP BY t2.id
                    ";
        $area_row = $this->query($aere_sql);
        foreach($area_row as $k=>$v)
            $area_row_new[$v['id']] = $v;
        foreach($row_info as $k=>$v){
            $row_info[$k]['mean_consumption'] = $count_row_new[$v['id']]['mean_consumption'];
            $row_info[$k]['mean_temperature'] = $count_row_new[$v['id']]['mean_temperature'];
            $row_user[$k]['mean_area'] = round(intval($area_row_new[$v['id']]['sum'])/intval($area_row_new[$v['id']]['count']),2);
        }
        if($row_user!==false){
            $data['data']['total']=$total;
            $data['data']['rows']=$row_info;
            $data['success']=200;
            $data['message']=null;
        }else{
            $data['success']=400;
            $data['message']="暂无数据!";
        }
        unset($count_row_new,$count_row,$count_id,$row_user);
        return $data;
    }


    private function getCity(){
        if(I('get.district')>0) {
            $where_city = " and t1.id = ".I('get.district','','trim');
        }else{
            if(I('get.city')>0){
                $area = M('area');
                $row = $area->where('status = 1 and parentid ='.I('get.city','','trim'))->field('id')->select();
                $district_id = array();
                foreach($row as $k=>$v){
                    $district_id[]=$v['id'];
                }
                $district_id = implode(',',$district_id);
                $where_city = " and t1.id in({$district_id})";
            }else{
                if(I('get.province')>0) {
                    $area = M('area');
                    $row = $area->where('status = 1 and parentid ='.I('get.province','','trim'))->field('id')->select();
                    $city_id = array();
                    foreach($row as $k=>$v){
                        $city_id[]=$v['id'];
                    }
                    $city_id = implode(',',$city_id);
                    $row = $area->where('status = 1 and parentid in('.$city_id.')')->field('id')->select();
                    $district_id = array();
                    foreach($row as $k=>$v){
                        $district_id[]=$v['id'];
                    }
                    $district_id = implode(',',$district_id);
                    $where_city = " and t1.id in({$district_id})";
                }
            }
        }
        return $where_city;
    }

    /**
     * 查询设备当前状态拼接命令
     * @fid 设备id
     * @data_set Array 格式化的命令
     * @is_order 是否存在待发送命令 1 不存在 2 存在
     * */
    public function setOrder($fid,$data_set,$is_order = 1){
        $preparesend = M('preparesend');
		
        if($is_order == 1){
            $receivelog = M('receivelog');
            $data_receivelog = $receivelog->where('fid = '.$fid)->order('addtime desc')->find();
            $data = array_slice($data_receivelog,4,37);
        }else if($is_order == 2){
            $data = $preparesend->where('data2 = 221 and fcid = '.$fid)->order('addtime desc')->find();
        }else{
            die('非法操作!');
        }
		
        foreach ($data_set as $k=>$v)
            if($v != $data[$k])
                $data[$k] = $v;
        $data['fcid'] = $fid;
        $data['identifynum'] = CreateIdentifyNum($data['fcid']);
        //处理不同值
        $data['data2'] = 221;
        $data['data12'] = 1;
        $data['addtime'] = time();
		
        $row = $preparesend->data($data)->add();
        if($row!=false){
            $facility = M('facilities');
            $facility->where('id = '.$fid)->save(array('is_order'=>2));
            $facilitiesorderlog = D('facilitiesorderlog');
            $facilitiesorderlog->setTransform($data,$data_receivelog);//写入日志
        }
        return $row;
    }

    /**
     * 查出待发送命令表中设备状态
     *
     * */
    public function getProOrder($fid,$data_set){
        $data = array_slice($data_set,4,37);
        $preparesend = M('preparesend');
        $row = $preparesend->where('fcid = '.$fid)->order('addtime asc')->select();
        foreach ($row as $k=>$v)
            foreach ($data as $k_data => $v_data)
                $data[$k_data] = $v_data != $v[$k_data] ? $v[$k_data] : $v_data;;
        return $data;
    }


    /**
     * 年
     * */
    public function get_year($user_id,$type,$time,$table_Q){
        $use_time = date('Y',strtotime($time));
        $sql = "
           select sum(t.mean_consumption) as sum_mean_consumption,sum(t.mean_temperature) as sum_mean_temperature,COUNT(1) as count
           from {$table_Q}statistics_month t
           WHERE t.user_id = {$user_id} AND t.year = {$use_time}
        ";
        $row = $this->query($sql);
        return $row[0];
    }

    /**
     * 月
     * */
    public function get_month($user_id,$type,$time,$table_Q){
        $use_time = date('m',strtotime($time));
        $year = date('Y',strtotime($time));
        $sql = "
           select sum(t.mean_consumption) as sum_mean_consumption,sum(t.mean_temperature) as sum_mean_temperature,COUNT(1) as count
           from {$table_Q}statistics_month t
           WHERE t.user_id = {$user_id} AND t.month = {$use_time} AND t.year = {$year}
        ";
        $row = $this->query($sql);
        return $row[0];
    }

    /**
     * 日
     * */
    public function get_day($user_id,$type,$time,$table_Q){
        $year = date('Y',strtotime($time));
        $month = date('m',strtotime($time));
        $day = date('d',strtotime($time));
        $sql = "
           select sum(t.mean_consumption) as sum_mean_consumption,sum(t.mean_temperature) as sum_mean_temperature,COUNT(1) as count
           from {$table_Q}statistics_day t
           WHERE t.user_id = {$user_id} AND t.month = {$month} AND t.day = {$day} AND t.year = {$year}
        ";

        $row = $this->query($sql);
        return $row[0];
    }

    /**
     * 周
     * */
    public function get_week($user_id,$type,$time,$table_Q){
        $year = date('Y',strtotime($time));
        $month = date('m',strtotime($time));
        $week = date('W',strtotime($time));
        $sql = "
           select sum(t.mean_consumption) as sum_mean_consumption,sum(t.mean_temperature) as sum_mean_temperature,COUNT(1) as count
           from {$table_Q}statistics_week t
           WHERE t.user_id = {$user_id} AND t.month = {$month} AND t.week = {$week} AND t.year = {$year}
        ";

        $row = $this->query($sql);
        return $row[0];
    }


}