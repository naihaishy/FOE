<?php
namespace Api\Controller;
use Think\Controller;
class WechatController extends Controller {
	
	
		public function __construct(){
			
		}
    public function index(){
		//获得参数 signature nonce token timestamp echostr
		$data=I('get.');
		//dump($data);die;
		$nonce     = $data['nonce'];
		$token     = 'weixin';
		$timestamp = $data['timestamp'];
		$echostr   = $data['echostr'];
		$signature = $data['signature'];
		//形成数组，然后按字典序排序
		$array = array();
		$array = array($nonce, $timestamp, $token);
		sort($array);
		//拼接成字符串,sha1加密 ，然后与signature进行校验
		$str = sha1(implode($array));
		if( $str  == $signature && $echostr ){
			//第一次接入weixin api接口的时候
			//dump('ok');die;
			ob_clean();
			 echo $echostr;
		}
	}
	
     
}