<?php
//1.定义命名空间
namespace Teacher\Controller;
//2.引入父类控制器
use Think\Controller;

//3.定义控制器并且继承父类
class BaseController extends Controller{
	
 		/*---------验证码处理--------*/
 		
 		//登录验证码 captcha方法
		public function captcha(){
			//配置
			$config=array(
					'fontSize'  =>  25,              // 验证码字体大小(px)
	        'useCurve'  =>  true,            // 是否画混淆曲线
	        'useNoise'  =>  true,            // 是否添加杂点	
	        'length'    =>  4,               // 验证码位数
	        'fontttf'   =>  '4.ttf',              // 验证码字体，不设置随机获取
			);
			//实例化验证码类
			$verify=new \Think\Verify($config);
			//输出验证码
			$verify->entry();
		}
		
		
		
		
		/*---------短信处理----------*/
    
    //注册短信验证码
		public function sendSms($tel){
			
			$verify_code = generate_numcode(6);
			$send_time = time();
			$result = sms_verf_code($tel,$verify_code); 
				//发送成功 保存至数据库
				$data['verify_code']=$verify_code;
				$data['send_time']= $send_time;
				$data['token']= md5($tel . $send_time);
				$data['tel']= $tel;
				$_SESSION['sms_token'] = $data['token'];
				
				M('Sms')->add($data);	
		}
		
		public function checkSms($verify_code){
			
			$condition['token'] = session('sms_token');
			
			$result = M('Sms')->where($condition)->find();
			//dump($result);die;
			if($result){
				//存在此消息
				if($verify_code == $result['verify_code']){
					//验证成功
					return true;
				}

			}else{
				return false;
			}
		}
	
		 
	
	
	
	
	
	
	
	
	
 
}
