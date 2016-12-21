<?php
if(!defined('IN_LW')) {
	exit('Access Denied');
}
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);

ini_set('date.timezone', 'Asia/Shanghai');//设置date函数的时区

//定义URL常量
define('M_URL',strtolower(substr($_SERVER['SERVER_PROTOCOL'],0,strpos($_SERVER['SERVER_PROTOCOL'],'/'))).'://'.$_SERVER['HTTP_HOST'].(in_array($_SERVER['SERVER_PORT'],array(80,443))?null:':'.$_SERVER['SERVER_PORT']));//站点主域名URL
defined('R_URL') or define('R_URL',substr($_SERVER['SCRIPT_NAME'],0,-9));//站点根路径URL
define('F_URL',M_URL.R_URL);//站点完整URL

//目录常量
define('DIR_SEP',DIRECTORY_SEPARATOR);//目录分隔符
define('SRC_DIR',dirname(__FILE__).DIRECTORY_SEPARATOR);
define('LW_ROOT',substr(SRC_DIR,0,-4));
define('TPL_DIR','tpl');
define('SKIN_URL',R_URL.'skin');

//时间戳常量
define('TIMESTAMP',time());

//行为常量
define('IN_AJAX',$_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest');
define('IN_POST',$_SERVER["REQUEST_METHOD"]=='POST');

if($_GET['rewrite']){//应用其它重写参数
	foreach(explode('/', $_GET['rewrite']) as $arg){
		if(($pos=strpos($arg, '-'))!==false){
			$_GET[substr($arg, 0,$pos)]=urldecode(substr($arg, $pos+1));
		}
	}
	$_GET['rewrite']=NULL;unset($_GET['rewrite']);
}

//HTTP请求参数简写
$_G=& $_GET;
$_P=& $_POST;
//$_C=& $_COOKIE;
//$_R=& $_REQUEST;

include_once LW_ROOT.'data'.DIR_SEP.'config.php';//配置文件
include_once SRC_DIR.'mod'.DIR_SEP.'base.php';//模块基类

//根据配置连接到数据库
db::init(DB_HOST,DB_USER,DB_PW,DB_NAME,DB_CHARSET,DB_PCONNECT,DB_TABLEPRE);

//登录用户相关变量(全局)
$_UID=0;
$_UNAME='';
$_EMAIL='';
$_USER=array();

//获取登录状态
if($_G['agent']){//GET参数
	$args=explode('|',authcode($_G['agent'],'DECODE',COOKIE_KEY));
}elseif(SESSION_MODE){//Session参数
	session_start();
	if($_SESSION['UID'] && $_SESSION['PWD']){
		$args=array($_SESSION['UID'],$_SESSION['PWD']);
	}
}else{//Cookie参数
	$args=explode('|',authcode(getcookie('auth'),'DECODE',COOKIE_KEY));
}
//$args参数获取登录信息
if(count($args)==2 && ($_USER=m('user')->get(db::format('`uid`=%d AND `password`=%s',$args)))){
	$_UID=$_USER['uid'];
	$_UNAME=$_USER['username'];
	$_EMAIL=$_USER['email'];
}else{
	if($_G['agent']){
		exit($_G['agent'].'请先登录！');
	}elseif(SESSION_MODE){
		$_SESSION['UID']=$_SESSION['PWD']=null;
		unset($_SESSION['UID'],$_SESSION['PWD']);
	}else{
		dsetcookie('auth');
	}
}

//获取客户端IP
if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
	$onlineip = getenv('HTTP_CLIENT_IP');
} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
	$onlineip = getenv('HTTP_X_FORWARDED_FOR');
} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
	$onlineip = getenv('REMOTE_ADDR');
} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
	$onlineip = $_SERVER['REMOTE_ADDR'];
}else{
	$onlineip = '';
}
define('ONLINE_IP',$onlineip);

define('IN_MANAGER',$_UID==1);//是否为管理员

//HTTP MIME头信息
header('Content-Type: text/html; charset=utf-8');

function __autoload($class){//自动加载类库lib
	include_once SRC_DIR.'lib'.DIR_SEP.$class.'.php';
	class_exists($class,false) or die('Library class "'.$class.'" not exists!');
}

function M($mod){//模块调用主函数
	static $mods;
	if($mod=='base'){
		return new stdClass();
	}
	if(!is_array($mods)){
		$mods=array();
	}
	if(!is_object($mods[$mod])){
		require_once SRC_DIR.'mod'.DIR_SEP.$mod.'.php';
		$class='model_'.$mod;
		class_exists($class,false) or die('Model class "'.$class.'" not exists!');
		$mods[$mod]=new $class($class);
	}
	return $mods[$mod];
}

//获取URL地址有4种格式(key1,key2,value1,value2都是非选定参数)，1-3为URL重写传递格式，4为正常URL传递格式
//1. /action.html
//2. /action/operate.html
//3. /action/operate/key1-value1/key2-value2.html
//4. index.php?action=user&operate=register&key1=value1&key2=value2
function U($action,$operate='',$args=''){
	if(URL_REWRITE){
		$url=R_URL.$action;
		if($operate){
			$url.='/'.$operate;
		}
		if($args){
			$url.='/';
			if(is_array($args)){
				foreach($args as $k=>$v){
					$url.=sprintf('/%s-%s',$k,urlencode($v));
				}
			}else{
				$url.=str_replace(array('&amp;','&','='), array('/','/','-'), $args);
			}
		}
		$url.=EXT_REWRITE;
	}else{
		$url='index.php?action='.$action;
		if($operate){
			$url.='&operate='.$operate;
		}
		if($args) {
			$url.='&' . $args;
		}
	}
	return $url;
}
//GET参数跳转
function gU($action,$operate='',$args=''){
	goto_url(U($action,$operate,$args));
}
//URL跳转
function goto_url($url){
	global $_G;
	if(IN_POST){
		jmessage('你无权操作，之后为你自动跳转！',$url);
	}
	header('Cache-Control: no-cache, no-store, must-revalidate');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Pragma: no-cache');
	header('Location: '.$url);
	exit;
}

//把指定的POST参数存入数组并返回
function gP(){
	$ret=array();
	foreach(func_get_args() as $arg){
		$ret[$arg]=$_POST[$arg];
	}
	return $ret;
}

//设置COOKIE
function dsetcookie($key, $value = '', $life = 0, $prefix = 1, $httponly = false) {
	$key = ($prefix ? COOKIE_PRE : '').$key;
	$_COOKIE[$key] = $value;

	if($value == '' || $life < 0) {
		$value = '';
		$life = -1;
	}

	$life = $life > 0 ? TIMESTAMP + $life : ($life < 0 ? TIMESTAMP - 31536000 : 0);
	$path = $httponly && PHP_VERSION < '5.2.0' ? COOKIE_PATH.'; HttpOnly' : COOKIE_PATH;

	$secure = $_SERVER['SERVER_PORT'] == 443 ? 1 : 0;
	if(PHP_VERSION < '5.2.0') {
		setcookie($key, $value, $life, $path, COOKIE_DOMAIN, $secure);
	} else {
		setcookie($key, $value, $life, $path, COOKIE_DOMAIN, $secure, $httponly);
	}
}
//获取Cookie
function getcookie($key,$prefix = 1) {
	if($prefix){
		$key=COOKIE_PRE.$key;
	}
	return isset($_COOKIE[$key]) ? $_COOKIE[$key] : '';
}

//检查模板是否被更新，已经更新模板自动生成php文件并写入缓存文件
function checktplrefresh($maintpl, $subtpl, $timecompare, $cachefile, $tpldir) {
	static $tempalte;
	if(!$tempalte){
		$template = new template();
	}

	if(empty($timecompare) || DEV_MODE) {
		if(empty($timecompare) || @filemtime(LW_ROOT.$subtpl) > $timecompare) {
			$template->parse_template($maintpl, $tpldir, $cachefile);
			return TRUE;
		}
	}
	return FALSE;
}
//获取模板缓存文件路径，自行再用include或require进行执行文件，如：include template('index');
function template($file, $tpldir = '',$is_return_tplfile=0) {
	$tpldir = $tpldir ? $tpldir : TPL_DIR;

	$tplfile = $tpldir.'/'.$file.'.htm';
	$cachefile = 'data/tpl/'.str_replace('/', '_', $file).'.tpl.php';

	if(DIR_SEP=='\\'){
		$tplfile=str_replace('/', '\\', $tplfile);
		$cachefile=str_replace('/', '\\', $cachefile);
	}
	if($is_return_tplfile){
		return $tplfile;
	}
	checktplrefresh($tplfile, $tplfile, @filemtime(LW_ROOT.$cachefile), $cachefile, $tpldir);
	return LW_ROOT.$cachefile;
}

//对字符串进行加解密
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {

	$ckey_length = 4;	// 随机密钥长度 取值 0-32;
				// 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
				// 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
				// 当此值为 0 时，则不产生随机密钥

	$key = md5($key ? $key : UC_KEY);
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);

	$result = '';
	$box = range(0, 255);

	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}

}

//转换为int类型并且会自行对数组进行遍历
function dintval($int, $allowarray = false) {
	$ret = intval($int);
	if($int == $ret || !$allowarray && is_array($int)) return $ret;
	if($allowarray && is_array($int)) {
		foreach($int as &$v) {
			$v = dintval($v, true);
		}
		return $int;
	} elseif($int <= 0xffffffff) {
		$l = strlen($int);
		$m = substr($int, 0, 1) == '-' ? 1 : 0;
		if(($l - $m) === strspn($int,'0987654321', $m)) {
			return $int;
		}
	}
	return $ret;
}
//添加转义字符并且会自行对数组进行遍历
function daddslashes($string, $force = 1) {
	if(is_array($string)) {
		$keys = array_keys($string);
		foreach($keys as $key) {
			$val = $string[$key];
			unset($string[$key]);
			$string[addslashes($key)] = daddslashes($val, $force);
		}
	} else {
		$string = addslashes($string);
	}
	return $string;
}

$__regs=array(
	'utf-8'	=>"/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/",
	'gb2312'=>"/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/",
	'gbk'	=>"/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/",
	'big5'	=>"/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|[\xa1-\xfe])/"
);
//获取字符串长度，中文认为1个字符
function dstrlen($str,$charset="utf-8"){
	if(function_exists("mb_strlen"))
		return mb_strlen($str,$charset);
	elseif(function_exists('iconv_strlen')) {
		return iconv_strlen($str,$charset);
	}else{
		global $__regs;
		preg_match_all($__regs[$charset],$str,$match);
		return count($match[0]);
	}
}
//截断字符串
function strcut($str,$start=0,$length,$charset="utf-8",$suffix='…'){
	if($start==0 && $this->len($str)<=$length)
		return $str;
	if(function_exists("mb_substr"))
		return mb_substr($str, $start, $length, $charset).($start==0?$suffix:'');
	elseif(function_exists('iconv_substr')) {
		return iconv_substr($str,$start,$length,$charset).($start==0?$suffix:'');
	}else{
		global $__regs;
		preg_match_all($__regs[$charset],$str,$match);
		return implode("",array_slice($match[0],$start,$length)).($start==0?$suffix:'');
	}
}
//随机返回$length长度的字符串
function random($length, $numeric = 0) {
	$seed = base_convert(md5(microtime().$_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
	$seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
	if($numeric) {
		$hash = '';
	} else {
		$hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
		$length--;
	}
	$max = strlen($seed) - 1;
	for($i = 0; $i < $length; $i++) {
		$hash .= $seed{mt_rand(0, $max)};
	}
	return $hash;
}
//检查string是否存在find
function strexists($string, $find) {
	return !(strpos($string, $find) === FALSE);
}
//合并数组
function array_merge_r(array $arr1,array $arr2) {
	$ret=$arr1;
	foreach($arr2 as $k=>$v){
		if(is_array($v)){
			$ret[$k]=array_merge($ret[$k],$v);
		}else{
			$ret[$k]=$v;
		}
	}
	return $ret;
}


//检查是否为提交
function is_submit($key){
	global $_P;
	return IN_POST && $_P[$key.'submit'];
}

//html格式消息提示
function hmessage($message,$url,$timeout=3){
	global $__message,$__url,$__timeout;
	$__message=$message;
	$__url=$url;
	$__timeout=($timeout>1?$timeout:1);
	include template('message');
	exit;
}

//修正无json扩展
if(!function_exists('json_encode')){
	function json_encode($json){
		global $__jsonObject;
		if(!$__jsonObject){
			$__jsonObject=new json;
		}
		return $__jsonObject->encode($json);
	}
}
if(!function_exists('json_decode')){
	function json_decode($obj,$assoc=false){
		global $__jsonObject;
		if(!$__jsonObject){
			$__jsonObject=new json;
		}
		return $__jsonObject->decode($json,$assoc);
	}
}

//JSON格式消息提示
function jmessage($message,$url='',$redirect=true){
	$json=array('message'=>$message);
	if($url){
		$json['url']=$url;
	}
	if($redirect){
		$json['redirect']=$redirect;
	}
	echo_json($json);
}

//输出JSON数据
function echo_json($json){
	header('Content-Type: application/json; charset=utf-8');
	header('Cache-Control: no-cache, no-store, must-revalidate');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Pragma: no-cache');
	exit(json_encode($json));
}

//获取头像
function avatar($uid,$size='middle',$is_file=false){
	$size = in_array($size, array('real','big', 'middle', 'small')) ? $size : 'middle';
	$uid = abs(intval($uid));
	$uid = sprintf("%09d", $uid);
	$dir1 = substr($uid, 0, 3);
	$dir2 = substr($uid, 3, 2);
	$dir3 = substr($uid, 5, 2);

	$dirs=array('data','avatar',$dir1,$dir2,$dir3,substr($uid, -2).'_'.$size.'.jpg');

	$file=LW_ROOT.implode(DIR_SEP,$dirs);

	return $is_file?$file:(file_exists($file)?R_URL.implode('/',$dirs):SKIN_URL.'/images/avatar_'.$size.'.gif');
}

//创建多级目录
function dmkdir($dir,$mode=0777,$recursive=false){
	if(is_null($dir) || $dir===""){
		return FALSE;
	}
	if(is_dir($dir) || $dir==="/"){
		return TRUE;
	}
	if(dmkdir(dirname($dir), $mode, $recursive)){
		$_umask=umask(0);
		$ret=@mkdir($dir,$mode);
		umask($_umask);
		return $ret;
	}
	return FALSE;
}
