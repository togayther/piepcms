<?php

namespace Org\Tencent;
use Think\Log;

class WeChatAuth {
    
    /* 消息类型常量 */
    const MSG_TYPE_TEXT     = 'text';
    const MSG_TYPE_IMAGE    = 'image';
    const MSG_TYPE_NEWS     = 'news';
    const MSG_TYPE_EVENT    = 'event';
    
    /**
     * 微信开发者申请的appID
     * @var string
     */
    private $appId = '';

    /**
     * 微信开发者申请的appSecret
     * @var string
     */
    private $appSecret = '';

    /**
     * 获取到的access_token
     * @var string
     */
    private $accessToken = '';

    /**
     * 微信api根路径
     * @var string
     */
    private $apiURL = 'https://api.weixin.qq.com/cgi-bin';

    private $requestCodeURL = 'https://open.weixin.qq.com/connect/oauth2/authorize';

    private $oauthApiURL = 'https://api.weixin.qq.com/sns';

    private $qrcodeApiURL = 'https://api.weixin.qq.com/cgi-bin/qrcode/create';
    
    private $jsapiTicketURL = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi';

    private $tempMsgURL = 'https://api.weixin.qq.com/cgi-bin/message/template/send';
    
    /**
     * 构造方法，调用微信高级接口时实例化SDK
     * @param string $appid  微信appid
     * @param string $secret 微信appsecret
     * @param string $token  获取到的access_token
     */
    public function __construct($appid, $secret, $token = null){
        if($appid && $secret){
            $this->appId     = $appid;
            $this->appSecret = $secret;

            if(!empty($token)){
                $this->accessToken = $token;
            }
        } else {
            throw new \Exception('参数错误！');
        }
    }

    public function getRequestCodeURL($redirect_uri, $state = null, $scope = 'snsapi_userinfo'){
        
        $query = array(
            'appid'         => $this->appId,
            'redirect_uri'  => $redirect_uri,
            'response_type' => 'code',
            'scope'         => $scope
        );

        if(!is_null($state) && preg_match('/[a-zA-Z0-9]+/', $state)){
            $query['state'] = $state;
        }

        $query = http_build_query($query);
        $codeUrl = "{$this->requestCodeURL}?{$query}#wechat_redirect";

        return $codeUrl;
    }

    /**
     * 获取access_token，用于后续接口访问
     * @return array access_token信息，包含 token 和有效期
     */
    public function getAccessToken($type = 'client', $code = null){

    	//首先读取缓存
    	if($type=="client"){
    		$accessToken = S("WECHAT_CLIENT_TOKEN");
    		if ($accessToken && !empty($accessToken)){
    			$this->accessToken = $accessToken;
    			return array(
                    "access_token" => $this->accessToken
                );
    		}
    	}
    	
        $param = array(
            'appid'  => $this->appId,
            'secret' => $this->appSecret
        );

        switch ($type) {
            case 'client':
                $param['grant_type'] = 'client_credential';
                $url = "{$this->apiURL}/token";
                break;

            case 'code':
                $param['code'] = $code;
                $param['grant_type'] = 'authorization_code';
                $url = "{$this->oauthApiURL}/oauth2/access_token";
                break;
            
            default:
                throw new \Exception('不支持的grant_type类型！');
                break;
        }

        $token = self::httpOperation($url, $param);

        $token = json_decode($token, true);

        if(is_array($token)){
        	
        	//加入缓存
        	if($type=="client" && !empty($token['access_token'])){
        		S("WECHAT_CLIENT_TOKEN", $token['access_token'], 3600);
        	}
        	
            $this->accessToken = $token['access_token'];
            return $token;
        } else {
            throw new \Exception('获取微信access_token失败！');
        }
    }

    /**
     * 获取场景二维码Ticket
     * @param  string $token acess_token
     * @param  string $qrtype  二维码类型
     * @return string     二维码ticket
     */
    public function getQrcodeTicket($access_token, $sceneVal, $qrtype = "QR_LIMIT_SCENE"){

        if (empty($access_token) || !$sceneVal) {
            return "";
        };

        $url = "{$this->qrcodeApiURL}?access_token=".$access_token;
        $data = array(
            'action_name' => $qrtype,
            'action_info' =>array(
                'scene'=>array(
                    'scene_id'=> $sceneVal
                )
            )
        );
        if ($qrtype=="QR_SCENE") {
            $data['expire_seconds'] = 1800;
        }

        $data = json_encode($data);
        $result = self::httpOperation($url, '', $data, "POST");
        
        $dataResult = json_decode($result, true);

        return $dataResult["ticket"];
    }
    
    /**
     * 获取jsapi配置ticket
     * @param  string $token acess_token
     * @return string     jsapi ticket
     */
    public function getJsApiTicket($access_token){
    	$url = "{$this->jsapiTicketURL}&access_token=".$access_token;
    	$result = self::httpOperation($url, null);
    	
    	$resultData = json_decode($result, true);
    	if ($resultData && is_array($resultData)){
    		return $resultData["jsapi_ticket"];
    	}
    	return null;
    }
    
    /**
     * 发送微信模板消息 
     * @param  string $access_token acess_token
     * @param  string $msgData msgData
     */
    public function sendTempMsg($access_token, $msgData){
    	$url = "{$this->tempMsgURL}?access_token=".$access_token;
    	$result = self::httpOperation($url, '', $msgData, $method = 'POST');
    	return $result;
    }

    /**
     * 获取授权用户信息
     * @param  string $token acess_token
     * @param  string $lang  指定的语言
     * @return array         用户信息数据，具体参见微信文档
     */
    public function getUserInfo($access_token, $openid, $lang = 'zh_CN'){
        $query = array(
            'access_token' => $access_token,
            'openid'       => $openid,
            'lang'         => $lang,
        );

        $info = self::httpOperation("{$this->apiURL}/user/info", $query);
        return json_decode($info, true);
    }

    /**
     * 给指定用户推送信息
     * 注意：微信规则只允许给在48小时内给公众平台发送过消息的用户推送信息
     * @param  string $openid  用户的openid
     * @param  array  $content 发送的数据，不同类型的数据结构可能不同
     * @param  string $type    推送消息类型
     */
    public function messageCustomSend($openid, $content, $type = self::MSG_TYPE_TEXT){
        
        //基础数据
        $data = array(
            'touser'=>$openid,
            'msgtype'=>$type,
        );

        //根据类型附加额外数据
        $data[$type] = call_user_func(array(self, $type), $content);

        return $this->api('message/custom/send', $data);
    }

    /**
     * 发送文本消息
     * @param  string $openid 用户的openid
     * @param  string $text   发送的文字
     */
    public function sendText($openid, $text){
        return $this->messageCustomSend($openid, $text, self::MSG_TYPE_TEXT);
    }

    /**
     * 发送图片消息
     * @param  string $openid 用户的openid
     * @param  string $media  图片ID
     */
    public function sendImage($openid, $media){
        return $this->messageCustomSend($openid, $media, self::MSG_TYPE_IMAGE);
    }

    /**
     * 发送图文消息
     * @param  string $openid 用户的openid
     * @param  array  $news   图文内容 [标题，描述，URL，缩略图]
     * @param  array  $news1  图文内容 [标题，描述，URL，缩略图]
     * @param  array  $news2  图文内容 [标题，描述，URL，缩略图]
     *                ...     ...
     * @param  array  $news9  图文内容 [标题，描述，URL，缩略图]
     */
    public function sendNews(){
        $news   = func_get_args();
        $openid = array_shift($news);
        return $this->messageCustomSend($openid, $news, self::MSG_TYPE_NEWS);
    }

    /**
     * 发送一条图文消息
     * @param  string $openid      用户的openid
     * @param  string $title       文章标题
     * @param  string $discription 文章简介
     * @param  string $url         文章连接
     * @param  string $picurl      文章缩略图
     */
    public function sendNewsOnce(){
        $news   = func_get_args();
        $openid = array_shift($news);
        $news   = array($news);
        return $this->messageCustomSend($openid, $news, self::MSG_TYPE_NEWS);
    }

    /**
     * 获取指定用户的详细信息
     * @param  string $openid 用户的openid
     * @param  string $lang   需要获取数据的语言
     */
    public function userInfo($openid, $lang = 'zh_CN'){
        $param = array('openid' => $openid, 'lang' => $lang);
        return $this->api('user/info', '', 'GET', $param);
    }

    /**
     * 创建自定义菜单
     * @param  array $button 符合规则的菜单数组，规则参见微信手册
     */
    public function menuCreate($button){
        $data = array('button' => $button);
        return $this->api('menu/create', $data);
    }

    /**
     * 获取所有的自定义菜单
     * @return array  自定义菜单数组
     */
    public function menuGet(){
        return $this->api('menu/get', '', 'GET');
    }

    /**
     * 删除自定义菜单
     */
    public function menuDelete(){
        return $this->api('menu/delete', '', 'GET');
    }

    /**
     * 调用微信api获取响应数据
     * @param  string $name   API名称
     * @param  string $data   POST请求数据
     * @param  string $method 请求方式
     * @param  string $param  GET请求参数
     * @return array          api返回结果
     */
    protected function api($name, $data = '', $method = 'POST', $param = ''){
        $params = array('access_token' => $this->accessToken);

        if(!empty($param) && is_array($param)){
            $params = array_merge($params, $param);
        }

        $url  = "{$this->apiURL}/{$name}";
        if(!empty($data)){
            //保护中文，微信api不支持中文转义的json结构
            /*
            array_walk_recursive($data, function(&$value){
                $value = urlencode($value);
            });
            */
            $data = urldecode(json_encode($data));
        }

        $data = self::httpOperation($url, $params, $data, $method);

        return json_decode($data, true);
    }

    /**
     * 发送HTTP请求方法，目前只支持CURL发送请求
     * @param  string $url    请求URL
     * @param  array  $param  GET参数数组
     * @param  array  $data   POST的数据，GET请求时该参数无效
     * @param  string $method 请求方法GET/POST
     * @return array          响应数据
     */
    public static function httpOperation($url, $param, $data = '', $method = 'GET'){
        $opts = array(
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        );

        /* 根据请求类型设置特定参数 */
        if(!empty($param)){
        	$url = $url . '?' . http_build_query($param);
        }
        $opts[CURLOPT_URL] = $url;
        if(strtoupper($method) == 'POST'){
            $opts[CURLOPT_POST] = 1;
            $opts[CURLOPT_POSTFIELDS] = $data;
            
            if(is_string($data)){ //发送JSON数据
                $opts[CURLOPT_HTTPHEADER] = array(
                    'Content-Type: application/x-www-form-urlencoded; charset=utf-8',  
                    'Content-Length: ' . strlen($data),
                );
            }
        }

        /* 初始化并执行curl请求 */
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data  = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        //发生错误，抛出异常
        if($error) throw new \Exception('请求发生错误：' . $error);

        return  $data;
    }
}

?>
