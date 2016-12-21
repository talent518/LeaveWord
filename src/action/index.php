<?php
if(!defined('IN_LW')) {
	exit('Access Denied');
}

$list=$pids=$rlist=array();

$count=M('talk')->count('ptid=0');

$perpage=10;
$pages=floor(($count+$perpage-1)/$perpage);
$page=min(max(1,intval($_G['operate'])),$pages);

if($count){
	//获取主题留言列表
	$res=db::query('SELECT * FROM %t as t LEFT JOIN %t as u ON (t.uid=u.uid) LEFT JOIN %t as p ON (u.uid=p.uid) WHERE t.ptid=0 ORDER BY t.tid DESC LIMIT %d,%d',array('talk','user','user_profile',($page-1)*$perpage,$perpage));
	while($r=db::fetch($res)){
		$list[$r['tid']]=$r;
	}
	db::free_result($res);

	if($list){//获取回复留言列表
		$res=db::query('SELECT * FROM %t as t LEFT JOIN %t as u ON (t.uid=u.uid) LEFT JOIN %t as p ON (u.uid=p.uid) WHERE t.ptid IN (%n) ORDER BY t.tid DESC',array('talk','user','user_profile',array_keys($list)));
		while($r=db::fetch($res)){
			$rlist[$r['pid']][$r['tid']]=$r;
		}
		db::free_result($res);
	}
	$multi=helper_page::multi($count,$perpage,$page,U('index','{page}'));//生成分页HTML
}

include template('index');
