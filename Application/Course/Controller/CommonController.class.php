<?php
//1.定义命名空间
namespace Course\Controller;
//2.引入父类控制器
use Think\Controller;

//3.定义控制器并且继承父类
class CommonController extends Controller{
	
		//验证登录
		public function _initialize(){
			$uid=session('uid');
			$rid=session('rid');
			//判断是否登录
			//判断来源 返回需要登录的界面 .....

			if(empty($uid) or empty($rid)){
				//没有登录
				$this->error('请先登录教师账号',U('Home/Teacher/login'),2);
			}elseif($rid==1){
				//学生无权限  先这么判断 后期基于auth认证 角色分配
				$this->error('对不起,你没有权限','',2);
			}elseif($rid==3){
				//学生无权限  先这么判断 后期基于auth认证 角色分配
				$this->error('对不起,你没有权限','',2);
			}
			
		}
		
		
		
	
		 
	
		 
	
	
	
	
	
	
	
	
	
 
}
