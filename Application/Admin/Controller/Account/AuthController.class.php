<?php

namespace Admin\Controller\Account;

use Think\Controller;

class AuthController extends Controller {
	
	/*
	 * 登录主页
	 * */
	public function index() {
		
		$rememberUser = cookie(C("COOKIE_KEY.LOGIN_REMEMBER"));
		if (!empty($rememberUser)) {
			$userInfo = explode("|*|",$rememberUser);
		
			$this->assign("username",$userInfo[0]);
			$this->assign("password",$userInfo[1]);
		}
		$this->display ("login");
	}
	
	/*
	 * 登录处理
	 * */
	public function login(){
		$username = I('username');
		$password = I('password');
	
		$isremember = I('isrember');
		
		$accountService = D("AccountAuth","Service");
		
		$accountInfo = $accountService->login($username, md5($password));
		
		$data = array();
		
		if ($accountInfo && count($accountInfo)>0){
			if ($isremember == "true"){
				$cookieVal = $username."|*|".$password;
				cookie(C("COOKIE_KEY.LOGIN_REMEMBER"), $cookieVal, time()+3600*24*30);
			}else{
				cookie(C("COOKIE_KEY.LOGIN_REMEMBER"),"",time()-3600);
			}
			session(C("SESSION_KEY.USERNAME"), $accountInfo["name"]);
			session(C("SESSION_KEY.USERID"), $accountInfo["id"]);
			session(C("SESSION_KEY.USERGROUP"), $accountInfo["groupid"]);
			
			$data["status"] = 200;
			$data["msg"] = U("admin/account/index/index");
			
		}else{
			$data["status"] = 400;
			$data["msg"] = "错误的用户名或密码";
		}
		
		$this->ajaxReturn ( $data );
	}
	
	/*
	 * 注销登录
	 * */
	public function Logout(){
		
		session(C("SESSION_KEY.USERNAME"),null);
		session(C("SESSION_KEY.USERID"),null);
		session(C("SESSION_KEY.USERGROUP"), null);
		
		$this->display('login');
	}
}