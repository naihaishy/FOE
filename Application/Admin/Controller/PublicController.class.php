<?php
//1.定义命名空间
namespace Admin\Controller;
//2.引入父类控制器
use Common\Controller\AccountsController;

//3.定义控制器并且继承父类
class PublicController extends AccountsController{

    /**  
     * 登录
     * @access public
     * @param   
     * @return   
     */
    public function login(){
        $this->display();
 
    }
    
  
    
  
    /**  
     * 登录验证
     * @access public
     * @param   
     * @return   
     */
    public function checkLogin(){
        if(!IS_POST) exit();
        $post   =   I('post.');//接收数据
        A('Common/Accounts')->checkAccountLogin($post, 'admin', U('Admin/Index/index') );
         
    }
    
    /**  
     * 管理员注册
     * @access public
     * @param   
     * @return   
     */ 
    public function signup(){
        //判断请求类型
        if(IS_POST){
            //处理请求
            $post   =   I('post.');
            A('Common/Accounts')->accountSignUp($post, 'admin', U('Admin/Public/login') );
            
        }else{
            //显示模板
            $this->display();
        }
    }
    
    /**  
     * 验证邮箱 激活账号
     * @access public
     * @param   
     * @return   
     */
    public function activation($accessToken){
        if(empty($accessToken)) $this->error('无效的激活Token'); 
        $this->activateAccount($accessToken, 'admin');
    }

    
    /**  
     * 验证邮箱是否已经注册
     * @access public 
     * @param   
     * @return   
     */
    public function checkEmail($email){
        $has_exists = M('User')->where(array('email'=>$email))->find();
        if($has_exists) $this->ajaxReturn('该邮箱已经注册');//已经注册 
    }
    
    
    /**  
     * 忘记密码
     * @access public
     * @param   
     * @return   
     */
    public function forget(){
        if(IS_POST){
            $email = I('post.email');
            $this->forgetPass($email, 'admin');
        }else{
            $this->display();
        }
        
    }
    
  
    
 
 
    
}





