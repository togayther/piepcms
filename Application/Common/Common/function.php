<?php

/*
 * 验证手机号码格式
 */
function mobileValidate($mobile) {
	$regex = '/^(13[0-9]|147|15[0|1|2|3|4|5|6|7|8|9]|170|18[0|1|2|3|4|5|6|7|8|9])\d{8}$/';
	
	if (preg_match ( $regex, $mobile )) {
		return true;
	}
	return false;
}

/*
 * 验证短信验证码格式
 */
function captchaValidate($captcha) {
	$regex = '/^[A-Za-z0-9]{6}$/';
	
	if (preg_match ( $regex, $captcha )) {
		return true;
	}
	return false;
}

/*
 * 生成指定长度的随机字符串
 * */
function createNonceStr($length = 16){
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	$str = "";
	for ($i = 0; $i < $length; $i++) {
		$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
	}
	return $str;
}

/*
 * 将类似"06 11 2014 9:05AM"的字符串转换为日期格式
 */
function changeToTime($str){
	if($str==""){return "";}
	$strs=explode(' ',$str);
	$dateStr='';
	$_strs=null;
	$index=0;
	foreach($strs as $s){
		if(!empty($s)){
			$_strs[$index++]=$s;
		}
	}
	$strs=$_strs;
	$dateStr=$strs[2].'-'.$strs[0].'-'.$strs[1];
	$t=$strs[3];
	$val=0;
	if(strpos($t,'PM')){
		$val=12;
	}
	$t=str_replace('AM','',$t);
	$t=str_replace('PM','',$t);
	$dateStr=$dateStr.' '.$t;
	if($val==0){
		return date('Y-m-d H:i:s',strtotime($dateStr));
	}else{
		return date('Y-m-d H:i:s',strtotime($dateStr)+43200);
	}
}
