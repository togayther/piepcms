<?php
namespace Wechat\Controller;
use Think\Controller;
use Org\Tencent\WeChatAuth;

class WechatUserController extends Controller {
	
	private $wechatAppId = null;
	
	private $wechatAppSecret = null;
	
	public function __construct(){
	
		parent::__construct();
		
		$this->wechatAppId = C("WECHAT_APPID");
		$this->wechatAppSecret = C("WECHAT_APPSECRET");
	}
	
	/*
	 * 根据微信openid拉取用户信息
	 * */
	public function getUserInfo($openid){
		
		if ($openid && !empty($openid)){
			$wechat = new WeChatAuth($this->wechatAppId, $this->wechatAppSecret);
			
			$token = $wechat->getAccessToken("client");
			$access_token = $token["access_token"];
			
			$userInfo = $wechat->getUserInfo($access_token, $openid);
			
			return $userInfo;
		}
		
		return null;
	}
}