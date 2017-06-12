<?php
namespace Home\Controller;
use Think\Controller;
class TeacherController extends BaseController {
 
    public function index(){
        
        
    }
        
    /**  
     * 教师登录
     * @access public
     * @param   
     * @return   
     */ 
    public function login(){
        $this->display();
    }
        

    /**  
     * 教师登录验证
     * @access public
     * @param   
     * @return   
     */
    public function checkLogin(){
        if(!IS_POST) exit();
        $post   =   I('post.');//接收数据
        A('Common/Accounts')->checkAccountLogin($post, 'teacher', U('Teacher/Index/index') );
    }

        
    /**  
     * 教师注册
     * @access public
     * @param   
     * @return   
     */ 
         
    public function signup(){
        //判断请求类型
        if(IS_POST){
            //处理请求
            $post   =   I('post.');
            A('Common/Accounts')->accountSignUp($post, 'teacher', U('Home/Teacher/login') );

        }else{
            //显示模板
            $this->display();    
        }
    }
        

    /**  
     * 验证邮箱 激活教师账号
     * @access public
     * @param   
     * @return   
     */
    public function activation($accessToken){
        if(empty($accessToken)) $this->error('无效的激活Token'); 
        $this->activateAccount($accessToken, 'teacher');
    }



   


    /**  
     * 验证邮箱是否已经注册
     * @access public 
     * @param   
     * @return   
     */
    public function checkEmail($email){
        $has_exists = M('Teacher')->where(array('email'=>$email))->find();
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
            $this->forgetPass($email, 'teacher');
        }else{
            $this->display();
        }
        
    }

        
     

        
    
}