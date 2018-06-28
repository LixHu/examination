<?php
/**
 * Created by PhpStorm.
 * User: shengye
 * Date: 2018/6/24
 * Time: 18:40
 * 数据库处理类
 */

namespace lib;


class Datebase
{
    // ip
    private $host;
    // 数据库
    private $db;
    // 密码
    private $pass;
    // 端口
    private $port;
    //方法
    protected $method = array('strict','order','alias','having','group','lock','distinct','auto','filter','validate','result','token','index','force');
    // 最近错误信息
    private $error;
    // 数据库对象
    private $dbobj;
    // 数据库表
    private $table;
    // 数据库表对象
    private $tableobj;
    // 数据信息
    public $data = array();
    // 查询表达式参数
    protected $options = array();

    function __construct($host='', $db='', $user= '', $pass='', $port='3306')
    {
        if(!isset($host) || !isset($db) || !isset($user) || !isset($pass) || !isset($port)) {
            return false;
        }else{
            $this->host = $host;
            $this->db = $db;
            $this->pass = $pass;
            $this->port = $port;
            // 链接数据库，如果失败返回false
            $dbobj = new \mysqli($host,$user,$pass,$db,$port);
            if($dbobj->connect_errno) {
                $this->error = $dbobj->connnet_error;
                return false;
            }else{
                $this->dbobj = $dbobj;
                return $this;
            }
        }
    }
    // 设置对象属性
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    //获取对象属性
    public function __get($name) {
        return isset($this->data[$name])?$this->data[$name]:null;
    }
    //检查对象是否存在
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }
    // 销毁对象的值
    public function __unset($name)
    {
        unset($this->data[$name]);
    }


    public function __call($method, $args)
    {
        if (in_array(strtolower($method),$this->method,true)) {
            // 连贯操作的实现
            $this->options[strtolower($method)] = $args[0];
            return $this;
        }elseif(in_array(strtolower($method),array('count','sum','min','max','avg'),true)){
            // 统计查询的实现
            $field = isset($args[0])?$args[0]:'*';
            return ;
        }elseif(strtolower(substr($method,0,5))== 'getby'){
            // 根据某个字段获取记录
            $field = parse_name(substr($method,5));
            $where[$field] = $args[0];
            return;
        }elseif(strtolower(substr($method,0,10) == 'getfileldby')) {
            // 根据字段获取记录某个值
            $name = parse_name(substr($method,10));
            $where[$name] = $args[0];
            return;
        }elseif(isset($this->_scope[$method])){
            // 单独调用的实现
            return ;
        }
    }
    protected function error() {
        return $this->error;
    }

    // 切换数据库
    public function db($db) {
        $select_db = mysqli_change_user($this->dbobj,$this->db);
        if($select_db) {
            $this->db = $db;
            $dbobj = new mysqli($this->host,$this->user,$this->pass,$db,$this->port);
            $this->dbobj = $dbobj;
            return $this;
        }else{
            $this->error = mysqli_error($this->dbobj);
            return false;
        }
    }

    //切换用户
    public function change_user($user,$pass) {
            $change_user = mysqli_change_user($this->dbobj,$user,$pass,$this->db);
            if($change_user) {
                $this->user = $user;
                $this->pass = $pass;
                $dbobj = new mysqli($this->host,$this->user,$this->pass,$this->db,$this->port);
                return $this;
            }else{
                $this->error = mysqli_connect_error($this->dbobj);
                return false;
            }
    }
    // 展示所有表名
    public function tables() {
        $sql = 'show tables';
        $search = mysqli_query($this->dbobj,$sql);
        if($search) {
            $num_row = $search->num_rows;
            $tables_msg = [
                'count' => $num_row,
                'tables' => array()
            ];
            for ($i = 0 ;$i<$num_row;$i++) {
                $row = $search->fetch_assoc();
                $key = 'Tables_in_'.$this->db;
                array_push($tables_msg['tables'],$row[$key]);
            }
            mysqli_free_result($search);
            return $tables_msg;
        }else {
            mysqli_free_result($search);
            return false;
        }
    }
    // 获取指定表中的数据
    public function select_table($table) {
        $sql = 'select * from '.$table;
        $search_res = mysqli_query($this->dbobj,$sql);
        if($search_res) {
            $this->table = $table;
            $tableobj = self::query_handle($search_res);
            $this->tableobj = $tableobj;
            mysqli_free_result($search_res);
            return $tableobj;
        }else{
            mysqli_free_result($search_res);
            return false;
        }
    }
    // 获取制定表中的字段详情
    public function select_table_field($table) {
        $sql = 'show field from '.$table;
        $search_res = mysqli_query($this->dbobj,$sql);
        if($search_res) {
            $this->table = $table;
            $filed_msg = self::query_handle($search_res);
            mysqli_free_result($search_res);
            return $filed_msg;
        }else {
            mysqli_free_result($search_res);
            return false;
        }
    }

    // 获取数据表中指定字段的数据
    public function getField($field) {
        $fields = self::param_handle($field);
        $count = count($fields);
        for($i=0;$i<$count;$i++) {
            $index = $fields[$i];
            $sql = "select ".$index.' form '.$this->table;
            $res = mysqli_query($this->dbobj,$sql);
            $field_msg[$index] = self::query_handle($res);
        }
        return $field_msg;
    }
    // 处理获取到的数据并返回
    protected function query_handle($obj) {
        $res = array();
        for($i=0;$i<$obj->num_rows;$i++) {
            $row = $obj->fetch_assoc();
            array_push($res,$row);
        }
        return $res;
    }
    // 处理参数传入函数

    protected function param_handle($param) {
        if(is_string($param) && !emtpy($param)) {
            $params = explode(',',$param);
        }elseif(is_array($param) && !emtpy($param)) {
            $params = $param;
        }else{
            return false;
        }
        return $params;
    }
    // 查询表达式参数处理
    protected function options_handle($param) {
        if(is_numeric($param)) {
            $option = $param;
        }elseif(is_string($param) && !empty($param) && !is_numeric($param)) {
            $params = explode(',',$param);
            $count = count($params);
            $option = implode(' and ',$params);
        }elseif(is_array($param) && !empty($params)) {
            $params = $param;
            $count = count($params);
            $arr = array();
            foreach($params as $key => $val) {
                $tip = "$key = $val";
                array_push($arr,$tip);
            }
            $option = implode(' and ',$arr);
        }else{
            return false;
        }
        return $option;
    }
    // 处理表达式方法
    public function option() {
        $options = $this->options;
        $option = '';
        if(isset($options['where'])) {
            $option .= 'where '.$options['where'].' ';
        }
        if(isset($options['order'])) {
            $option .= 'order by '.$options['order'].' '.$options['order_type'].' ';
        }
        if(isset($options['limit'])) {
            $option .= 'limit '.$options['limit'];
        }
        return $option;
    }
    // 查找单条记录
    public function find() {
        $option = self::option();
        $sql = 'select * from '.$this->table.' '.$option;
        $search_res = mysqli_query($this->dbobj,$sql);
        $msg = self::query_handle($search_res);
        return $msg;
    }

    // where 处理where 条件
    public function where($where) {
        $this->options['where'] = self::options_handle($where);
        return $this;
    }
    // limit 处理limit 条件
    public function limit($limit) {
        $this->options['limit'] = $limit;
        return $this;
    }
    // order 处理order 条件
    public function order($order,$type ='asc'){
        $this->options['order'] = $order;
        $this->options['order_type'] = $type;
        return $this;
    }
    // 数据处理函数

    public function data(array $data) {
        $values = array();
        $fields = array();
        if(is_array($data)) {
            foreach ($data as $key => $val) {
                if(is_array($val)){
                    $tip = 1;
                    array_push($values,'('.implode(',',array_values($val)).')');
                    array_push($fields,'('.implode(',',array_keys($val)).')');
                }else{
                    $tip = 0;
                }
            }
        }else{
            return false;
        }
        if(!$tip) {
            array_push($values,'('.implode(array_values($data)).')');
            array_push($fields,'('.implode(array_keys($data)).')');
        }
        $this->data['fields'] = $fields[0];
        $this->data['values'] = implode(',',$values);
        return $this;
    }
    // 添加
    public function add() {
        $fields = $this->data['fields'];
        $values = $this->data['values'];
        $sql = "INSERT INTO  ".$this->table.$fields.' VALUES '.$values;
        $res = mysqli_query($this->dbobj,$sql);
        return $res;
    }

    // 编辑
    public function save(array $data) {
        $tip = array();
        if(is_array($data)) {
            foreach ($data as $key => $val) {
                array_push($tip,"$key = $val");
            }
        }else{
            return false;
        }
        $set_msg = implode(',',$tip);
        $sql = "UPDATE ".$this->table." SET ".$set_msg. " WHERE " .$this->options['where'];
        $res = mysqli_query($this->dbobj,$sql);
        return $res;
    }

    // 删除
    public function delete () {
        $sql = 'DELETE FROM '.$this->table. " WHERE ".$this->options['where'];
        $res = mysqli_query($this->dbobj,$sql);
        return $res;
    }

    // sql 原生语句查询
    public function query($sql) {
        $res = mysqli_query($this->dbobj,$sql);
        return $res;
    }
    // 关闭链接
    public function close() {
        $close = mysqli_close($this->dbobj);
        if($close) {
            return true;
        }else{
            return false;
        }
    }
    // 析构函数  执行完关闭数据库
    function __destruct()
    {
        mysqli_close($this->dbobj);
    }

}

?>