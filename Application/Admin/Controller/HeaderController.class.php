<?php
//1.声明命名空间
namespace Admin\Controller;
//2.引入父类控制器
use Think\Controller;
//3.定义类并扩展父类

class HeaderController extends CommonController{
    
    

    //显示邮件未读数目
    public function showEmailUnreadCount(){
        //判断请求ajax
        if(IS_POST){
            $count = M('Email')->where(array('isread'=>0, 'to_id'=>session('uid')) )->count();
            session('emunreadcount', $count);
            $this->ajaxReturn($count);
        }
    }
    
 
    
    //前5条未读内容
     public function showEmailUnreadContent(){
        //判断请求ajax
         if(IS_POST){
            
            $emcon = M('Email')->where(array('isread'=>0, 'to_id'=>session('uid')) )->limit(5)->order('post_time')->select();   
            session('emcon', $emcon);
            $this->ajaxReturn($emcon);
        }
         
    }
 
    

    
}




