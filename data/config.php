<?php
if(!defined('IN_LW')) {
	exit('Access Denied');
}

//数据库
define('DB_HOST', '127.0.0.1');//主机
define('DB_USER', 'root');//用户名
define('DB_PW', '');//密码
define('DB_NAME', 'test');//库名
define('DB_CHARSET', 'utf8');//字符集
define('DB_PCONNECT', 0);//是否以持久连接
define('DB_TABLEPRE', 'lw_');//表前缀

define('DEV_MODE',true);//是否启用开发模式

#define('R_URL','/');末尾要加"/"
define('URL_REWRITE',false);//URL重写
define('EXT_REWRITE','.html');//URL重写

define('SESSION_MODE',false);//是否启用SESSION模式

//COOKIE设置
define('COOKIE_PRE','lw_');//前缀
define('COOKIE_DOMAIN','');//作用域
define('COOKIE_PATH','/');//作用路径
define('COOKIE_KEY','LW551188');//安装密钥
