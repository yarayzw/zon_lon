<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/27
 * Time: 19:35
 */
namespace Admini\Model;

class ContainerTypeModel extends PublicModel
{
    public function __construct(){
        parent::__construct();
    }
    protected $table = 'ymkj_container_type';
    //显示字段
    protected $list_fields = array('id','name','longs','wide','higt','is_del','capacity','createtime');

    public function preprocess($rs,$stime,$etime){

        foreach ($rs as $k=>$v){
            $rs[$k]['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
        }

        return $rs;
    }

}