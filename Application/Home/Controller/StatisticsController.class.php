<?php
/**
 * Created by PhpStorm.
 * User: shawn
 * Date: 2018/4/30
 * Time: 12:42
 */

namespace Home\Controller;



use Home\Model\EquipmentStatisticsModel;

class StatisticsController extends PublicController
{
    public function getStatisticsByDay()
    {
        $day = isset($_POST['day']) ? $_POST['day'] : date('Y-m-d');
        $address_no = isset($_POST['address']) ? $_POST['address'] : null;
        if (! $address_no) $this->ajax_return(10001, '', '数据缺失');
        $start_time = strtotime($day);
        $end_time = $start_time + 24 * 3600 - 1;
        $statistics_data = EquipmentStatisticsModel::getModelByTimeAndEquipment($start_time, $end_time, $address_no);
        if (!$statistics_data) $this->ajax_return(10001, '', '信息不存在');
        // 总和
        $result = ['count' => 0];
        $all_data = array_column($statistics_data, 'quantity', 'createtime');
        $max = max($all_data);
        $min = min($all_data);
        foreach ($statistics_data as $key => $value) {
            $result['count'] += $value['quantity'];
            $result['abscissa'][(int)date('H', $value['createtime'])] = [(int)date('H', $value['createtime']), $value['quantity']];
            if ($value['quantity'] == $max) $result['max'][] = [date('H', $value['createtime']), $max];
            if ($value['quantity'] == $min) $result['min'][] = [date('H', $value['createtime']), $min];
        }
        // 对不存在的时段插入数据中
        for ($i = 1; $i < 25; $i++) if (! key_exists($i, $result['abscissa'])) $result['abscissa'][$i] = [$i, 0];
        ksort($result['abscissa']);
        $result['ordinate'] =[ceil($max / 10) * 10,  ceil($max / 10)];
        // 平均值计算
        $result['average'] = round($result['count'] / 24, 2);
        $result['max'] = $this->getPercentage($result['average'], $result['max'], 1);
        $result['min'] = $this->getPercentage($result['average'], $result['min'], 1);
        $this->ajax_return(10000, $result);
    }

    /**
     * 获取百分比
     * @param $average
     * @param $array
     * @param int $index 需要比较的数字
     * @return string
     */
    public function getPercentage($average, $array, $index)
    {
        foreach ($array as $k => $v) {
            $distance = abs($average - $v[$index]);
            $array[$k][$index] = (round($distance / $average, 2) * 100) . '%';
        }
        return $array;
    }

}