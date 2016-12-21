<?php
if(!defined('IN_LW')) {
	exit('Access Denied');
}

if($_G['operate']=='manager'){//管理用户
	$navtitle='用户管理';
	if(IN_MANAGER){//管理员
		$uid=$_G['uid']+0;
		$user=array();
		if($uid>0){
			$user=M('user')->get($uid);
			if($user){
			}elseif(IN_POST){
				jmessage('用户不存在！',U($action,$operate));
			}else{
				hmessage('用户不存在！',U($action,$operate));
			}
		}
		if(is_submit('manager')){
			if($uid>0){
				$data=gP('password','email');
				if(M('user')->edit($uid,$data)){
					jmessage('编辑用户成功！',U($action,$operate));
				}else{
					jmessage(M('user')->error?M('user')->error:'编辑用户失败！');
				}
			}else{
				if(M('user')->register($_P['username'],$_P['password'],$_P['email'])){
					jmessage('添加用户成功！',U($action,$operate));
				}else{
					jmessage(M('user')->error?M('user')->error:'添加用户失败！');
				}
			}
		}elseif(is_submit('drop')){
			if($uid==1){
				jmessage('用户“'.$user['name'].'”受系统保护，不能删除！');
			}elseif(M('user')->drop($uid)){
				jmessage('删除用户成功！',U($action,$operate));
			}else{
				jmessage('删除用户失败！');
			}
		}else{
			if(!isset($_G['uid'])){
				$count=M('user')->count('1>0');
				$perpage=10;
				$pages=floor(($count+$perpage-1)/$perpage);
				$page=min(max(1,intval($_G['page'])),$pages);
				$multi=helper_page::multi($count,$perpage,$page,U($action,$operate,'page={page}'));//生成分页HTML
				$list=M('user')->get_list(db::format('1>0 LIMIT %d,%d', array(($page-1)*$perpage,$perpage)));
			}
			include template('user/manager');
		}
	}else{//无权限
		hmessage('你没有权限管理用户！');
	}
	exit;
}elseif($_G['operate']=='logout'){//用户退出
	if(SESSION_MODE){
		$_SESSION['UID']=$_SESSION['PWD']=null;
		unset($_SESSION['UID'],$_SESSION['PWD']);
	}else{
		dsetcookie('auth');
	}
	$url=strpos($_SERVER['HTTP_REFERER'],F_URL)!==false?$_SERVER['HTTP_REFERER']:U('index');
	hmessage('请登录后再继续！',$url,5);
}elseif(is_submit('login')){//提交用户登录
	if($_USER=M('user')->get(db::format('`username`=%s AND `password`=%s',array($_P['username'],md5($_P['password']))))){
		$_UID=$_USER['uid'];
		$_UNAME=$_USER['username'];
		$_EMAIL=$_USER['email'];
		if(SESSION_MODE){
			$_SESSION['UID']=$_UID;
			$_SESSION['PWD']=$_USER['password'];
		}else{
			dsetcookie('auth',authcode($_UID.'|'.$_USER['password'],'ENCODE',COOKIE_KEY),$_P['cookietime']+0);
		}
		M('user')->edit($_UID,array('logip'=>ONLINE_IP,'logtime'=>TIMESTAMP,'prevlogtime'=>$_USER['logtime'],'prevlogip'=>$_USER['logip']),false);
		jmessage('登录成功',U('index'));
	}
	jmessage('用户名或密码不正确！');
}elseif(is_submit('register')){//提交用户注册
	if($_P['password']!=$_P['rpassword']){
		jmessage('两输入的密码不同！');
	}elseif($uid=M('user')->register($_P['username'],$_P['password'],$_P['email'])){
		if(SESSION_MODE){
			$_SESSION['UID']=$uid;
			$_SESSION['PWD']=md5($_P['password']);
		}else{
			dsetcookie('auth',authcode($uid.'|'.md5($_P['password']),'ENCODE',COOKIE_KEY));
		}
		jmessage('注册成功',U($action,'profile'));
	}else{
		jmessage(M('user')->error);
	}
}elseif($_G['operate']=='avatar' && in_array($_G['a'],array('uploadavatar','rectavatar'))){//提交上传头像
	$callback='on'.$_GET['a'];
	if(function_exists($callback))
		echo $callback();
	exit;
}elseif(is_submit('profile')){//提交用户资料
	$data=gP('nickname','sex','birthday','qq');
	$data['uid']=$_UID;
	if($_USER['is_profiled']==1){
		if(M('user_profile')->add($data)){
			M('user')->edit($_UID,array('is_profiled'=>2),false);
			jmessage('保存成功',U('index'));
		}else{
			jmessage(M('user_profile')->error?M('user_profile')->error:'保存失败');
		}
	}else{
		if(M('user_profile')->edit($_UID,$data)){
			jmessage('保存成功');
		}else{
			jmessage(M('user_profile')->error?M('user_profile')->error:'保存失败');
		}
	}
}elseif(is_submit('account')){//提交帐户
	if(md5($_P['spassword'])!=$_USER['password']){
		jmessage('请输入原密码！');
	}elseif($_P['password']!=$_P['rpassword']){
		jmessage('两次输入的新密码不同！');
	}else{
		$data=gP('password','email');
		if(M('user')->edit($_UID,$data)){
			jmessage('保存成功！',U($action,$data['password']?'login':'account'));
		}else{
			jmessage(M('user')->error?M('user')->error:'保存失败！');
		}
	}
}else{//表单与页面显示
	if($_UID){
		$operates=array('account'=>'帐户设置','avatar'=>'上传头像','profile'=>'资料设置');
		$operate=($operates[$_G['operate']]?$_G['operate']:gU($action,'account'));
	}else{
		$operates=array('login'=>'用户登录','register'=>'用户注册');
		$operate=($operates[$_G['operate']]?$_G['operate']:gU($action,'login'));
	}
	$navtitle=$operates[$operate];
	include template($action.'/'.$operate);
}

function flashdata_decode($s) {
	$r = '';
	$l = strlen($s);
	for($i=0; $i<$l; $i=$i+2) {
		$k1 = ord($s[$i]) - 48;
		$k1 -= $k1 > 9 ? 7 : 0;
		$k2 = ord($s[$i+1]) - 48;
		$k2 -= $k2 > 9 ? 7 : 0;
		$r .= chr($k1 << 4 | $k2);
	}
	return $r;
}

function onuploadavatar() {//原文件提交头像
	global $_UID;
	@header("Expires: 0");
	@header("Cache-Control: private, post-check=0, pre-check=0, max-age=0", FALSE);
	@header("Pragma: no-cache");
	//header("Content-type: application/xml; charset=utf-8");

	if(empty($_FILES['Filedata'])) {
		return -3;
	}

	list($width, $height, $type, $attr) = getimagesize($_FILES['Filedata']['tmp_name']);
	$imgtype = array(1 => '.gif', 2 => '.jpg', 3 => '.png');
	$filetype = $imgtype[$type];
	$tmpavatar = avatar($_UID,'real',true);
	$tmpdir=dirname($tmpavatar);
	is_dir($tmpdir) or dmkdir($tmpdir);
	file_exists($tmpavatar) && @unlink($tmpavatar);
	if(@copy($_FILES['Filedata']['tmp_name'], $tmpavatar) || @move_uploaded_file($_FILES['Filedata']['tmp_name'], $tmpavatar)) {
		@unlink($_FILES['Filedata']['tmp_name']);
		list($width, $height, $type, $attr) = getimagesize($tmpavatar);
		if($width < 10 || $height < 10 || $type == 4) {
			@unlink($tmpavatar);
			return -2;
		}
	} else {
		@unlink($_FILES['Filedata']['tmp_name']);
		return -4;
	}
	return M_URL.avatar($_UID,'real');
}

function onrectavatar() {//保存调整过的头像
	global $_UID,$_USER;
	@header("Expires: 0");
	@header("Cache-Control: private, post-check=0, pre-check=0, max-age=0", FALSE);
	@header("Pragma: no-cache");
	header("Content-type: application/xml; charset=utf-8");

	$bigavatarfile = avatar($_UID,'big',true);
	$middleavatarfile = avatar($_UID,'middle',true);
	$smallavatarfile = avatar($_UID,'small',true);

	$bigavatar = flashdata_decode($_POST['avatar1']);
	$middleavatar = flashdata_decode($_POST['avatar2']);
	$smallavatar = flashdata_decode($_POST['avatar3']);
	if(!$bigavatar || !$middleavatar || !$smallavatar) {
		return '<?xml version="1.0" ?><root><message type="error" value="-2" /></root>';
	}

	$success = 1;
	$fp = @fopen($bigavatarfile, 'wb');
	@fwrite($fp, $bigavatar);
	@fclose($fp);

	$fp = @fopen($middleavatarfile, 'wb');
	@fwrite($fp, $middleavatar);
	@fclose($fp);

	$fp = @fopen($smallavatarfile, 'wb');
	@fwrite($fp, $smallavatar);
	@fclose($fp);

	$biginfo = @getimagesize($bigavatarfile);
	$middleinfo = @getimagesize($middleavatarfile);
	$smallinfo = @getimagesize($smallavatarfile);
	if(!$biginfo || !$middleinfo || !$smallinfo || $biginfo[2] == 4 || $middleinfo[2] == 4 || $smallinfo[2] == 4) {
		file_exists($bigavatarfile) && unlink($bigavatarfile);
		file_exists($middleavatarfile) && unlink($middleavatarfile);
		file_exists($smallavatarfile) && unlink($smallavatarfile);
		$success = 0;
	}

	if($_USER['is_profiled']==0){
		M('user')->edit($_UID,array('is_profiled'=>1),false);
	}

	if($success) {
		return '<?xml version="1.0" ?><root><face success="1"/></root>';
	} else {
		return '<?xml version="1.0" ?><root><face success="0"/></root>';
	}
}
