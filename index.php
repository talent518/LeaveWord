<?php
define('IN_LW',true);

require './src/loader.php';

if($_G['m']=='user' && $_G['a']){//UCenter头像上传
	$_G['action']='user';
	$_G['operate']='avatar';
}

//Action默认为index
if(!$_G['action'] || $_G['action']=='index.php'){
	$_G['action']='index';
}

$incfile=SRC_DIR.'action'.DIR_SEP.$_G['action'].'.php';
file_exists($incfile) or hmessage('不合法的URL地址',U('index'));

$action=$_G['action'];
$operate=$_G['operate'];

$_url=$_G['action'].'/'.(is_numeric($_G['operate'])?'?':$_G['operate']);

if($_UID){//登录用户
	if($_USER['is_profiled']!=2){//未完善资料
		switch($_USER['is_profiled']){
			case 0://上传头像
				$_url=='user/avatar' or gU('user','avatar');
				break;
			case 1://填写资料
				$_url=='user/profile' or gU('user','profile');
				break;
			default://其它
				break;
		}
	}
	if($_url=='user/login' || $_url=='user/register'){//已经登录不允许再访问
		gU('index');
	}
}elseif(!in_array($_url,array('user/login','user/register','index/','index/?'))){//检查访问权限
	if(IN_POST || $_G['dataType']=='json'){
		jmessage('请登录后再继续！',U('user','login'));
	}else{
		hmessage('请登录后再继续！',U('user','login'),5);
	}
}
require $incfile;