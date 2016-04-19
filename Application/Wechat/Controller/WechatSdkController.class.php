<?php
namespace Wechat\Controller;
use Think\Controller;
use Org\Tencent\WeChatAuth;

class WechatSdkController extends Controller {
	
	private $wechatAppId = null;
	
	private $wechatAppSecret = null;
	
	public function __construct(){
	
		parent::__construct();
		
		$this->wechatAppId = C("WECHAT_APPID");
		$this->wechatAppSecret = C("WECHAT_APPSECRET");
	}
	
	/*
	 * 获取jsSdk配置需要的ticket
	 * */
	public function getJsApiTicket(){
		$ticket = S('WECHAT_JSAPI_TICKET');
		if (!$ticket || empty($ticket)) {
			$wechat = new WeChatAuth($this->wechatAppId, $this->wechatAppSecret);
			$token = $wechat->getAccessToken("client");
			$accessToken = $token["access_token"];
			if ($accessToken){
				$ticket = $wechat->getJsApiTicket($accessToken);
				if ($ticket) {
					S('WECHAT_JSAPI_TICKET',$ticket,7000);
				}
			}
		} 
		return $ticket;
	}
	
	/*
	 * 获取jsSdk配置
	 * */
	public function getSignPackage($ticket = ""){
		if(!$ticket || empty($ticket)){
			$ticket = $this->getJsApiTicket();
		}
		
		$url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$timestamp = time ();
		$nonceStr = createNonceStr();
		
		// 这里参数的顺序要按照 key 值 ASCII 码升序排序
		$string = "jsapi_ticket=$ticket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
		$signature = sha1 ( $string );
		$signPackage = array (
				"appId" => $this->wechatAppId,
				"nonceStr" => $nonceStr,
				"timestamp" => $timestamp,
				"url" => $url,
				"signature" => $signature,
				"rawString" => $string
		);
		
		return $signPackage;
	}
	
}