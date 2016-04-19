<?php
namespace Wechat\Controller;
use Think\Controller;
use Think\Log;
use Org\Tencent\WeChat;
use Org\Tencent\WeChatAuth;
use Org\Util\DeEncrypt;
use Org\Net\HttpClient;

class IndexController extends Controller {

	//消息回复类型
	private $replyType = "";

	//消息回复内容
	private $replyContent = "";

	//客服人员信息
	private $servicer = "";
	
	//微信消息处理器
	private $wechatHandler = null;

	public function __construct(){

		parent::__construct();

		$this->wechatHandler = new WeChat(C("WECHAT_TOKEN"));
		
		$this->replyType = C("WECHAT_REPLY_TYPE.TEXT");
	}
    	
	/*
	 	微信验证接口
	*/
	public function index(){

		if($this->checkSignature()){
        	$this->replyMessage(); 
        }else{
        	exit();
        }
	}

	/*
		回复消息
	*/
	private function replyMessage(){
    	$data = $this->wechatHandler->request();
    	
    	if($data && is_array($data)){
    		$type = $data["MsgType"];
    		$openid = $data['FromUserName'];
    		$eventKey = $data["EventKey"];
        	$transInfo = null; //多客服指定接待
        	$replyContent = null;
        	switch ($type) {
        		case WeChat::MSG_TYPE_TEXT:
        			$keyword = $data["Content"];
        			$this->setReplyContent($keyword);
        			break;
        		case WeChat::MSG_TYPE_EVENT:
        			$eventName = $data["Event"];
        			switch ($eventName) {
        				//关注事件
	        			case WeChat::MSG_EVENT_SUBSCRIBE:
	        				//关注时存储扫描归属关系
	        				$this->qrcodeScanCallback(WeChat::MSG_EVENT_SUBSCRIBE, $openid, $eventKey);
	        				//设置回复内容
	        				$this->setReplyContent("redbag_welcome");
	        				break;
	        			//退订事件
	        			case WeChat::MSG_EVENT_UNSUBSCRIBE:
	        				$keyword = "iwillbeback";
	        				$this->setReplyContent($keyword);
	        				break;
	        			//菜单点击
	        			case WeChat::MSG_EVENT_CLICK:
	        				$this->setReplyContent($keyword);
	        				break;
	        			case WeChat::MSG_EVENT_SCAN:
	        				//扫描时存储扫描归属关系
							$this->qrcodeScanCallback(WeChat::MSG_EVENT_SCAN, $openid, $eventKey);
							//设置回复内容
	        				$this->setReplyContent("redbag_welcome");
	        				break;
	        			default:
	        				break;
	        		}
        			break;
        		default:
        			break;
        	}
        	//消息回复处理
        	$this->handleReply($openid);
    	}
	}
	
	/*
	 * 消息回复处理
	 * */
	private function handleReply($openId = ""){
		
		if (!$this->type || !$this->replyContent){
			return ;
		}
		
		//回复图文
		if ($this->type == WeChat::MSG_TYPE_IMAGE) {
			$linkUrl = $this->formatLinkUrl($openId, $this->replyContent["linkurl"]);
			$this->wechatHandler->replyNewsOnce($this->replyContent["title"], $this->replyContent["descr"], $linkUrl, $this->replyContent["picurl"]);
		}
		//回复文本
		else{
			$this->wechatHandler->response($this->replyContent, $this->type, $this->servicer);
		}
	}
      
	/*
		根据指定关键字获取回得内容
	*/
	private function setReplyContent($keyword){
		$keywordService = D("Keyword","Service");

		$this->replyContent = $keywordService->getKeywordInfo($keyword);
		$this->type = WeChat::MSG_TYPE_TEXT;

		if ($this->replyContent && !empty($this->replyContent)) {
			if (!is_string($this->replyContent)) {
				$this->type = WeChat::MSG_TYPE_IMAGE;
			}
		}else if($this->isInWorkTime()){
			$this->type = WeChat::MSG_TYPE_SERVICE;
			$this->servicer = $this->getServicePerson();
		}else{
			$keyword = "noworktime";
			$this->$replyContent = $this->getKeywordInfo($keyword);
		}
	}
	
	/*
	 * 用户 场景二维码扫描事件处理
	 * */
	private function qrcodeScanCallback($eventType, $openid,  $sceneKey){
		
		if($sceneKey && !empty($sceneKey)){
			$sceneVal = $sceneKey;
			if ($eventType === WeChat::MSG_EVENT_SUBSCRIBE){ //未关注触发
				if($sceneKey && !empty($sceneKey) && strpos($sceneKey, "qrscene")!==false){
					$sceneVal = str_replace("qrscene_", "", $sceneKey);
				}
			}
			$data["scan_openid"] = $openid;
			$data["refer_id"] = $sceneVal;
			$data["scandate"] = date("Y-m-d H:i:s");
			$data["type"] = C("DEFAULT_SCAN_TYPE");
			$data["status"] = 1;
			$qrcodeScanService = D("QrcodeScan","Service");
			$qrcodeScanService->insertScanInfo($data);
		}
	}
	
	/*
		检查微信身份信息
	*/
    private function checkSignature(){
        $signature = $_REQUEST["signature"];
        $timestamp = $_REQUEST["timestamp"];
        $nonce = $_REQUEST["nonce"];
		$token = C("WECHAT_TOKEN");
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		return $tmpStr==$signature;
	}

	/*
	    格式化分享导向链接，加入相关参数
	*/
	private function formatLinkUrl($openid, $orignUrl){

		if (C($orignUrl) != null) {
			$orignUrl = C($orignUrl);
		}
		if (strpos($orignUrl, '{shareCode}')) {
		    $manager_code  = C("DEFAULT_MANAGER_CODE");
			$userService = D('Redbag/User',"Service");
			$userInfo = $userService->getUserInfoByOpenId($openid);
			$encrypt_data = $manager_code.'##'.$userInfo->id;
			$encrypt_str =  DeEncrypt::encrypt($encrypt_data,$userInfo->id);

			$paramStr = urlencode($encrypt_str.'|-'.$userInfo->id);
			$orignUrl = str_replace("{shareCode}",$paramStr,$orignUrl);
		}

		return $orignUrl;
	}

	/*
		检测是否属于工作时间
	*/
	private  function isInWorkTime(){
		$currentDate = date("Y-m-d H:i:s");
		$startDate = date("Y-m-d 08:30:00");
		$endDate = date("Y-m-d 22:00:00");	
		$weekDay = date("N");
		if ($weekDay>5) {
			$startDate = date("Y-m-d 10:00:00");
			$endDate = date("Y-m-d 21:30:00");	
		}

		if ($currentDate < $startDate || $currentDate > $endDate) {
			return false;
		}
		return true;
	}

	/*
		获取在线客服
     */
    public function getServicePerson(){

    	$wechatAppId = C("WECHAT_APPID");
    	$wechatAppSecret = C("WECHAT_APPSECRET");

    	$wechat = new WeChatAuth($wechatAppId, $wechatAppSecret);
        $token = $wechat->getAccessToken("client");
		$access_token = $token["access_token"];
		$req_url = "https://api.weixin.qq.com/cgi-bin/customservice/getonlinekflist?access_token=".$access_token;

		$result = HttpClient::quickGet($req_url);

        $resultList = json_decode($result, true);
        $onlinePerson = array();

        if(is_array($resultList)){
            $persons =  $resultList['kf_online_list'];
            if ($persons && count($persons)>0) {
            	foreach($persons as $person){
            		if ($person['status'] == 1) {
            			$onlinePerson[] = $person['kf_account'];
            		}
            	}
            }
        }
        if (count($onlinePerson)>0) {
			$rand_keys = array_rand($onlinePerson, 1);
			return $onlinePerson[$rand_keys] ;
        } 
        return "1001@hxzqlczj";
    }
}