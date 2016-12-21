<?php
if(!defined('IN_LW')) {
	exit('Access Denied');
}

class model_user extends model_base{
	protected $table='user';
	protected $priKey='uid';
	protected $forKey='';
	protected $rules=array(
		'username'=>array(
			'required'=>true,
			'rangelength'=>'3,25',
			'username'=>true,
			'query'=>'user'
		),
		'password'=>array(
			'required'=>true,
			'minlength'=>6,
			'password'=>true,
		),
		'email'=>array(
			'required'=>true,
			'email'=>true,
			'query'=>'user'
		),
	);
	protected $erules=array(
		'username'=>array(
			'required'=>false,
		),
		'password'=>array(
			'required'=>false,
		),
		'email'=>array(
			'required'=>false,
		),
	);
	protected $messages=array(
		'username'=>array(
			'required'=>'用户名不能为空',
			'rangelength'=>'用户名的长度只能在{0}和{1}之间',
			'query'=>'用户名“{0}”已经存在'
		),
		'password'=>array(
			'required'=>'密码不能为空',
			'minlength'=>'密码长度不能少于{0}字',
		),
		'email'=>array(
			'required'=>'电子邮件不能为空',
			'query'=>'电子邮件“{0}”已经存在'
		),
	);
	function register($username,$password,$email){
		$data=array('username'=>$username,'password'=>$password,'email'=>$email);
		if($this->check($data,true)){
			$data['password']=md5($password);
			$data['regtime']=TIMESTAMP;
			$data['regip']=ONLINE_IP;
			$data['logtime']=TIMESTAMP;
			$data['logip']=ONLINE_IP;
			return parent::add($data,false,true);
		}
		return false;
	}
	function edit($id,$data,$isCheck=true, $unbuffered = false, $low_priority = false){
		if($isCheck || $this->check($data,false)){
			unset($data['username']);
			if($data['password']){
				$data['password']=md5($data['password']);
			}else{
				unset($data['password']);
			}
			return parent::edit($id,$data,false,$unbuffered,$low_priority);
		}
		return false;
	}
}
