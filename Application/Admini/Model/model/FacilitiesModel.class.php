<?php
/**
 * Created by PhpStorm.
 * User: yara
 * Date: 2017/6/8
 * Time: 9:52
 */
namespace Admini\Model;


use Think\Model;

class FacilitiesModel extends Model
{

    /**
     * 异步加载首页运行设备地图
     * */
    public function getOperationFacility($table_Q,$is_run,$is_get_run=0){
        $mysql = new \Think\Model();
        $time_where = 'WHERE 1=1 ';
//        echo '<pre>';
//        var_dump($_POST);die();
        $facilityList = $this->query("
            select t.* , t1.shortname,t1.areaname,t2.id as pro_id,t2.shortname as pro_shortname,t2.areaname as pro_areaname,t3.id as provin_id,t3.shortname as provin_shortname,t3.areaname as provin_areaname
            from {$table_Q}facilities t
            LEFT JOIN {$table_Q}area t1
            ON t.areaid = t1.id
            LEFT JOIN {$table_Q}area t2
            ON t1.parentid = t2.id
            LEFT JOIN {$table_Q}area t3
            ON t2.parentid = t3.id
        ");

        if($facilityList==false){
            echo json_encode(array('code'=>400,'msg'=>'暂无数据！'));
            die();
        }
        if($is_get_run === 1){
            return count($facilityList);
        }
        foreach ($facilityList as $k=>$v)
            $facilityId[] = $v['id'];
        $new_facilityList = $this->facilityIdToStutas($table_Q,$facilityId,$facilityList,$is_run);
        $sum = $this->getStatistics($new_facilityList,$facilityId,$table_Q,1);
        return $sum;
//        $where = $is_run == 1 ? ' and t.data38 = 0' : ' and t.data38 <> 0';
        /*全国设备单页条件设置*/
//        if(!empty($_POST['all'])){
//            if(I('post.is_run_query','','trim')==-1){
//                $where = '';
//            }else if(I('post.is_run_query','','trim')==1){
//                $where = ' and t.data38 = 0';
//            }else{
//                $where = ' and t.data38 <> 0';
//            }
//        }
//        /*end*/
//        $operationFacility_sql = "select t.fid
//                from {$table_Q}receivelog t
//                RIGHT JOIN (
//                        SELECT MAX(t1.addtime) AS max_time,t1.fid
//                        FROM {$table_Q}receivelog t1
//                        GROUP BY
//                        t1.fid
//                ) t2 ON t.fid = t2.fid AND t.addtime = t2.max_time
//                WHERE 1=1 {$where}
//                ";
//        $operationFacility = $mysql->query($operationFacility_sql);
//        if($operationFacility == false) {echo json_encode(array('code'=>400,'msg'=>'暂无数据！'));die();}
//        $operation_fid = [];
//        foreach($operationFacility as $k=>$value) $operation_fid[] = $value['fid'];
//        $operation = implode(',',$operation_fid);
//        $where_row = $is_run == 1 ? 'COUNT(1) as value , t6.shortname as name' : 'COUNT(1) as value,t5.shortname as name , t5.lng ,t5.lat';
//        $where_all = " and t.id in({$operation})";
//        /*全国设备单页条件设置*/
//        if(!empty($_POST['all']) && I('post.provinceid','','trim')!=-1){
//            $status = I('post.status','','trim');
//            if($status==2){
//                $provinceid = I('post.provinceid','','trim');
//                $areaid_my = $this->provinceGetdistrict($provinceid);
//            }else if($status == 3){
//                if(I('post.cityid','','trim')!=-1){
//                    $cityid = I('post.cityid','','trim');
//                    $areaid_my = $this->cityGetdistrict($cityid);
//                }else{
//                    $provinceid = I('post.provinceid','','trim');
//                    $areaid_my= $this->provinceGetdistrict($provinceid);
//                }
//            }else if($status == 4){
//                if(I('post.disabledid','','trim')!=-1){
//                    $areaid_my = "'".I('post.disabledid','','trim')."'";
//                }else{
//                    if(I('post.cityid','','trim')!=-1){
//                        $cityid = I('post.cityid','','trim');
//                        $areaid_my = $this->cityGetdistrict($cityid);
//                    }else{
//                        $provinceid = I('post.provinceid','','trim');
//                        $areaid_my= $this->provinceGetdistrict($provinceid);
//                    }
//                }
//            }else{
//                die('非法操作！');
//            }
//            $where_all .= ' and t.areaid in ('.$areaid_my.')';
//        }
//        /*end*/
//        $row_sql = "select  {$where_row}
//                    from {$table_Q}facilities t
//                    LEFT JOIN {$table_Q}area t4
//                    ON t.areaid = t4.id
//                    LEFT JOIN {$table_Q}area t5
//                    ON t4.parentid = t5.id
//                    LEFT JOIN {$table_Q}area t6
//                    ON t5.parentid = t6.id
//                    WHERE 1=1 {$where_all}
//                    GROUP BY t6.id";
//        $row = $mysql->query($row_sql);
//        if($is_run == 1){
//            $data['row'] = $row;
//            $data['nums'] = count($row);
//        }else{
//            $rs = [];
//            foreach($row as $k=>$v){
//                $rs[$k]['name'] = $v['name'];
//                $rs[$k]['value'] = array(0=>$v['lng'],1=>$v['lat'],2=>$v['value']);
//            }
//            $data['row'] = $rs;
//            $data['nums'] = count($row);
//        }
//        return $data;
    }

    /**
     * main AJAX
     * */
    public function getMainFacility($table_Q,$is_run){
        $mysql = new \Think\Model();
        $where = $is_run == 1 ? ' and t.data38 = 0' : ' and t.data38 <> 0';
        /*需求改了无数次 方法已经忘了为了什么做的*/
        //上面本来是查的故障，但是首页改了需求 又不知道别的地方有没有用到 所以就重置下条件
        if($_POST['all_fa']==='yes'){
            $where = '';
        }
        /*全国设备单页条件设置*/
        if(!empty($_POST['all'])){
            if(I('post.is_run_query','','trim')==-1){
                $where = '';
            }else if(I('post.is_run_query','','trim')==1){
                $where = ' and t.data38 = 0';
            }else{
                $where = ' and t.data38 <> 0';
            }
        }
        /*end*/
        $operationFacility_sql = "select t.fid
                from {$table_Q}receivelog t
                RIGHT JOIN (
                        SELECT MAX(t1.addtime) AS max_time,t1.fid
                        FROM {$table_Q}receivelog t1
                        GROUP BY
                        t1.fid
                ) t2 ON t.fid = t2.fid AND t.addtime = t2.max_time
                WHERE 1=1 {$where}
                ";
        $operationFacility = $mysql->query($operationFacility_sql);

        if($operationFacility == false) {echo json_encode(array('code'=>400,'msg'=>'暂无数据！'));die();}
        $operation_fid = [];
        foreach($operationFacility as $k=>$value) $operation_fid[] = $value['fid'];
        $operation = implode(',',$operation_fid);
        $where_row = $is_run == 1 ? 'COUNT(1) as value , t6.shortname as name' : 'COUNT(1) as value,t5.shortname as name , t5.lng ,t5.lat';
        $where_all = " and t.id in({$operation})";
        /*全国设备单页条件设置*/
        if(!empty($_POST['all']) && I('post.provinceid','','trim')!=-1){
            $status = I('post.status','','trim');
            if($status==2){
                $provinceid = I('post.provinceid','','trim');
                $areaid_my = $this->provinceGetdistrict($provinceid);
            }else if($status == 3){
                if(I('post.cityid','','trim')!=-1){
                    $cityid = I('post.cityid','','trim');
                    $areaid_my = $this->cityGetdistrict($cityid);
                }else{
                    $provinceid = I('post.provinceid','','trim');
                    $areaid_my= $this->provinceGetdistrict($provinceid);
                }
            }else if($status == 4){
                if(I('post.disabledid','','trim')!=-1){
                    $areaid_my = "'".I('post.disabledid','','trim')."'";
                }else{
                    if(I('post.cityid','','trim')!=-1){
                        $cityid = I('post.cityid','','trim');
                        $areaid_my = $this->cityGetdistrict($cityid);
                    }else{
                        $provinceid = I('post.provinceid','','trim');
                        $areaid_my= $this->provinceGetdistrict($provinceid);
                    }
                }
            }else{
                die('非法操作！');
            }
            $where_all .= ' and t.areaid in ('.$areaid_my.')';
        }
        /*end*/
        $row_sql = "select  {$where_row}
                    from {$table_Q}facilities t
                    LEFT JOIN {$table_Q}area t4
                    ON t.areaid = t4.id
                    LEFT JOIN {$table_Q}area t5
                    ON t4.parentid = t5.id
                    LEFT JOIN {$table_Q}area t6
                    ON t5.parentid = t6.id
                    WHERE 1=1 {$where_all}
                    GROUP BY t6.id";
        $row = $mysql->query($row_sql);
        if($is_run == 1){
            $data['row'] = $row;
            $data['nums'] = count($row);
        }else{
            $rs = [];
            foreach($row as $k=>$v){
                $rs[$k]['name'] = $v['name'];
                $rs[$k]['value'] = array(0=>$v['lng'],1=>$v['lat'],2=>$v['value']);
            }
            $data['row'] = $rs;
            $data['nums'] = count($row);
        }
        return $data;
    }

    /**
     * 根据省获取下属区域id
     * */
    public function provinceGetdistrict($provinceid,$is_city){

        $area = D('area');
        if($is_city==1){
            $district = $area->GetArea($provinceid);
        }else if($is_city==3){
            $district[0]['id'] = $provinceid;
        } else{
            $city = $area->GetCity($provinceid);
            $cityid = '';
            foreach($city as $k=>$v)
                $cityid .= "'".$v['id']."',";
            $district = $area->where('parentid in('.substr($cityid, 0, -1).')')->field('id')->select();
            if($provinceid==820000){
                $district = $city;
            }
//            var_dump($district);die();

        }
        $districtid = '';
        foreach($district as $k=>$v)
            $districtid .= "'".$v['id']."',";
//        var_dump($districtid);
//        var_dump(substr($districtid, 0, -1));die();
        return substr($districtid, 0, -1);
    }

    /**
     * 根据城市获取下属区域id
     * */
    public function cityGetdistrict($cityid){
        $area = D('area');
        $district = $area->GetArea($cityid);
        $districtid = '';
        foreach($district as $k=>$v)
            $districtid .= "'".$v['id']."',";
        return substr($districtid, 0, -1);
    }

    /**
     * 设备类型列表ajax
     * */
    public function facilityTypeListAjax($table_Q){
        $mysql = new \Think\Model();
        $where = "";
        $page = I('get.pageNumber','','trim');
        $limit = I('get.pageSize','','trim');
        $sort = I('get.sortName','','trim');
        $sort_by = I('get.sortOrder','','trim');
        if(I('get.facname')!=="") $where.=" and t.facname like %'".I('get.facname','','trim')."'%";
        I('get.status','','trim') != -1 ? I('get.status','','trim') == 1 ? $where.=" and t.status = 1" : $where.=" and t.status = 0" : '';
        $faciliType_sql = "select t.*
                            from {$table_Q}facilitiestype t
                            WHERE 1=1 {$where}";
        $total_arr =  $mysql->query($faciliType_sql);

        $total = $total_arr[0]['numrows'];
        $total_page = ceil($total / $limit);//总页数
        if($page <= 1){
            $page = 1;
        }elseif($page >= $total_page){
            $page = $total_page;
        }
        $offset = ($page - 1 ) * $limit;
        if (!empty($sort)) $where .= " order by t.{$sort} {$sort_by}";
        if (!empty($page)) $where .= " LIMIT  {$offset},{$limit} ";
        $faciliType_list_sql = "select t.*
                                from {$table_Q}facilitiestype t
                                WHERE 1=1 {$where}";
        $row_faciliType = $mysql->query($faciliType_list_sql);
        if($row_faciliType!==false){
            $data['data']['total']=$total;
            $data['data']['rows']=$row_faciliType;
            $data['success']=200;
            $data['message']=null;
        }else{
            $data['success']=400;
            $data['message']="暂无数据!";
        }
        return $data;
    }

    /**
     * 设备列表AJAX
     * */
    public function getFacilityAjax($table_Q){
        $mysql = new \Think\Model();
        $where = "";
        $page = I('get.pageNumber','','trim');
        $limit = I('get.pageSize','','trim');
        $sort = I('get.sortName','','trim');
        $sort_by = I('get.sortOrder','','trim');
        $where .= I('get.register') != -1 ? I('get.register') == 1 ?  ' and t.register = 1' : ' and t.register = 0' : '';
        $where .= $_SESSION['admin']['id'] == 1 ? '' : ' and t.aid = '.$_SESSION['admin']['id'];
        if(I('get.serialnumber')!=='') $where.=" and t.serialnumber like '%".I('get.serialnumber','','trim')."%' ";
        if(I('get.imei')!=='') $where.=" and t.imei like '%".I('get.imei','','trim')."%' ";
        if(I('get.user_add')=='user_add'){
            $where.=" and t.id not in(SELECT faciid from ymkj_user_faci_mapping) ";
        }
        if(I('get.district')>0){
            $where_city = ' and t4.id = '.I('get.district','','trim');
        }else{
            if(I('get.city')>0) {
                $area = M('area');
                $row = $area->where('status = 1 and parentid ='.I('get.city','','trim'))->field('id')->select();
                $district_id = implode(',',$row[0]);
                $where_city = " and t4.id in({$district_id})";
            }else{
                if(I('get.province')>0) {
                    $area = M('area');
                    $row = $area->where('status = 1 and parentid ='.I('get.province','','trim'))->field('id')->select();
                    $city_id = array();
                    foreach($row as $k=>$v){
                        $city_id[]=$v['id'];
                    }
                    $row = $area->where('status = 1 and parentid in('.implode(',',$city_id).')')->field('id')->select();
                    $district_id = array();
                    foreach($row as $k=>$v){
                        $district_id[]=$v['id'];
                    }
                    $district_id = implode(',',$district_id);
                    $where_city = " and t4.id in({$district_id})";
                }
            }
        }
        $where .= !empty($where_city) ? $where_city : '';
        $facili_sql = "select COUNT(1)  AS `numrows`
                        from {$table_Q}facilities t
                        LEFT JOIN {$table_Q}facilitiestype t1
                        ON t.fid = t1.id
                        LEFT JOIN {$table_Q}user_faci_mapping t7
                        ON t.id = t7.faciid
                        LEFT JOIN {$table_Q}area t4
                        ON t.areaid = t4.id
                        LEFT JOIN {$table_Q}area t5
                        ON t4.parentid = t5.id
                        LEFT JOIN {$table_Q}area t6
                        ON t5.parentid = t6.id
                        WHERE 1=1 {$where}";
        $total_arr =  $mysql->query($facili_sql);

        $total = $total_arr[0]['numrows'];
        if($total == 0){
            echo json_encode($data=array('success'=>200,'message'=>'暂无数据!','data'=>array('total'=>0,'rows'=>0)));die();
        }
        $total_page = ceil($total / $limit);//总页数
        if($page <= 1){
            $page = 1;
        }elseif($page >= $total_page){
            $page = $total_page;
        }
        $offset = ($page - 1 ) * $limit;
//        if(I('get.fault','','trim')!=-1&&I('get.fault','','trim')!=='')
//            if(I('get.fault','','trim')==1){$where .= ' and t8.fault = 0';}else{$where .= ' and t8.fault <> 0';}
        if (!empty($sort)) $where .= " order by t.{$sort} {$sort_by}";
        if (!empty($page)) $where .= " LIMIT  {$offset},{$limit} ";
        $facili_list_sql = "select t.*,t1.facname,t4.areaname as district_areaname,t4.id as district_id,t5.areaname as city_areaname,t5.id as city_id,t6.id as province_id, t6.areaname as province_areaname,t7.uid as user_id, t1.type,t7.faciname as new_faciname
                            from {$table_Q}facilities t
                            LEFT JOIN {$table_Q}facilitiestype t1
                            ON t.fid = t1.id
                            LEFT JOIN {$table_Q}user_faci_mapping t7
                            ON t.id = t7.faciid
                            LEFT JOIN {$table_Q}area t4
                            ON t.areaid = t4.id
                            LEFT JOIN {$table_Q}area t5
                            ON t4.parentid = t5.id
                            LEFT JOIN {$table_Q}area t6
                            ON t5.parentid = t6.id
                            WHERE 1=1 {$where}";
//        LEFT JOIN (
//            SELECT t10.data38 as fault ,t9.id
//                                  FROM {$table_Q}facilities t9
//                                  LEFT JOIN (
//            SELECT MAX(t11.addtime) as max_time,t11.fid
//                                        FROM {$table_Q}receivelog t11
//                                        GROUP BY t11.fid
//                                  ) t12
//                                  ON t9.id = t12.fid
//                                  LEFT JOIN {$table_Q}receivelog t10
//                                  ON t10.addtime = t12.max_time  and t10.fid = t12.fid
//                            ) t8
//                            ON t8.id = t.id
//        var_dump($facili_list_sql);die();
        $row_facili = $mysql->query($facili_list_sql);
        $receivelog = M('receivelog');
        $userinfo = D('userinfo');
        foreach($row_facili as $k=>$v){
            //如果有待发送命令则设备列表的显示状态查询待发送命令表
            $fault = $receivelog->where('fid = '.$v['id'])->order('addtime desc')->limit('0,1')->find();
            $row_facili[$k]['fault'] = $fault['data38'];
            if($v['is_order']==2){
                $row_pro = $userinfo->getProOrder($v['id'],$fault);
                $row_facili[$k]['is_off'] = $row_pro['data11_7'];
                $row_facili[$k]['is_cool'] = $row_pro['data11_2'];
            }else{
                $row_facili[$k]['is_off'] = $fault['data11_7'];
                $row_facili[$k]['is_cool'] = $fault['data11_2'];
            }
        }

        if($row_facili!==false){
            $row_new = $this->getIsGood($_GET['new_is_good'],$row_facili);
            $data['data']['total']=count($row_new);
            $data['data']['rows']=$row_new;
            $data['success']=200;
            $data['message']=null;
        }else{
            $data['success']=400;
            $data['message']="暂无数据!";
        }

        return $data;
    }

    /**
     * 按设备状态提取数据
     * */
    private function getIsGood($new_is_good,$row_facili){
        $new_array = [];
        if($new_is_good==1){
            foreach ($row_facili as $k=>$v)
                if($v['fault']==='0')
                    $new_array[]=$row_facili[$k];
        }else if($new_is_good==2){
            foreach ($row_facili as $k=>$v)
                if($v['fault'])
                    $new_array[]=$row_facili[$k];
        }else if($new_is_good==3) {
            foreach ($row_facili as $k=>$v)
                if($v['fault']===null)
                    $new_array[]=$row_facili[$k];
        }else{
            $new_array = $row_facili;
        }
        return $new_array;
    }

    /**
     * 设备历史记录
     * @fid 设备id 数组
     * @address 区域 省，市，区
     * @times 0=>起始日期，1=>结束日期
     * */
    public function historyList($fid=array(),$address=array(),$times=array()){
        $table_Q = C("DB_PREFIX");
        $page = I('get.pageNumber','','trim');
        $limit = I('get.pageSize','','trim');
        $sort = I('get.sortName','','trim');
        $sort_by = I('get.sortOrder','','trim');
        $where = '';
        if(count($fid)>0){
            if(count($fid)==1){
                $where .=' and fid = '.$fid[0];
            }else{
                $where .=' and fid in('.implode(',',$fid).')';
            }
        }
        if(count($times)>0){
            if(!empty($times[0]))
                $where .= ' and addtime >= '.$times[0];
            if(!empty($times[1]))
                $where .= ' and addtime < '.$times[1];
        }
        $sql_page = "  
                  select *, from_unixtime(t.addtime,'%Y%m%d') as day ,addtime
                  from (
                      SELECT
			              *
		              FROM
			          {$table_Q}receivelog
			          ORDER BY 
			          addtime desc
                  )as t 
                  WHERE  1=1 {$where} 
                  GROUP BY 
                      DAY,fid";
//        var_dump($sql_page);die();
        $total_arr =  $this->query($sql_page);
        $total = count($total_arr);
        if($total == 0){
            echo json_encode($data=array('success'=>200,'message'=>'暂无数据!','data'=>array('total'=>0,'rows'=>0)));die();
        }
        $total_page = ceil($total / $limit);//总页数
        if($page <= 1){
            $page = 1;
        }elseif($page >= $total_page){
            $page = $total_page;
        }
        $offset = ($page - 1 ) * $limit;

        $sql = " select *, from_unixtime(t.addtime,'%Y%m%d') as day ,addtime
                  from (
                      SELECT
			              *
		              FROM
			          {$table_Q}receivelog
			          ORDER BY 
			          addtime desc
                  )as t 
                  WHERE  1=1 {$where} 
                  GROUP BY 
                      DAY,fid
                  ";
        if (!empty($sort)) $sql .= " order by {$sort} {$sort_by}";
        if (!empty($page)) $sql .= " LIMIT  {$offset},{$limit} ";
//        var_dump($sql);die();
        $row = $this->query($sql);
        $data['total'] = $total;
        $data['row'] = $row;
        $data['sum_up'] = $this->energyConsumption($row,$times);


        foreach($data['row'] as $de_k=>$de_v){
            unset($data['row'][$de_k]['original']);
        }

        return $data;
    }

    /**
     * energyConsumption
     * 能耗
     * @data 数据数组 仅限状态返回表信息
     * */
    public function energyConsumption($data,$times){
        $start = $data[0];
        $end = end($data);
//        var_dump(round(($times[1]-$times[0])/82400));die();
        $data_return['energy'] = (floatval($end['data34'].$end['data35'].$end['data36'].'.'.$end['data37']) - floatval($start[ 'data34'].$start[ 'data35'].$start['data36'].'.'.$start['data37'])) / floor(($times[1]-$times[0])/82400);
        $data_return['indoor_t'] = $end['data42'];
        $data_return['outdoor'] = substr($end['data39'],0,1)==1 ? substr($end['data39'],1) : $end['data39'];
        return $data_return;
    }

    /**
     * 设备历史状态中的详细状态
     * */
    public function minute(){
        $facility = M('facilities');
        $type_id = $facility->where('id = '.I('post.fid','','trim'))->field('fid')->find();
        $recivelog = M('receivelog');
        $status = $recivelog->where('id = '.I('post.id','','trim'))->find();
        if($status['data38']>0){
            $facilitiestype = D('facilitiestype');
            $error = $facilitiestype->error;
            $data['error'] = $error[$type_id[$status]['data38']];
        }else{
            $data['error'] = -1;
        }
        if($status!=false){
            $data['row'] = $status;
            $data['code'] = 200;
        }else{
            $data['code'] = 400;
            $data['msg'] = '暂无数据!';
        }
        unset($data['row']['original']);
        echo json_encode($data);die();

    }

    /**
     * 省级地图设备AJAX
     * @is_city 是否查询的城市 自己看吧
     * */
    public function getProvinceFacility($table_Q,$post_province,$is_run,$is_city=0,$is_get_run = 0){

        $mysql = new \Think\Model();
        $district = $this->provinceGetdistrict($post_province,$is_city);

        if($district==false){
            echo json_encode(array('code'=>400,'msg'=>'暂无数据！'));
            die();
        }
        $facilityList = $this->query("
            select t.* , t1.shortname,t1.areaname,t2.id as pro_id,t2.shortname as pro_shortname,t2.areaname as pro_areaname
            from {$table_Q}facilities t
            LEFT JOIN {$table_Q}area t1
            ON t.areaid = t1.id
            LEFT JOIN {$table_Q}area t2
            ON t1.parentid = t2.id
            WHERE t.areaid in($district)
        ");
//        var_dump($district);
//        var_dump($this->_sql());die();
        if($facilityList == false){
            echo json_encode(array('code'=>400,'msg'=>'暂无数据！'));
            die();
        }
        if($is_get_run === 1){
            return count($facilityList);
        }
        foreach ($facilityList as $k => $v)
            $facilityId[] = $v['id'];
        $new_facilityList = $this->facilityIdToStutas($table_Q,$facilityId,$facilityList,$is_run);
        $sum = $this->getStatistics($new_facilityList,$facilityId,$table_Q,2);
//        var_dump($sum);die();
        return $sum;
//        echo json_encode($sum);die();
    }

    /**
     * 处理图表所需参数
     * */
    public function getStatistics($new_facilityList,$facilityid,$table_Q,$lv){
        $history_run_time = S('history_run_time');
        /**
         *设备运行起始值
         */
//        echo  '<pre>';
//        var_dump($new_facilityList);die();
//        $history_fid = '';
//        foreach ($new_facilityList as $k=>$v){
//            $history_fid .= $v['id'].',';
//        }
//        $history_fid = substr($history_fid, 0, -1);

        $sum = [
            'proportion' => 0,
            'indoor_t' => 0,
            'energy' => 0,
            'runtime' => 0,
            'new_arr' =>array()
        ];
        foreach ($new_facilityList as $k=>$v){
            $sum['proportion'] += $v['area'];
            if(isset($v['data42'])){
                $sum['indoor_t'] += $v['data42'];
                $data34 = $v['data34'] == false ? '00' : $v['data34'];
                $data35 = $v['data35'] == false ? '00' : $v['data35'];
                $data36 = $v['data36'] == false ? '00' : $v['data36'];
                $data37 = $v['data37'] == false ? '00' : $v['data37'];
                $his_data34 = $v['his_data34'] == false ? '00' : $v['his_data34'];
                $his_data35 = $v['his_data35'] == false ? '00' : $v['his_data35'];
                $his_data36 = $v['his_data36'] == false ? '00' : $v['his_data36'];
                $his_data37 = $v['his_data37'] == false ? '00' : $v['his_data37'];
                $sum['energy'] += floatval($data34.$data35.$data36.'.'.$data37)-floatval($his_data34.$his_data35.$his_data36.'.'.$his_data37);
            }
            $sum['runtime'] += $v['fruntime'];
            if($lv==1){
                $sum['new_arr'][$v['provin_id']][] = $v;
            }else if($lv==2){
                $sum['new_arr'][$v['pro_id']][] = $v;
            }
        }
        $data_new =[];//返回的级别设备数量
        if($lv==1){
            foreach ($sum['new_arr'] as $k=>$v)
                $data_new[] = array('name' => $v[0]['provin_shortname'], 'value' => count($v));
        }else if($lv==2){
            foreach ($sum['new_arr'] as $k=>$v)
                $data_new[] = array('name' => $v[0]['pro_areaname'], 'value' => count($v));
        }else{
            die('非法操作！');
        }

        /*
         * 碳排放
         * 计算所有设备安装到现在的 改了之后不用了，唉
         * */
        $mysql = new \Think\Model();
        $where = ' and t.fid in('.implode(',',$facilityid).')';
        $where .= ' and t.addtime < '.$history_run_time[1];
        $where .= ' and t.addtime > '.$history_run_time[0];
        $sql = "
            select t.*, from_unixtime(t.addtime,'%Y%m%d') as day ,t.addtime,t1.area as area_fid
                  from (
                      SELECT
			              *
		              FROM
			          {$table_Q}receivelog
			          WHERE data11_7 = 1
			          ORDER BY 
			          addtime desc
                  )as t 
                  LEFT JOIN {$table_Q}facilities t1 
                  ON t.fid = t1.id
                  WHERE  1=1 {$where} 
                  GROUP BY 
                      DAY,t.fid
        ";
        $carbon_row = $mysql->query($sql);
        $area_all = 0;
        //当前时间段采暖面积和
        foreach ($carbon_row as $k=>$v)
            $area_all += $v['area_fid'];


        $new_indoor = array_column($new_facilityList,'data42');
//        echo '<pre>';
//        print_r($new_indoor);die();
        $new_indoor_max =$new_indoor[array_search(max($new_indoor),$new_indoor)];
        $new_indoor_min =$new_indoor[array_search(min($new_indoor),$new_indoor)];
//        var_dump(array_column($new_facilityList,'data42'));die();
//        $carbon_row = ($history_run_time[1]-$history_run_time[0])/82400;
        $carbon = $area_all*0.417-count($carbon_row)*8.785;
        $sum = [
            'sum_energy' => round($sum['energy'],2),
            'proportion' => round($sum['proportion'],2),
            'indoor_t' => round($sum['indoor_t']/count($new_facilityList),2),
            'energy' => round($sum['energy']/count($new_facilityList),2),
            'runtime' => round($sum['runtime']/count($new_facilityList)/82400,2),
            'carbon' => round($carbon,2),
            'new_arr' =>$data_new,
            'fidsum' => count($new_facilityList),
            'new_indoor_max' => $new_indoor_max,
            'new_indoor_min' => $new_indoor_min
        ];

        if(empty($sum['new_arr'])){
            $sum['carbon'] = 0;
        }
        return $sum;
//        echo '<pre>';
//        var_dump($sum);die();
    }


    /**
     * 根据设备id查询设备状态信息
     * @facilityid array
     * @return 设备最新信息 array
     * */
    public function facilityIdToStutas($table_Q,$facilityid,$facilityList,$is_run=-1){
        $receivelog = M('receivelog');
        $where = ' and t.fid in('.implode(',',$facilityid).')';
//        var_dump($where);die();
//        if($is_run!==-1){
//            $where .= $is_run == 1 ? ' and t.data38 = 0' : ' and t.data38 > 0';
//        }
        $history_run_time = S('history_run_time');
        $history_where = ' and t1.addtime < '.$history_run_time[1];
        $sql = "select t.*,t6.*
                from {$table_Q}receivelog t
                RIGHT JOIN (
                        SELECT MAX(t1.addtime) AS max_time,t1.fid
                        FROM {$table_Q}receivelog t1
                        WHERE 1=1 {$history_where}
                        GROUP BY
                        t1.fid
                ) t2 ON t.fid = t2.fid AND t.addtime = t2.max_time
                LEFT JOIN (
                        select t3.fid as his_id,t3.data34 as his_data34,t3.data35 as his_data35,t3.data36 as his_data36,t3.data37 as his_data37
                    from {$table_Q}receivelog t3
                    RIGHT JOIN (
                            SELECT min(t4.addtime) AS max_time,t4.fid
                            FROM {$table_Q}receivelog t4
                            WHERE 1=1 and t4.addtime > {$history_run_time[0]}
                            AND  t4.addtime < {$history_run_time[1]}
                            GROUP BY
                            t4.fid
                    ) t5 ON t3.fid = t5.fid AND t3.addtime = t5.max_time
                    WHERE 1=1 and t5.fid in(".implode(',',$facilityid).")
                ) t6
                ON t.fid = t6.his_id
                WHERE 1=1 {$where}
                ";
//        var_dump($sql);die();
        $receivelogList = $receivelog->query($sql);
//        $new_facilityList = [];
        foreach ($facilityList as $k => $v)
            foreach ($receivelogList as $k_log => $v_log)
                if($v['id'] == $v_log['fid'])
                    $facilityList[$k] = $v + $v_log;

//        var_dump($is_run);die();
         if($is_run!==-1){
             foreach ($facilityList as $k=>$v){
                 if($is_run==1&&$v['data38']>0){
                     unset($facilityList[$k]);
                 }else if($is_run==2&&$v['data38']==0){
                     unset($facilityList[$k]);
                 }
             }
         }

        return $facilityList;
//        var_dump($receivelogList);die();
    }

}