<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/27
 * Time: 19:35
 */
namespace Admini\Model;

class EquipmentListModel extends PublicModel
{
    public function __construct(){
        parent::__construct();
    }
    protected $table = 'ymkj_equipment_list';
    //显示字段
    protected $list_fields = array('id','equipment_address','createtime','is_del');

    public function preprocess($rs,$stime,$etime){

        foreach ($rs as $k=>$v){
            $rs[$k]['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
        }

        return $rs;
    }

}