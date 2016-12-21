<?php
if(!defined('IN_LW')) {
	exit('Access Denied');
}

class model_talk extends model_base{
	protected $table='talk';
	protected $priKey='tid';
	protected $forKey='uid';
	function drop($id, $limit = 0, $unbuffered = true,$isFirst=true){
		if($isFirst){
			$ret=parent::drop($id,$limit,$unbuffered);
		}else{
			$ret=true;
		}
		if($ret && ($pids=$this->get_list('pid='.$id,$this->priKey))){
			$this->delete('pid='.$id,$limit,$unbuffered);
			foreach($pids as $tid){
				$this->drop($tid,$limit,$unbuffered,false);
			}
		}
		return $ret;
	}
}
