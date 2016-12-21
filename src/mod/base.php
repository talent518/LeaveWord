<?php
if(!defined('IN_LW')) {
	exit('Access Denied');
}

class model_base{
	protected $table;//无前缀{DB_TABLEPRE}表名
	protected $priKey;//主键字段名
	protected $forKey;//外键字段名

	function __construct($classname){
		isset($this->table) or die('Class '.$classname.' not define property $table!');
		isset($this->priKey) or die('Class '.$classname.' not define property $priKey!');
		isset(self::$valid) or self::$valid=new validate;
		$this->erules=array_merge_r($this->rules,$this->erules);
	}

	private static $valid;
	var $key,$error;

	protected $rules=array(),$erules=array();
	protected $messages=array();

	//检查数据格式
	protected function check($data,$is_add=true){
		$this->key=self::$valid->key='';
		$this->error=self::$valid->error='';
		if(($is_add && empty($this->rules)) || (!$is_add && empty($this->erules)) || self::$valid->check($data,($is_add?$this->rules:$this->erules),$this->messages)){
			return true;
		}else{
			$this->key=self::$valid->key;
			$this->error=self::$valid->error;
			return false;
		}
	}

	//获取总计
	function count($condition){
		return db::result_first('SELECT COUNT(*) FROM %t WHERE %i',array($this->table,$condition));
	}
	
	/**
	 * 紧获取一行记录
	 * @param int/string $condition priKey(int)/condition(string)
	 * @param string $field 指定字段名列表，用户逗号分隔(SQL标准)
	 * @return array
	 */
	function get($condition,$field='*'){
		if(is_numeric($condition)){
			return db::fetch_first('SELECT %i FROM %t WHERE `%i` = %d', array($field,$this->table,$this->priKey,$condition));
		}else{
			return db::fetch_first('SELECT %i FROM %t WHERE %i', array($field,$this->table,$condition));
		}
	}

	//根据条件获取列表
	function get_list($condition,$field='*', $keyfield = ''){
		$isValue=!($field=='*' && strexists($field,','));
		return db::fetch_all('SELECT %i FROM %t WHERE %i', array($field,$this->table,$condition),$isValue?$field:$keyfield,$isValue);
	}
	//添加一行数据
	function add($data,$isCheck=true, $return_insert_id = false, $replace = false, $silent = false){
		if(!$isCheck || $this->check($data,true)){
			return db::insert($this->table, $data, $return_insert_id, $replace, $silent);
		}
		return false;
	}
	//更新一行数据
	function edit($id,$data,$isCheck=true, $unbuffered = false, $low_priority = false){
		if(!$isCheck || $this->check($data,false)){
			return db::update($this->table, $data, sprintf('%s = %d',$this->priKey,$id), $unbuffered,$low_priority);
		}
		return false;
	}
	//删除一行数据
	function drop($id, $limit = 0, $unbuffered = true){
		return db::delete($this->table, sprintf('%s = %d',$this->priKey,$id),$limit,$unbuffered);
	}
	//更新多行数据
	function update($data, $condition, $unbuffered = false, $low_priority = false){
		return db::update($this->table, $data, $condition, $unbuffered,$low_priority);
	}
	//删除多行数据
	function delete($condition, $limit = 0, $unbuffered = true){
		return db::delete($this->table, $condition, $limit,$unbuffered);
	}
}
