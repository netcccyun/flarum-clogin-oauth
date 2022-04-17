<?php

namespace Cccyun\Oauth;
use Exception;

class CloginOauth
{

  private $apiurl;
	private $appid;
	private $appkey;
	private $callback;

	function __construct($config){
		$this->apiurl = $config['apiurl'].'connect.php';
		$this->appid = $config['appid'];
		$this->appkey = $config['appkey'];
		$this->callback = $config['callback'];
	}

	//获取登录跳转url
	public function login($type, $state){

		//-------构造请求参数列表
		$keysArr = array(
			"act" => "login",
			"appid" => $this->appid,
			"appkey" => $this->appkey,
			"type" => $type,
			"redirect_uri" => $this->callback,
			"state" => $state
		);
		$login_url = $this->apiurl.'?'.http_build_query($keysArr);
		$response = $this->get_curl($login_url);
		$arr = json_decode($response,true);
		if(isset($arr['code']) && $arr['code']==0){
      return $arr['url'];
    }else{
      throw new Exception($arr['msg']?$arr['msg']:'登录地址获取失败');
    }
	}

	//登录成功返回网站
	public function callback($code){
		//-------请求参数列表
		$keysArr = array(
			"act" => "callback",
			"appid" => $this->appid,
			"appkey" => $this->appkey,
			"code" => $code
		);

		//------构造请求access_token的url
		$token_url = $this->apiurl.'?'.http_build_query($keysArr);
		$response = $this->get_curl($token_url);

		$arr = json_decode($response,true);
    if(isset($arr['code']) && $arr['code']==0){
      return $arr;
    }else{
      throw new Exception($arr['msg']?$arr['msg']:'登录数据获取失败');
    }
	}

	//查询用户信息
	public function query($type, $social_uid){
		//-------请求参数列表
		$keysArr = array(
			"act" => "query",
			"appid" => $this->appid,
			"appkey" => $this->appkey,
			"type" => $type,
			"social_uid" => $social_uid
		);

		//------构造请求access_token的url
		$token_url = $this->apiurl.'?'.http_build_query($keysArr);
		$response = $this->get_curl($token_url);

		$arr = json_decode($response,true);
    if(isset($arr['code']) && $arr['code']==0){
      return $arr;
    }else{
      throw new Exception($arr['msg']?$arr['msg']:'登录数据获取失败');
    }
	}

	private function get_curl($url){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36");
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$ret = curl_exec($ch);
		curl_close($ch);
		return $ret;
	}
}
