<?php
if(!defined('IN_LW')) {
	exit('Access Denied');
}

class model_user_profile extends model_base{
	protected $table='user_profile';
	protected $priKey='uid';
	protected $forKey='';
	protected $rules=array(
		'nickname'=>array(
			'required'=>true,
			'chinese'=>true,
		),
		'sex'=>array(
			'required'=>true,
			'ufloat'=>true,
		),
		'birthday'=>array(
			'required'=>true,
			'date'=>true,
		),
		'qq'=>array(
			'required'=>true,
			'ufloat'=>true,
			'min'=>10000,
		),
	);
	protected $messages=array(
		'nickname'=>array(
			'required'=>'昵称不能为空',
			'chinese'=>'昵称只能包括中文和英文、数字和非特殊符号'
		),
		'sex'=>array(
			'required'=>'请选择性别',
			'ufloat'=>'选择性别错误',
		),
		'birthday'=>array(
			'required'=>'请输入生日',
			'date'=>'生日格式不正确，如：1987-2-26',
		),
		'qq'=>array(
			'required'=>'请输入QQ',
			'ufloat'=>"QQ只能为数字",
			'min'=>"QQ必须大于{0}的整数",
		),
	);
	function add($data,$isCheck=true, $return_insert_id = false, $replace = false, $silent = false){
		if(!$isCheck || $this->check($data,true)){
			$data['birthday']=@strtotime($data['birthday']);
			return parent::add($data,false,$return_insert_id,$replace,$silent);
		}
		return false;
	}
	function edit($id,$data,$isCheck=true, $unbuffered = false, $low_priority = false){
		if(!$isCheck || $this->check($data,false)){
			$data['birthday']=@strtotime($data['birthday']);
			return parent::edit($id,$data,false,$unbuffered,$low_priority);
		}
		return false;
	}
}
