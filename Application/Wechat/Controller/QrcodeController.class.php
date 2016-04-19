<?php
namespace Wechat\Controller;
use Think\Controller;
use Think\Log;
use Org\Tencent\WeChatAuth;
use Org\Net\HttpClient;

class QrcodeController extends Controller {

	const QRCODE_SHOWURL = 'https://mp.weixin.qq.com/cgi-bin/showqrcode';

	/*
		根据参数生成场景值二维码 ticket
	*/
	public function ticket($sceneVal){
		if (!$sceneVal) {
			return ;
		}
		$wechatAppId = C("WECHAT_APPID");
		$wechatAppSecret = C("WECHAT_APPSECRET");

		$wechatAuth = new WeChatAuth($wechatAppId, $wechatAppSecret);
	    $token = $wechatAuth->getAccessToken("client");
		
	    $access_token = $token["access_token"];
	    if (!$access_token) {
	    	return ;
	    }
	    $qrcodeTicket = $wechatAuth->getQrcodeTicket($access_token, $sceneVal, "QR_SCENE");

	    return $qrcodeTicket;
	}
	
	/*
	 * 根据场景ticket获取二维码链接
	 * 临时二维码请勿保存
	 * */
	public function qrcodeUrlByTicket($ticket, $isdownload = false, $savePath = ""){
		if ($ticket && !empty($ticket)) {
			$qrcodeOnlineUrl = SELF::QRCODE_SHOWURL."?ticket=".$ticket;
			if ($isdownload === true) {
		
				$httpClient = new HttpClient();
				$imageInfo = $httpClient->downloadImage($qrcodeOnlineUrl);
		
				if (empty($savePath)) {
					$savePath = PUBLIC_PATH."Qrcode/".$ticket.".png";
				}
		
				$localFile = fopen($savePath, "W");
		
				if (false != $localFile) {
					if (false != fwrite($localFile, $imageInfo["body"])) {
						fclose($localFile);
		
						return $savePath;
					}
				}
			}
			return $qrcodeOnlineUrl;
		}
	}

	/*
		根据参数生成场景值二维码地址。
		如果$isdownload = true，则为本地地址
		否则，为腾讯线上地址
		note:临时二维码请勿保存
	*/
	public function qrcodeUrl($sceneVal, $isdownload = false, $savePath = ""){
		
		$ticket = $this->ticket($sceneVal);
		
		return $this->qrcodeUrlByTicket($ticket,$isdownload,$savePath);
	}
}