<?php
/**
 * Created by PhpStorm.
 * User: shawn
 * Date: 2018/4/27
 * Time: 19:08
 */

namespace Home\Model;
use Think\Model;

class EquipmentStatisticsModel extends Model
{
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


}