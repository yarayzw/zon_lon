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
    private function getStatisticsByDay($address_no, $day)
    {
        $day = !isset($day) ? $day : date('Y-m-d');
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
        $abscissa_middle = [];
        foreach ($statistics_data as $key => $value) {
            $result['count'] += $value['quantity'];
            $abscissa_middle[(int)date('H', $value['createtime'])] = [(int)date('H', $value['createtime']), $value['quantity']];
            if ($value['quantity'] == $max) $result['max'][] = [(int)date('H', $value['createtime']), $max];
            if ($value['quantity'] == $min) $result['min'][] = [(int)date('H', $value['createtime']), $min];
        }
        // 对不存在的时段插入数据中
        for ($i = 1; $i < 25; $i++) if (! key_exists($i, $abscissa_middle)) $abscissa_middle[$i] = [$i, 0];
        ksort($abscissa_middle);
        // 平均值计算
        $average = round($result['count'] / 24, 2);
        $result['abscissa'] = [array_column($abscissa_middle, 0), array_column($abscissa_middle, 1)];
        $average_arr = [];
        for ($i = 1; $i < 25; $i++) $average_arr[] = $average;
        array_push($result['abscissa'], $average_arr);
        $result['ordinate'] =[ceil($max / 10) * 10,  ceil($max / 10)];
        $result['max'] = $this->getPercentage($average, $result['max'], 1, 0);
        $result['min'] = $this->getPercentage($average, $result['min'], 1, 0);
        $this->ajax_return(10000, $result);
    }

    /**
     * 获取百分比
     * @param $average
     * @param $array
     * @param int $index 需要比较的数字
     * @param $time_index
     * @param int $type
     * @return array
     */
    public function getPercentage($average, $array, $index, $time_index, $type = 0)
    {
        $new_array = [];
        foreach ($array as $k => $v) {
            $distance = abs($average - $v[$index]);
            $point = $average != 0 ? (round($distance / $average, 3) * 100) . '%' : 0;
            $str = is_int($v[$time_index]) ? $v[$time_index] . '点' : $v[$time_index];
            if($type == 2) $str .= '月 ';
            $str .= '产生的垃圾量比平均量';
            $str .= $average > $v[$index] ? '低' : '高';
            $str .= $point;
            $new_array[] = $str;
        }
        return $new_array;
    }

    /**
     * [data_statistics 数据统计]
     * @return [type] [description]
     *
     * 类型：
     *    天 0
     *    周 1
     *    月 2
     */
    public function data_statistics(){
        $data = I('request.');
        $data['type'] = empty($data['type']) ? 0 : $data['type'];
        if(!in_array($data['type'], [0, 1, 2, 4])) $this->ajax_return(10005, '', '类型有误！');
        $where_data['start_time'] = is_date($data['start_time']) ? 0 : strtotime($data['start_time']);
        $where_data['end_time'] = is_date($data['end_time']) ? 0 : strtotime($data['end_time']);
        if((int)$data['type'] == 4) return $this->getStatisticsByDay($data['equipment_id'], date('Y-m-d', $where_data['start_time']));
        
        $where_data['equipment_id'] = implode(',', $data['equipment_id']);
        if(empty($where_data['equipment_id'])) $this->ajax_return(10002, '', '设备Id不可为空！');
        if(empty($where_data['start_time'])) $this->ajax_return(10003, '', '开始时间为必填项！');
        if(empty($where_data['end_time'])) $this->ajax_return(10004, '', '结束时间为必填项！');
        $max_data = $min_data = $return_data = $date_list = [];
        $this_value = $agv_data = $iii = $count = $max_val = $min_val = 0;
        switch ((int)$data['type']) {
            case 1: //周
                $date_list = getWeek(date('Y-m-d', $where_data['start_time']), date('Y-m-d', $where_data['end_time']));
                foreach ($date_list as $key => $value) {
                    $this_value = EquipmentStatisticsModel::getModelByTimeCount(strtotime($value[0] . ' 00:00:00'), strtotime($value[1] . ' 23:59:59'), $where_data['equipment_id']);
                    $this_value = empty($this_value) ? 0 : $this_value;
                    $count += $this_value;
                    $return_data[$key] = [
                        $value[0] . ' ~ ' . $value[1],
                        $this_value,
                        0
                    ];
                }
                break;
            case 2: //月份
                $date_list = monthList($where_data['start_time'], $where_data['end_time']);
                foreach ($date_list as $kkk => $vvv) {
                    $start_day = strtotime(date( 'Y-m-01 00:00:00', strtotime($vvv) ));
                    $end_day = strtotime(date( 'Y-m-' . date( 't', strtotime($vvv) ) . ' 23:59:59', strtotime($vvv) ));
                    $this_value = EquipmentStatisticsModel::getModelByTimeCount($start_day, $end_day, $where_data['equipment_id']);
                    $this_value = empty($this_value) ? 0 : $this_value;
                    $count += $this_value;
                    $return_data[$kkk] = [
                        $vvv,
                        $this_value,
                        0
                    ];
                }
                break;
            default: //默认为天
                $date_list = getDateRange2(date('Y-m-d', $where_data['start_time']), date('Y-m-d', $where_data['end_time']));
                foreach ($date_list as $k => $v) {
                    $this_value = EquipmentStatisticsModel::getModelByTimeCount(strtotime($v . ' 00:00:00'), strtotime($v . ' 23:59:59'), $where_data['equipment_id']);
                    $this_value = empty($this_value) ? 0 : $this_value;
                    $count += $this_value;
                    $return_data[$k] = [
                        $v,
                        $this_value,
                        0
                    ];
                }
                break;
        }
        /**
         * 最大值、最小值的数组
         */
        $max_val = max(array_column($return_data, '1', 0));
        $min_val = min(array_column($return_data, '1', 0));
        foreach ($return_data as $key => $value) {
            if($max_val == $value[1]) $max_data[] = $value;
            if($min_val == $value[1]) $min_data[] = $value;
        }
        $agv_data = number_format($count / count($date_list), 2);
        // $max_data['max'] = $this->getPercentage($agv_data, $max_data, 1);
        // $min_data['min'] = $this->getPercentage($agv_data, $min_data, 1);
        $max_data['max'] = $this->getPercentage($agv_data, $max_data, 1, 0, $data['type']);
        $min_data['min'] = $this->getPercentage($agv_data, $min_data, 1, 0, $data['type']);
        
        $abscissa = [
            array_column($return_data, 0),
            array_column($return_data, 1),
            array_column($return_data, 2)
        ];
        /**
         * 平均值追加到返回数组中
         */
        foreach ($abscissa as $key => $value) {
            if($key == 2){
                foreach ($value as $k => $value) $abscissa[$key][$k] = $agv_data;
            }
        }
        $return_array = [
            'count' => $count,
            'abscissa' => $abscissa,
            'max' => $max_data, //最大值
            'min' => $min_data, //最小值
            'average' => $agv_data, //平均值
            'ordinate' => [ //纵坐标
                ceil($max_val / 10) * 10,
                ceil($max_val / 10)
            ]
        ];
        $this->ajax_return(10000, $return_array, '数据查询成功！');
    }
}