<?php
//1.定义命名空间
namespace Teacher\Controller;
//2.引入父类控制器
use Think\Controller;

//3.定义控制器并且继承父类
class CommonController extends Controller{
	
		//验证教师登录
		public function _initialize(){
			
			$id=session('uid');
			$rid=session('rid');
			if(empty($id)){
				$this->error('请先登录教师账号',U('Home/Teacher/login'),3);
			}else{
				if($rid != 2 ){
					$this->error('对不起这不是教师账号',U('Home/Teacher/login'),3);
				}
			}

			//初始化操作

			$email_unread_count   	=   D('Common/Email')->getUnreadCount(session('uid'), session('rid'));//获取未读信件数目
			$email_unread 			= 	D('Common/Email')->getUnread(session('uid'), session('rid'), 3);//获取未读信件
			session('email_unread_count', $email_unread_count);
			session('email_unread', $email_unread);
			//dump($email_unread);die;
		}
		
		
		 
		
		
	
		 
	
		 
	
	
	
	
	
	
	
	
	
 
}
