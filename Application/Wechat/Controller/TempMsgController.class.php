<?php
namespace Wechat\Controller;
use Think\Controller;
use Think\Log;
use Org\Tencent\WeChatAuth;

class TempMsgController extends Controller {
	
	public function __construct(){
	
		parent::__construct();
		
		$this->wechatAppId = C("WECHAT_APPID");
		$this->wechatAppSecret = C("WECHAT_APPSECRET");
	}
	
	/*
	 * 向指定用户发送模板消息
	 * */
	public function sendTempMsg($msgData){
		if ($msgData && count($msgData)>0){
			$wechat = new WeChatAuth($this->wechatAppId, $this->wechatAppSecret);
				
			$token = $wechat->getAccessToken("client");
			$access_token = $token["access_token"];
			
			$msgData = json_encode($msgData);
			$result = $wechat->sendTempMsg($access_token, $msgData);
			return $result;
		}
	}
}