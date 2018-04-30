<?php
/**
 * Created by PhpStorm.
 * User: dyxdw
 * Date: 2016/12/5
 * Time: 15:05
 */

namespace Admini\Controller;
use Think\Auth;
use Think\Controller;

class CommonController extends Controller
{
    public  $table = "";
    public function _initialize(){
        $this->table = C("DB_PREFIX");
        if(!is_login()){
            $this->redirect("Public/login");
        }
        $this->uid=session("admin.id");
        $this->account=session("admin.account");
        $this->auth=new Auth();
    }

    /**
     * 验证页面权限
     * @param bool $ajax
     * @param string $name
     * @param int $type
     * @return mixed
     */
    public function CheckAuth($ajax=false,$name='',$type=1){
        if(empty($name)){
            $name=MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME;
        }
        if(session('admin.id')==1) return true;
        $r = $this->auth->check($name,$this->uid,$type);
        if($ajax){
            return $r;
        }else{
            if(!$r){
                $this->redirect("Public/error_permission");
            }
        }
    }

    /**记录操作日志
     * @param string $tablename
     * @param string $log
     */
    public function writelog($tablename='',$log=''){
        systemlog($tablename,$this->uid,$this->account,$log);
    }


    /**
     * 获取列表
     *
     * @param int $page 页码
     *        int $limit 每页显示条数
     *        string $where 条件
     *        string $order 排序字段
     *        string $by 排序值
     * @return array
     */
    public function getList($table,$page=1,$limit=200,$where='',$order='id',$by='desc'){
//        var_dump($order);die();
        $tableQ = C('DB_PREFIX');
        $condition = 'WHERE 1 = 1 ';
        $Model = new \Think\Model();
        if($where != ''){
            $condition .= $where;
        }
        if($limit == 0){
            $limit = 200;
        }
        //总行数
        $query = "SELECT COUNT(1)  AS `numrows`  FROM `{$tableQ}{$table}` {$condition}";

        $total_arr = $Model->query($query);
        $total = $total_arr[0]['numrows'];
        $total_page = ceil($total / $limit);//总页数
        if($page <= 1){
            $page = 1;
        }elseif($page >= $total_page){
            $page = $total_page;
        }
        $offset = ($page - 1 ) * $limit;
//        var_dump($page);die();
        $_RE = array('total'=>$total);
        //查询字段
        $fields = $this->getListFields();
        $sql = "SELECT {$fields} FROM {$tableQ}{$table} {$condition} ORDER BY {$order} {$by} LIMIT {$offset} ,{$limit} ";
//        var_dump($sql);die();
        $rs = $Model->query($sql);
        if(!$rs){
            $_RE['rows'] = '';
            return $_RE;
        }else{
            $_RE['rows'] = $rs;
        }

//        若存在方法preprocess，将返回数据交给preprocess处理
        if(method_exists($this,'preprocess')){
            $stime = $this->query_stime;
            $etime = $this->query_etime;
            $_RE['rows'] = $this->preprocess($rs,$stime,$etime);
        }

        return $_RE;
    }

    /**
     * 将属性list_fields转为字符串
     */
    protected function getListFields(){
        if(property_exists(get_class($this),'list_fields')){
            $fields = '*';
            if(!empty($this->list_fields)){
                $fields = implode(',',$this->list_fields);
            }
        }else{
            $fields = '*';
        }
        return $fields;
    }
    //返回固定格式数组
    public function returnArr($code=200,$msg='操作成功',$href=''){
        $now_ac = CONTROLLER_NAME . '/' . ACTION_NAME;
        $href = empty($href) ? 'Home/index' : $now_ac;
        return array('code'=>$code,'message'=>$msg,'href'=>$href);
    }


    /**
     * 树形数据生成
     * @param array $items 原始数据
     * @param int $id 主键字段（默认：id）
     * @param int $pid 父节点标识（默认：pid）
     * @param string $son 子节点标识（默认：children）
     * @param string $attributes 是否添加attributes属性
     * @param bool $close 超过二级是否不显示
     * @return array 树形数据
     */
    function genTree($items, $id = 'id', $pid = 'pid', $textFiled = 'name', $closed = false, $iconCls = 'icon_cls', $son = 'nodes')
    {
        $tree = array(); // 格式化的树
        $tmpMap = array(); // 临时扁平数据
        foreach ($items as $item) {
            if (!isset($item['id'])) {
                $item['id'] = $item[$id];
            }
            if (!isset($item['text'])) {
                $item['text'] = $item[$textFiled];
                unset($item[$textFiled]);
            }
            if (!isset($item['iconCls'])) {
                $item['iconCls'] = $item[$iconCls];
            }

            if (isset($item['auth_path']) && $closed == true) {
                $count = substr_count($item['auth_path'], ',');
                if ($count > 2) {
                    //是否存在子节点
                    foreach ($items as $k => $v) {
                        if ($item[$id] == $v[$pid]) {
                            $item['state'] = 'closed';
                        }
                    }
                }
            }
            $tmpMap[$item[$id]] = $item;
        }
//        echo "<pre>";
//        var_dump($tmpMap);die();
        foreach ($items as $item) {
            if (isset($tmpMap[$item[$pid]])) {
                $tmpMap[$item[$pid]][$son][] = &$tmpMap[$item[$id]];
            } else {
                $tree[] = &$tmpMap[$item[$id]];
            }
        }
        return $tree;
    }
}