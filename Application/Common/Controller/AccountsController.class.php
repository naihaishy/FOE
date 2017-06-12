<?php
namespace Common\Controller;
use Think\Controller;


/**
 * 账号处理相关通用处理类 
 */
class AccountsController extends Controller {


    /**  
     * 账号退出
     * @access public
     * @param   
     * @return   
     */
    public function logout(){
        //清除session
        session(null);
        $this->success('退出成功',U('Home/Index/index'),2);
    }

    /**  
     * 登录验证
     * @access public
     * @param   redirect 登录成功后跳转跳转页面
     * @return   
     */
    public function checkAccountLogin($post, $role, $redirect="U('Home/Index/index')"){

        switch ($role) {
            case 'student':
                $model = M('Stu');
                $para = array('rid'=>3);
                break;
            case 'teacher':
                $model = M('Teacher');
                $para = array('rid'=>2);
                break;
            case 'admin':
                $model = M('User');
                $para = array('rid'=>1);
                break;
            default:
                # code...
                break;
        }

        //验证验证码
        $verify =   new \Think\Verify();
        $result =   $verify->check($post['captcha']);
        if(!$result) $this->error('验证码错误','',2);//验证码错误
 
        unset($post['captcha']);//删除验证码
        
        $salt   =   $model->field('salt')->where(array('email'=>$post['email']))->find()['salt'];

        $post['password']  =   md5($post['password'].$salt);
        $data   =   $model->where($post)->find();
        //判断是否存在用户
        if($data){
            //存于session 跳转到前台首页
            session('uid', $data['id']);
            session('uname', $data['username']);
            session('rid', $para['rid']);
            session('avatar', $data['avatar']);
            session('last_login_time', $data['last_login_time']);
            session('last_login_ip', $data['last_login_ip']);
            //更新数据库中last_login_time信息
            $update = array(
                    'last_login_time'   =>  time(),
                    'last_login_ip'     =>  get_client_ip(),
                    'session_id'        =>  session_id(),
                );
            $model->where(array('id'=>$data['id']))->save($update);
            A('Common/Messages')->send('account', 'login', '', session('uid'), session('rid') );//消息机制
            $this->success('登录成功', $redirect, 2);
        }else{
            $this->error('用户名或密码错误','',2);
        }
        
    }

    /**  
     * 账户注册
     * @access public
     * @param   redirect 注册成功后跳转页面
     * @return   
     */
    public function accountSignUp($post, $role, $redirect="U('Home/Index/index')"){

        switch ($role) {
            case 'student':
                $model = M('Stu');
                $para = array('rid'=>3);
                break;
            case 'teacher':
                $model = M('Teacher');
                $para = array('rid'=>2);
                break;
            case 'admin':
                $model = M('User');
                $para = array('rid'=>1);
                break;
            default:
                # code...
                break;
        }


        $rules  =   array(
             array('username','require','用户名不得为空'),
             array('username','','帐号名称已经存在！',0,'unique',1), // 在新增的时候验证name字段是否唯一
             array('email','require','电子邮箱不得为空'),
             array('email','','该邮箱已经注册',0,'unique',1), // 在新增的时候验证name字段是否唯一
             array('tel','require','手机必须有！'),
             array('tel','','该手机已经注册',0,'unique',1), // 在新增的时候验证name字段是否唯一
             array('password','require','密码必须有！'),
             array('password2','password','两次密码不一致',0,'confirm'), // 验证确认密码是否和密码一致
            );
        if(!$model->validate($rules)->create())  $this->error($model->getError(),'',1);

        $verify_code    =   $post['verify_code'];//短信验证码
        $bb = $this->checkSms($verify_code);//返回布尔值
        if(!$bb)   $this->error('短信验证码错误','',1);
    
        
        //增加信息  
        
        $salt = md5(mt_rand(rand(1,9),rand(88,999))); //salt 生成
        $post['salt']          =   $salt;
        $post['password']      =   md5($post['password'].$salt);
        $post['registered']    =   time();

        $result = $model->add($post);
        $id = $result;//add返回新增记录的主键id
        if($result ){
            //注册成功
            $email_result = $this->sendAccountVerify($id, $role);//发送邮件
            if($email_result['error']==1)  $this->error('恭喜注册成功！ 但账号激活邮件发送失败 请登录后台手动重新验证');//发送失败
            else $this->success('恭喜注册成功！ 账号激活邮件已发送至你的邮箱,请到邮箱查看激活', $redirect, 3);//发送成功 
        }else{
            //注册失败
            $this->error('注册失败','',1);
        }
    }

    /**  
     * 登录验证码
     * @access public
     * @param   
     * @return   
     */
    public function captcha(){
        //配置
        $config=array(
        'fontSize'  =>  25,      // 验证码字体大小(px)
        'useCurve'  =>  true,    // 是否画混淆曲线
        'useNoise'  =>  true,    // 是否添加杂点  
        'length'    =>  4,       // 验证码位数
        'fontttf'   =>  '4.ttf', // 验证码字体，不设置随机获取
        );
        //实例化验证码类
        $verify=new \Think\Verify($config);
        //输出验证码
        $verify->entry();
    }



    /**  
     * 发送账号激活邮件
     * @access public
     * @param   role
     * @return   array
     */ 
    public function sendAccountVerify($id, $role){
 
        switch ($role) {
            case 'student':
                $model = M('Stu');
                $para = array('Module'=>'Home', 'Controller'=>'Index');
                break;
            case 'teacher':
                $model = M('Teacher');
                $para = array('Module'=>'Home', 'Controller'=>'Teacher');
                break;
            case 'admin':
                $model = M('User');
                $para = array('Module'=>'Admin', 'Controller'=>'Public');
                break;
            default:
                # code...
                break;
        }

        if(empty($id) || !is_numeric($id)) return array('error', 1);

        $data = $model->find($id);
        if(empty($data))  $this->error('该用户不存在');
        if($data['enabled']) $this->error('该账号已经激活了');
        
        $username   =   $data['username'];
        $email      =   $data['email'];
        //生成access
        $access_send_time   =   time();//激活码最新发送时间
        $access_token       =   md5($username . $email . $access_send_time);//创建用于激活识别码
        //更新access
        $data['access_send_time']   =   $access_send_time;
        $data['access_token']       =   $access_token;
        $update_res = $model->save($data);
        if(!$update_res) return array('error', 1); //保存失败 停止发送邮件
        
        $link = "http://" . $_SERVER['HTTP_HOST'] . "/index.php/".$para['Module']."/".$para['Controller']."/activation?accessToken=".$access_token;
        
        $send_to    = $data['email'];
        $from_name  = sitesname();
        $subject    = "帐户激活邮件";
        $body       = "恭喜您在我站".sitesname()."注册成功! 点击下面的链接立即激活帐户(或将网址复制到浏览器中打开)\r\n <a href='".$link."' target='_blank'>".$link."</a>";
        
        $result = think_send_mail($send_to, $from_name,$subject, $body);
        return $result;
        
    }



    /**  
     * 验证邮箱 激活账号
     * @access public
     * @param   
     * @return   
     */
    public function activateAccount($accessToken, $role){
         
        switch ($role) {
            case 'student':
                $model = M('Stu');
                $para = array('rid'=>3);
                break;
            case 'teacher':
                $model = M('Teacher');
                $para = array('rid'=>2);
                break;
            case 'admin':
                $model = M('User');
                $para = array('rid'=>1);
                break;
            default:
                # code...
                break;
        }

        $access_token  = $accessToken;
        $nowtime       = time();
        $map = array('enabled'=>0, 'access_token'=>$access_token);
        $res = $model->where($map)->find();
        //不存在该账号
        if(!$res)  $this->error('不存在该账号','',2);
        //该账号已经激活
        if($res['enabled']==1) $this->error('该账号已经激活了','',2);
            
        //存在相应未激活账号
        if($nowtime > $res['access_未激活账号 send_time'] +60*60*24 ) $this->error('您的激活有效期[24小时]已过，请重新发送激活邮件','',2); //24小时有效期
         
        //修改enabled字段
        $update_res = $model->where(array('id' => $res['id']))->setField('enabled', 1);
        //判断修改结果
        $update_res ? $this->success('账号激活成功') :  $this->error('激活失败...请重试...');

        //消息机制
        if($update_res)  A('Common/Messages')->send('account', 'activate', '', $res['id'], $para['rid'] );
 
         
    }




    /**  
     * 注册短信验证码
     * @access public
     * @param   
     * @return   
     */
    public function sendSms($tel, $from){
        if($this->checkTel($tel, $from)) $this->ajaxReturn('该手机号码已经注册');
        $verify_code    = generate_numcode(6);
        $send_time      = time();
        $result = sms_verf_code($tel, $verify_code);

        if($result){
            //发送成功 保存至数据库
            $data   =   array(
                    'verify_code'   =>  $verify_code,
                    'send_time'     =>  $send_time,
                    'token'         =>  md5($tel . $send_time),
                    'tel'           =>  $tel,
                );

            session('sms_token', $data['token']);
            
            $result2 = M('Sms')->add($data); 
            /*while (!$result2) {  //难道一直等待 会不会陷入死循环 ?
                $result2 = M('Sms')->add($data); 
            }*/
            $this->ajaxReturn('发送成功');
        }else{
            $this->ajaxReturn('发送失败');
        }
    }

    /**  
     * 验证短信验证码
     * @access public
     * @param   
     * @return   
     */   
    public  function checkSms($verify_code){
        
        $result = M('Sms')->where(array('token'=>session('sms_token') ))->find();
        if($result && $verify_code == $result['verify_code'] ) return true;//验证成功 
        else return false;
    }



    /**  
     * 验证手机是否已经注册
     * @access public 
     * @param   
     * @return   
     */
    public function checkTel($tel, $from){

        switch ($from) {
            case 'student':
                $model = M('Stu');
                break;
            case 'teacher':
                $model = M('Teacher');
                break;
            case 'admin':
                $model = M('User');
                break;
            default:
                # code...
                break;
        }

        $has_exists = $model->where(array('tel'=>$tel))->find();
        if($has_exists) return true;//已经注册 
        else return false;
    }

   


    /**  
     * 找回密码 email 发送
     * @access public
     * @param   
     * @return   
     */
    public function forgetPass($email, $role){
        switch ($role) {
            case 'student':
                $model = M('Stu');
                $para = array('Module'=>'Home', 'Controller'=>'Index');
                break;
            case 'teacher':
                $model = M('Teacher');
                $para = array('Module'=>'Home', 'Controller'=>'Teacher');
                break;
            case 'admin':
                $model = M('User');
                $para = array('Module'=>'Admin', 'Controller'=>'Public');
                break;
            default:
                # code...
                break;
        }

        $info = $model->where(array('email'=>$email))->find();
        if(!$info) $this->error('不存在该账号');

        $forget_send_time   =   time();
        $forget_token       =   md5($info['username'] . $email . $forget_send_time);

        $data['forget_send_time']   =   $forget_send_time;
        $data['forget_token']       =   $forget_token;
        $update_res = $model->where(array('id'=>$info['id']))->save($data);
        if(!$update_res) $this->error('发送邮件失败'); //保存失败 停止发送邮件
        
        $link = "http://" . $_SERVER['HTTP_HOST'] . "/index.php/".$para['Module']."/".$para['Controller']."/resetPass?Token=".$forget_token;
        
        $send_to    = $email;
        $from_name  = sitesname();
        $subject    = "重置密码";
        $body       = "您将要修改您的用户密码,点击下面的链接进行修改(或将网址复制到浏览器中打开)\r\n <a href='".$link."' target='_blank'>".$link."</a>";
        
        $result = think_send_mail($send_to, $from_name,$subject, $body);
        if($result) $this->success('密码重置邮件已经成功发送至您的邮箱');

    }


    /**  
     * 重置密码
     * @access public
     * @param   
     * @return   
     */
    public function resetPass($Token){
        
        if(MODULE_NAME=='Admin' && CONTROLLER_NAME=='Public'){
            $model = M('User');//管理员
            $para = array('rid'=>1);
        }elseif(MODULE_NAME=='Home' && CONTROLLER_NAME=='Index'){
            $model = M('Stu');//学员
            $para = array('rid'=>3);
        }
        elseif(MODULE_NAME=='Home' && CONTROLLER_NAME=='Teacher'){
            $model = M('Teacher');//教师
            $para = array('rid'=>2);
        }

        $check = $model->where(array('forget_token'=>$Token))->find();
        if(!$check) $this->error('对不起,该Token无效');
        if($check && (time() > $check['forget_send_time'] +60*60*24) ) $this->error('对不起,该Token已经超过24小时的有效期');

        if (IS_POST) {

            $post = I('post.');
            $rules  =   array(
                 array('password','require','密码必须有！'),
                 array('password2','password','两次密码不一致',0,'confirm'), // 验证确认密码是否和密码一致
                );
            if(!$model->validate($rules)->create())  $this->error($model->getError(),'',2);

            $salt = md5(mt_rand(rand(1,9),rand(88,999))); //salt 生成
            $data   =   array(
                    'salt'      =>  $salt,
                    'password'  =>  md5($post['password'].$salt),
                );
            $result  = $model->where(array('email'=>$check['email']))->save($data);
            if($result){
                $this->success('密码修改成功', U(MODULE_NAME."/".CONTROLLER_NAME."/login"), 2);
                //消息机制
                A('Common/Messages')->send('account', 'changepassword', '', $check['id'], $para['rid'] );
            } 

        }else{
            $this->assign('token', $Token);
            $this->display('reset');
        }
        

    }




}