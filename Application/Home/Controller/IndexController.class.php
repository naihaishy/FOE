<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends BaseController {
 
    public function _initialize(){

        //网站设置信息
        $system_config = get_group_options('system');
        $siteinfo  = array();
        foreach ($system_config as $key => $value) {
            $siteinfo[$value['name']] = $value['value']; 
        }
        $this->assign('siteinfo', $siteinfo); 


        if(C('SITE_STATUS')=='close'){
            $data = C('SITE_CLOSE_TEXTS');
            $this->assign('data', $data);
            $this->display('Public/close');die;
        }
    }

    /**  
     * 首页
     * @access public
     */
    public function index(){
        $this->display();
    }

    
    /**  
     * 学员登录
     * @access public
     * @param   
     * @return   
     */
    public function login(){
        $this->display();
    }
        
    
    /**  
     * 学员登录验证
     * @access public
     * @param   
     * @return   
     */
    public function checkLogin(){
        if(!IS_POST) exit();
        $post   =   I('post.');//接收数据
        $this->checkAccountLogin($post, 'student', U('Home/User/index') );
    }
    

 
    
    /**  
     * 学员注册
     * @access public
     * @param   
     * @return   
     */ 
    public function signup(){
        //判断请求类型
        if(IS_POST){
            //处理请求
            $post   =   I('post.');
            $this->accountSignUp($post, 'student', U('Home/Index/login') );
            
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
        $this->activateAccount($accessToken, 'student');
    }
        
   
 

    
    

    /**  
     * 验证邮箱是否已经注册
     * @access public 
     * @param   
     * @return   
     */
    public function checkEmail($email){
        $has_exists = M('Stu')->where(array('email'=>$email))->find();
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
            $this->forgetPass($email, 'student');
        }else{
            $this->display();
        }
        
    }



    
}