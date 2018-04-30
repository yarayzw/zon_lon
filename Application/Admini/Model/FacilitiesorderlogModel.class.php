<?php
/**
 * Created by PhpStorm.
 * User: yara
 * Date: 2017/6/13
 * Time: 14:17
 */
namespace Admini\Model;

use Think\Model;

class FacilitiesorderlogModel extends Model
{

    /**
     * 记录数组
     * */
    private $rows = array(
        'data1' => '当前联机号',
        'data2' => '命令',
//        'data3' => '设定室内温度',
//        'data4' => '制冷设定温度',
//        'data5' => '制热设定温度',
        'data6' => '化霜进入温度',
        'data7' => '化霜退出温度',
        'data8' => '化霜周期',
//        'data9' => '温度正常补',
        'data10' => '压缩机重启偏差',
//        'data11_7' => '开/关机',
//        'data11_6' => '锁定/不锁定',
//        'data11_5' => '强制化霜',
//        'data11_4' => '0',
//        'data11_3' => '参数恢复',
//        'data11_2' => '制热',
//        'data11_1' => '0',
//        'data11_0' => '制冷',
        'data12' => '改pc有写操作时的联机号',
//        'data13' => '设定温度上限',
        'data14' => '退出化霜后电加热延时关闭',
        'data15' => '化霜温差1',
        'data16' => '化霜温差2',
        'data17' => '电子膨胀阀调节周期',
        'data18' => '过热度',
        'data19' => '膨胀阀开大的排气温度',
        'data20' => '化霜时膨胀阀的开度',
        'data21' => '膨胀阀最小开度',
        'data22' => '过热度补偿',
//        'data23' => '电流设定',
//        'data24' => '风机控制参数',
        'data25' => '联机总数',
        'data26' => '化霜时间',
//        'data27' => '备用',
//        'data28' => '备用',
        'data29' => '客户类型',
//        'data30' => '备用'
    );

    /**
     * 获取设备命令日志
     * */
    public function getSendLog($tableQ){
        $where = '';
        if(I('get.serialnumber','','trim')!='')
            $where .= " and t1.serialnumber like '%".I('get.serialnumber','','trim')."%'";
        if(I('get.imei','','trim')!='')
            $where .= " and t1.imei like '%".I('get.imei','','trim')."%'";
        if (I('get.E_time')!="") {
            $where .= " and t.addtime < " . trim(strtotime(I('get.E_time')));
        }
        if (I('get.Q_time')!="") {
            $where .= " and t.addtime > " . trim(strtotime(I('get.Q_time')));
        }
        $page = I('get.pageNumber','','trim');
        $limit = I('get.pageSize','','trim');
        $sort = I('get.sortName','','trim');
        $sort_by = I('get.sortOrder','','trim');
        $page_sql = "select count(*) as numrows 
                    from {$tableQ}facilitiesorderlog t
                    LEFT JOIN {$tableQ}facilities t1
                    ON t.fid = t1.id
                    WHERE 1=1  {$where}";
        $total_arr =  $this->query($page_sql);
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
        $sql_list = "
             select t.* ,t1.serialnumber ,t1.imei
             from {$tableQ}facilitiesorderlog t
             LEFT JOIN {$tableQ}facilities t1
             ON t.fid = t1.id
             WHERE 1=1  {$where}
        ";
        $row = $this->query($sql_list);
        if($row!==false){
            $data['data']['total']=$total;
            $data['data']['rows']=$row;
            $data['success']=200;
            $data['message']=null;
        }else{
            $data['success']=400;
            $data['message']="暂无数据!";
        }
        return $data;
    }

    /**
     * 编写日志
     * */
    public function setTransform($data,$row_receivelog=''){
        $receivelog = M('receivelog');
        if($row_receivelog==''){
            $row_receivelog = $receivelog->where('fid = '.$data['fcid'])->order('addtime desc')->find();
        }
        $log = '';
        foreach ($this->rows as $k=>$v){
            if (isset($row_receivelog[$k]))
                $log .= $data[$k] == $row_receivelog[$k] ? '' : $v.'改为'.$data[$k].',';
        }
        $data_insert['fid'] = $data['fcid'];
        $data_insert['memo'] = $log;
        $data_insert['addtime'] = time();
        $data_insert['uid'] = $_SESSION['admin']['id'];
        $data_insert['uname'] = $_SESSION['admin']['account'];
        $this->data($data_insert)->add();
    }

}