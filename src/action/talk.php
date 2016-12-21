<?php
if(!defined('IN_LW')) {
	exit('Access Denied');
}

$tid=$_G['tid']+0;

if($_G['operate']=='drop'){//删除操作
	if(!$tid){
		jmessage('参数错误！');
	}elseif(IN_MANAGER){//管理员
		if(M('talk')->drop($tid)){
			jmessage('删除成功', 'javascript: location.reload();');
		}else{
			jmessage('删除失败');
		}
	}elseif($talk=M('talk')->get(db::format('tid=%d AND uid=%d',array($tid,$_UID)))){//用户本人
		if(M('talk')->drop($tid)){
			jmessage('删除成功', 'javascript: location.reload();');
		}else{
			jmessage('删除失败');
		}
	}else{
		jmessage('你没有权限操作！');
	}
}elseif(is_submit('send')){//提交主题留言
	if(!dstrlen($_P['subject'])){
		jmessage('请填写主题！');
	}
	if(!dstrlen($_P['message'])){
		jmessage('请填写留言！');
	}
	if(IN_MANAGER){
		$data=gP('subject','message');
	}else{
		$data=array();
		$data['message']=htmlentities($_P['message'],ENT_QUOTES);
	}
	$data['uid']=$_UID;
	$data['dateline']=TIMESTAMP;
	if(M('talk')->add($data)){
		jmessage('留言成功！', 'javascript: location.reload();');
	}else{
		jmessage('留言失败！');
	}
}elseif(is_submit('revert')){//提交回复留言
	if(!dstrlen($_P['message'])){
		jmessage('请填写留言！');
	}
	if(!($talk=M('talk')->get($tid))){
		jmessage('参数错误！');
	}
	$data=array();
	$data['message']=(IN_MANAGER?$_P['message']:htmlentities($_P['message'],ENT_QUOTES));
	$data['pid']=$tid;
	$data['ptid']=($talk['ptid']?$talk['ptid']:$tid);
	$data['uid']=$_UID;
	$data['dateline']=TIMESTAMP;
	if(M('talk')->add($data)){
		jmessage('回复成功！', 'javascript: location.reload();');
	}else{
		jmessage('回复失败！');
	}
}else{//显示留言表单
	$operates=array('send','revert');
	$operate=(in_array($_G['operate'],$operates)?$_G['operate']:($tid?'revert':'send'));

	include template($action.'/'.$operate);
}
