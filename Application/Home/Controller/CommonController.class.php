<?php
//1.定义命名空间
namespace Home\Controller;
//2.引入父类控制器
use Think\Controller;

//3.定义控制器并且继承父类
class CommonController extends Controller{
    
        //验证学员登录
        public function _initialize(){

            //网站状态
            if(C('WEB_STATUS')=='close'){
                $this->display('Public/close');
            }


            $uid=session('uid');
            $rid=session('rid');
            //判断学员是否登录
            if(empty($uid) or empty($rid) ){
                //学员没有登录
                $this->error('请先登录',U('Index/login'),3);
            }elseif($rid != '3'){
                $this->error('您的身份不是学员',U('Index/index'),3);
            }
            
        }
    
    
    
    
    
    
 
}
