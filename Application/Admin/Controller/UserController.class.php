<?php
//1.声明命名空间
namespace Admin\Controller;
//2.引入父类控制器
use Think\Controller;
//3.定义控制器并且继承父类

/**
 * 用户管理
 * @access public 
 */
class UserController extends CommonController{

    /**
     * 显示
     * @access public 
     * @param
     * @return 
     */
    public function index(){
        //实例化模型
        $model= M('User');
        //联表查询
        $data = $model->select();
        //dump($data);die;                                          
        $this->assign('data',$data);
        $this->display();
        
    }


    /**
     * 管理人员删除
     * @access public 
     * @param
     * @return 
     */
    public function deleteUser($id){
        $this->checkUser($id);//用户检验 
        $result =   M('User')->delete($id);
        $result ? $this->success('删除成功'):$this->error('删除失败');
    }

    /**
     * 管理人员禁用
     * @access public 
     * @param
     * @return 
     */
    public function banUser($id){
        $this->checkUser($id);//用户检验 
        $result = M('User')->where(array('id'=>$id))->setField('locked','1');
        $result === false ? $this->error('禁用账号失败'): $this->success('禁用账号成功');
        
    }
    

    /**
     * 管理
     * @access public 
     * @param
     * @return 
     */
    public function manage($id){
        $this->checkUser($id);//用户检验 
        if(IS_POST){
            $post = I('post.');
            $result = M('User')->where(array('id'=>$id))->save($post);
            $result ? $this->success('更新成功'):$this->error('更新失败');
        }else{
            $data = M('User')->find($id);
            $this->assign('data',$data);
            $this->display('manage'); 
        }
        
    }
    
    //发送验证邮箱信件
    public function sendEmailVerify($id){
        //判断请求类型
        if(IS_GET && I('get.id') ){
            
            $id =I('get.id');//请求用户id
            
            //查询数据
            
            $data = M('User')->find($id);
            if(empty($data)){
                $this->error('该用户不存在');exit;
            }
            if($data['enabled']){
                $this->error('该账号已经激活了');exit;
            }
            $username=$data['username'];
            $email  =   $data['email'];
            
            $access_send_time =time();//激活码最新发送时间
            $access_token = md5($username . $email . $access_send_time);//创建用于激活识别码
            
            $data['access_send_time'] =$access_send_time ;
            $data['access_token'] =$access_token;
            //保存数据
            $update_res = M('User')->save($data);
            if(!$update_res){
                //保存失败 停止发送邮件
                die;
            }
            
            //$link = "http://tp3.zhf.com/index.php/Admin/Public/activation?accessToken=".$access_token;
            
            $link = "http://" . $_SERVER['HTTP_HOST'] . "/index.php/Admin/Public/activation?accessToken=".$access_token;
            
            $to=$data['email'];
            $body="点击下面的链接立即激活帐户(或将网址复制到浏览器中打开)\r\n".$link;
            $result = think_send_mail($to, 'Naihai','帐户激活邮件', $body);
            //判断发送结果
            if($result['error']==1){
                //发送失败
                $this->error('邮件发送失败');exit;
            }else{
                //发送成功
                $this->success('邮件发送成功,请到邮箱查看激活',U('User/manage'),3);
            }
        }
        
    }
    
    
    //用户注册时发送激活邮件
    public function sendCountVerify($id){
 
            //查询数据
            
            $data = M('User')->find($id);
            if(empty($data)){
                $this->error('该用户不存在');exit;
            }
            if($data['enabled']){
                $this->error('该账号已经激活了');exit;
            }
            
            $username=$data['username'];
            $email  =   $data['email'];
            
            $access_send_time =time();//激活码最新发送时间
            $access_token = md5($username . $email . $access_send_time);//创建用于激活识别码
            
            $data['access_send_time'] =$access_send_time ;
            $data['access_token'] =$access_token;
            //保存数据
            $update_res = M('User')->save($data);
            if(!$update_res){
                //保存失败 停止发送邮件
                die;
            }
             
            $link = "http://" . $_SERVER['HTTP_HOST'] . "/index.php/Admin/Public/activation?accessToken=".$access_token;
            
            $send_to = $data['email'];
            $from_name = sitesname();
            $subject = "帐户激活邮件";
            $body="恭喜您在我站".sitesname()."注册成功点击下面的链接立即激活帐户(或将网址复制到浏览器中打开)\r\n".$link;
            
            $result = think_send_mail($send_to, $from_name,$subject, $body);
            //判断发送结果
            if($result['error']==1){
                //发送失败
                $this->error('恭喜注册成功！ 但账号激活邮件发送失败 请登录后台手动重新验证');exit;
            }else{
                //发送成功
                $this->success('恭喜注册成功！ 账号激活邮件已发送至你的邮箱,请到邮箱查看激活',U('Public/login'),3);
            }
        
    }
    
    
    //显示个人资料
    public function profile(){
        //查询信息
        $data = M('User')->where('id='. session('uid'))->find();
        //dump($data);
        //变量分配
        $this->assign('data',$data);
        //显示模板
        $this->display('profile');
    }
    
    
    //更新个人资料
    public function updatepro(){
        
        if(IS_POST ){
            $post=I('post.');
            $post['id']=session('uid');
            $result = M('User')->save($post);
            if($result){
                $this->success('更新成功');
            }else{
                $this->error('更新失败');
            }
            
        }else{
            $this->profile();
        }
        
        //显示模板
        
    }

    /**
     * 用户合法性检验
     * @access private 
     * @param
     * @return 
     */
    private function checkUser($id){
        if(empty($id)|| !is_numeric($id) || !M('User')->find($id)) $this->error('不存在该用户');  
    }
    
    
    
}

