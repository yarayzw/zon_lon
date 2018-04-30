<?php
/**
 * Created by PhpStorm.
 * User: shawn
 * Date: 2018/4/27
 * Time: 19:08
 */

namespace Home\Model;
use Think\Model;
use Home\Model\CommandListModel;
use Home\Model\ContainerTypeModel;

class EquipmentStatisticsModel extends Model
{
    protected static $table_name = 'equipment_statistics';
    
    /**
     * @param $start_time
     * @param $end_time
     * @param array $equipment_id
     * @return mixed
     */
    public static function getModelByTimeAndEquipment($start_time, $end_time, $equipment_id)
    {
        $model = M('equipment_statistics');
        $condition = [
            'createtime' => ['between', "$start_time, $end_time"],
            'equipment_id' => ['in', $equipment_id]
        ];
        return $model->where($condition)->select();
    }

    /**
     * [createData ]
     * @param  integer $equipment_id                  [description]
     * @param  integer $quantity                      [description]
     * @param  [type]  $this_container_use_percentage [description]
     * @return [type]                                 [description]
     *
     * 
     * 检测该设备Id一小时之内是否插入过数据
     * 插入过：更新产生量
     * 未插入：插入数据
     */
    public static function createData($equipment_id = 0, $this_container_use_percentage = 0){
        $command = (new CommandListModel())->where(['equipment_id' => $equipment_id])->order('createtime DESC')->field('container_use_percentage')->find();
        if(!empty($command)){
            $container_type = (new ContainerTypeModel())->where(['id' => $equipment_id])->field('capacity')->find();
            if($command['container_use_percentage'] > $this_container_use_percentage){
                $quantity = ((100 - $this_container_use_percentage) + $command['container_use_percentage']) * $container_type['capacity'] / 100;
            }else{
                $quantity = ($this_container_use_percentage - $command['container_use_percentage']) * $container_type['capacity'] / 100;
            }
            $model = M(self::$table_name);
            $data = [
                'createtime' => strtotime(date('Y-m-d H', time()) . ':00:00'),
                'equipment_id' => $equipment_id
            ];
            $info = $model->where($data)->find();
            if(empty($info)) $model->add($data);
            else{
                if((float)$quantity == 0) return ;
                $data['id'] = $info['id'];
                $data['quantity'] = $quantity + $info['quantity'];
                $model->save($data);
            }
        }
    }

    /**
     * [getModelByTimeCount 获取指定时间内总产量]
     * @param  integer $start_time   [description]
     * @param  [type]  $end_time     [description]
     * @param  [type]  $equipment_id [description]
     * @return [type]                [description]
     */
    public static function getModelByTimeCount($start_time = 0, $end_time, $equipment_id = []){
        $model = M('equipment_statistics');
        $condition = [
            'createtime' => ['between', "{$start_time}, {$end_time}"],
            'equipment_id' => ['in', $equipment_id]
        ];
        return $model->where($condition)->SUM('quantity');
    }
}