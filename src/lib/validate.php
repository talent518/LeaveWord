<?php
if(!defined('IN_LW'))
	exit('Access Denied');
/*
@desc 批量数据验证
$datas=array(
	'email'=>'ss-_32.s@ss.cn',
	'age'=>'-16',
	'ip'=>'120.0.0.1',
	'password'=>'dsfdfsdsddddddddd',
	'idcard'=>'342529198605293018',
	'url'=>'https://sdfsd.com?夺夺',
	'date'=>'1950-2-28',
	'currency'=>'-12.45131',
);
$rules=array(
	'email'=>array(
		'required'=>true,
		'email'=>true,
	),
	'age'=>array(
		'integer'=>true,
		'range'=>'-100,100',
	),
	'ip'=>array(
		'required'=>true,
		'ip'=>true
	),
	'password'=>array(
		'required'=>true,
		'equal'=>$datas['password'],
		'rangelength'=>'6,20',
	),
	'moblie'=>array(
		'required'=>true,
		'uinteger'=>true,
		'length'=>11,
	),
	'idcard'=>array(
		'required'=>true,
		'idcard'=>true
	),
	'url'=>array(
		'required'=>true,
		'url'=>true,
	),
	'date'=>array(
		'date'=>true,
	),
	'currency'=>array(
		'required'=>true,
		'float'=>true,
	)
);
$messages=array(
	'email'=>array(
		'required'=>'EMAIL不能为空',
		'email'=>'格式不正确',
	),
	'age'=>array(
		'required'=>'年龄不能为空',
		'integer'=>'必需为正整数'
	),
	'ip'=>array(
		'required'=>'IP地址不能为空',
		'ip'=>'IP地址不正确'
	),
	'password'=>array(
		'required'=>'密码不能为空',
		'equal'=>'俩次输入的密码不同',
	),
	'idcard'=>array(
		'idcard'=>'身证证号码不合法'
	)
);

$valid = new validate($datas,$rules,$messages);
echo '<pre>';
print_r($valid->key,$valid->error);
echo '</pre>';
*/

class validate{
	var $key,$error;
	private $messages=array(
		'required'=>'不能为空',
		'equal'=>'“{0}”不等于“{1}”',
		'email'=>'电子邮件格式不正确',
		'integer'=>'不是一个正整数',
		'float'=>'不合法的浮点数',
		'ufloat'=>'不合法的无符号浮点数',
		'min'=>'不能小于{0}的正整数',
		'max'=>'不能大于{0}的正整数',
		'range'=>'只能在{0}和{1}之间的正整数',
		'mobile'=>'手机号格式不正确',
		'phone'=>'固定电话格式不正确',
		'ip'=>'IP地址格式不正确',
		'minlength'=>'不能小于{0}个字符',
		'maxlength'=>'不能大于{0}个字符',
		'rangelength'=>'字符个数只能在{0}和{1}之间',
		'length'=>'只能是{0}个字符',
		'url'=>'URL地址格式不正确',
		'idcard'=>'身证证号码不合法',
		'date'=>'日期不合法',
		'query'=>'信息“{0}”已存在,您再换一个试试',
		'chinese'=>'只能包括中文和英文、数字和非特殊符号',
		'english'=>'只能包括英文字母、数字和非特殊符号',
		'username'=>'用户名只能包括中文字、英文字母、数字和下划线并以中文或英文字母开头',
		'password'=>'密码只能包括英文字母、数字和下划线',
	);

	/*
	* @desc 检查数据
	* @param $data:要验证的数据
	* @param $rules:数据验证规则
	* @param $messages:可选，提示消息
	* @return true为成功,false为失败,错误信息存与属性$error中
	*/
	function check($data,$rules,$messages=array()){
		if(!$messages && is_array($this->error)){
			$messages=$this->error;
		}
		foreach($rules as $key=>$rule){
			$this->key=$key;
			foreach($rule as $k=>$v){
				$this->error = (!empty($messages[$key][$k])?$messages[$key][$k]:$this->messages[strtolower($k)]);
				if(!method_exists($this,$k)){
					$this->error=sprintf('规则“%s”中的 "%s"验证方法未定义！',$key,$k);
					return false;
				}
				if(!$this->$k($data[$key],$v)){
					return false;
				}
			}
		}
		return true;
	}

	//不为空
	function required($data,$value=null){
		return ($value===false)?true:strlen($data)>0;
	}

	//等于
	function equal($data,$value){
		if(!$this->required($data)) return true;
		$this->error=str_replace('{0}',$data,$this->error);
		$this->error=str_replace('{1}',$value,$this->error);
		return $data==$value;
	}

	//电子邮件
	function email($email){
		if(!$this->required($email)) return true;
		return preg_match("/^[_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3}$/i",$email);
	}

	//整数
	function integer($integer){
		if(!$this->required($integer)) return true;
		return preg_match("/^[\+\-]?[0-9]*$/",$integer);
	}

	//无符号整数
	function uinteger($integer){
		if(!$this->required($integer)) return true;
		return preg_match("/^[0-9]*$/",$integer);
	}

	//浮点数
	function float($float){
		if(!$this->required($float)) return true;
		return preg_match("/^[\+\-]?[0-9]+(\.[0-9]+)?$/",$float);
	}

	//无符号浮点数
	function ufloat($ufloat){
		if(!$this->required($ufloat)) return true;
		return preg_match("/^[0-9]+(\.[0-9]+)?$/",$ufloat);
	}

	//最小
	function min($data,$min){
		if(!$this->required($data)) return true;
		$this->error=str_replace('{0}',$min,$this->error);
		return $data>=$min;
	}

	//最大
	function max($data,$max){
		if(!$this->required($data)) return true;
		$this->error=str_replace('{0}',$max,$this->error);
		return $data<=$max;
	}

	//正整数
	function range($data,$value){
		if(!$this->required($data)) return true;
		list($min,$max)=explode(',',$value);
		$this->error=str_replace('{1}',$max,str_replace('{0}',$min,$this->error));
		return $data<=$max && $data>=$min;
	}

	//手机
	function mobile($phone){
		if(!$this->required($phone)) return true;
		return preg_match("/^1(3|5|8)[0-9]{9}$/",$phone);
	}

	//电话号
	function phone($phone){
		if(!$this->required($phone)) return true;
		return preg_match("/^([0-9]{3,4}-?)?[0-9]{5,9}(-[0-9]{1,4})?$/",$phone);
	}

	//IP地址
	function ip($ip){
		if(!$this->required($ip)) return true;
		return preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/",$ip);
	}

	//字符最小长度
	function minlength($data,$len){
		if(!$this->required($data)) return true;
		$this->error=str_replace('{0}',$len,$this->error);
		return dstrlen($data)>=$len;
	}

	//字符最大长度
	function maxlength($data,$len){
		if(!$this->required($data)) return true;
		$this->error=str_replace('{0}',$len,$this->error);
		return dstrlen($data)<=$len;
	}

	//字符长度范围
	function rangelength($data,$value){
		if(!$this->required($data)) return true;
		list($min,$max)=explode(',',$value);
		$this->error=str_replace('{1}',$max,str_replace('{0}',$min,$this->error));
		return $min<=dstrlen($data) && dstrlen($data)<=$max;
	}

	//字符固定长度
	function length($data,$len){
		if(!$this->required($data)) return true;
		$this->error=str_replace('{0}',$len,$this->error);
		return dstrlen($data)==$len;
	}

	//URL地址
	function url($data){
		if(!$this->required($data)) return true;
		return preg_match("/^(https?|ftp|rtsp|mms|gopher|mailto|ed2k|thunder|flashget|news):\/\/[a-z\-_0-9\.]+([a-z0-9]+)+.*$/i",$data);
	}

	//日期时间
	function date($data){
		if(!$this->required($data)) return true;
		if(preg_match("/^[1-9][0-9]{3}\-[0-1]?[0-9]\-[0-3]?[0-9]$/",$data)){
			list($year,$month,$day)=explode('-',$data);
		}elseif(preg_match("/^[1-9][0-9]{3}\/[0-1][0-9]\/[0-3][0-9]$/",$data)){
			list($year,$month,$day)=explode('/',$data);
		}elseif(preg_match("/^[0-1][0-9]\/[0-3][0-9]\/[1-9][0-9]{3}$/",$data)){
			list($month,$day,$year)=explode('/',$data);
		}else{
			return false;
		}
		$year=intval($year);
		$month=intval($month);
		$day=intval($day);
		if($month==2){
			if($year%4==0 && ($year%100!=0 || $year%400==0)){
				return $day<=29 && $day>=1;
			}else{
				return $day<=28 && $day>=1;
			}
		}elseif(in_array($month,array('1','3','5','7','8','10','12'))){
			return $day<=31 && $day>=1;
		}else{
			return $day<=30 && $day>=1;
		}
		return false;
	}
	//自定义
	function custom($data,$value){
		if(!$this->required($data)) return true;
		return preg_match("/".$value."/",$data);
	}

	//查询
	function query($data,$value){
		$this->error=str_replace('{0}',$data,$this->error);
		if(is_array($value)){
			list($table,$where)=$value;
			return db::result_first('SELECT COUNT(*) FROM %t WHERE %i',array($table,$where))==0;
		}else{
			return db::result_first('SELECT COUNT(*) FROM %t WHERE `%i`=%s',array($value,$this->key,$data))==0;
		}
	}

	//URL地址
	function chinese($data){
		if(!$this->required($data)) return true;
		return preg_match("/^[\x21-\x7E\x{0391}-\x{FFE5}]+$/u",$data);
	}

	//URL地址
	function english($data){
		if(!$this->required($data)) return true;
		return preg_match("/^[\x21-\x7E]+$/",$data);
	}

	//URL地址
	function username($data){
		if(!$this->required($data)) return true;
		return preg_match("/^[a-zA-Z\x{0391}-\x{FFE5}][a-zA-Z0-9_\x{0391}-\x{FFE5}]+$/u",$data);
	}

	//URL地址
	function password($data){
		if(!$this->required($data)) return true;
		return preg_match("/^[a-z0-9_]+$/i",$data);
	}

	/**
	* 身份证15位编码规则：dddddd yymmdd xx p
	* dddddd：地区码
	* yymmdd: 出生年月日
	* xx: 顺序类编码，无法确定
	* p: 性别，奇数为男，偶数为女
	* <p />
	* 身份证18位编码规则：dddddd yyyymmdd xxx y
	* dddddd：地区码
	* yyyymmdd: 出生年月日
	* xxx:顺序类编码，无法确定，奇数为男，偶数为女
	* y: 校验码，该位数值可通过前17位计算获得
	* <p />
	* 18位号码加权因子为(从右到左) Wi = [ 7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2,1 ]
	* 验证位 Y = [ 1, 0, 10, 9, 8, 7, 6, 5, 4, 3, 2 ]
	* 校验位计算公式：Y_P = mod( ∑(Ai×Wi),11 )
	* i为身份证号码从右往左数的 2...18 位; Y_P为脚丫校验码所在校验码数组位置
	*([0-9]{17}([0-9xX]))|([0-9]{15})
	*/
	function idcard($idCard,$sex){
		if(empty($idCard)) return true;
		if(!preg_match("/^[0-9]{17}[0-9xX]$/",$idCard)) return false;
		if($this->IdCardVP($idCard)) return false;
		if (strlen($idCard) == 15) {
			return $this->IdCardVB15($idCard) && $sex==$this->IdCardVSex($idCard);
		}elseif(strlen($idCard) == 18){
			if($this->IdCardVB18($idCard) && $this->IdCardV18($idCard) && $sex==$this->IdCardVSex($idCard)){
				return true;
			}else{
				return false;
			}
		} else {
			return false;
		}
	}
	//判断身份证号码为18位时最后的验证位是否正确
	function IdCardV18($idCard) {
		$Vi=array('1', '0', 'x', '9', '8', '7', '6', '5', '4', '3', '2');
		$Wi=array(7,9,10,5,8,4,2,1,6,3,7,9,10,5,8,4,2,1);
		$sum = 0; // 声明加权求和变量
		for($i=0;$i<17;$i++){
			$sum += $Wi[$i] * substr($idCard,$i,1);// 加权求和
		}
		$p = $sum % 11;// 得到验证码所位置
		if (strtolower(substr($idCard,-1)) == $Vi[$p]) {
			return true;
		} else {
			return false;
		}
	}
	/**
	* 通过身份证判断是男是女
	* @param idCard 15/18位身份证号码
	* @return 'false'-女、'true'-男
	*/
	function IdCardVSex($idCard){
		if(strlen($idCard) == 15){
			return intval(substr($idCard,-2))%2==1;
		}elseif(strlen($idCard) == 18){
			return intval(substr($idCard,-4,3))%2==1;
		}else{
			return;
		}
	}
	/**
	* 验证18位数身份证号码中的生日是否是有效生日
	* @param idCard 18位身份证字符串
	* @return
	*/
	function IdCardVB18($idCard18){
		$year =  substr($idCard18,6,4);
		$month = substr($idCard18,10,2);
		$day = substr($idCard18,12,2);
		return checkdate(intval($month),intval($day),intval($year));
	}
	/**
	* 验证15位数身份证号码中的生日是否是有效生日
	* @param idCard15 15位身份证字符串
	* @return
	*/
	function IdCardVB15($idCard15){
		$year =  substr($idCard15,6,2);
		$month = substr($idCard18,8,2);
		$day = substr($idCard18,10,2);
		return checkdate($month,$day,$year);
	}
	/**
	* 验证15/位数身份证号码中的归属地
	* @param idCard15 15/18位身份证字符串
	* @return
	*/
	function IdCardVP($idCard){
		return false;
	}
}
