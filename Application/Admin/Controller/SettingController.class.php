<?php

//1.定义命名空间
namespace Admin\Controller;
//2.引入父类控制器
use Think\Controller;

//3.定义控制器并且继承父类
class SettingController extends CommonController{
    
     
    public function index(){
        //展示模板
        $this->display();
    }
    
    /**  
     * 系统设置
     * @access public
     * @param  
     * @return
     */
    public function systems(){
        //展示模板
        $this->display('systems');
    }
    
    public function blocks(){
        //展示模板
        $this->display('blocks');
    }


    /**  
     * 邮箱配置
     * @access public
     * @param  
     * @return
     */
    public function email(){

        if(IS_POST){
            $post = I('post.');
            $type = I('get.type');


            //邮件测试
            if( $type=='test'){
                $result   = think_send_mail($post['mail_test_send_to'], $post['mail_test_subject'], $post['email_from_name'], $post['mail_test_content']);  
                //dump($result);die;
                if($result === true){
                    $this->success('发送成功','',2);
                }else{
                    $this->error('发送失败:'.$result, '',3);
                }
            }

            //邮件配置
            if($type=='config'){
                foreach ($post as $k => $v) {
                    M('Setting')->where(array('name'=>$k))->setField('value',$v);
                }

                $email_config =<<<php
//***********************************邮箱设置***********************************************

'THINK_EMAIL' => array(
            'SMTP_HOST'   => '{$post['smtp_host']}', //SMTP服务器
            'SMTP_PORT'   => '{$post['smtp_port']}', //SMTP服务器端口 ssl加密
            'SMTP_USER'   => '{$post['smtp_user']}', //SMTP服务器用户名
            'SMTP_PASS'   => '{$post['smtp_pass']}', //SMTP服务器密码
            'FROM_EMAIL'  => '{$post['from_email']}', //发件人EMAIL
            'FROM_NAME'   => '{$post['from_name']}', //发件人名称
            'REPLY_EMAIL' => '', //回复EMAIL（留空则为发件人EMAIL）
            'REPLY_NAME'  => '', //回复名称（留空则为发件人名称）
        ),

php;


                $str = file_get_contents('./Application/Common/Conf/setting.php'); //原有配置

                if( $str==false || empty($str) ){
                    //初始化该文件
                    $config =<<<php
<?php
return array(
//此配置项为自动生成请勿直接修改；如需改动请在后台网站设置

//***********************************邮箱设置***********************************************

'THINK_EMAIL' => array(
            'SMTP_HOST'   => '{$post['smtp_host']}', //SMTP服务器
            'SMTP_PORT'   => '{$post['smtp_port']}', //SMTP服务器端口 ssl加密
            'SMTP_USER'   => '{$post['smtp_user']}', //SMTP服务器用户名
            'SMTP_PASS'   => '{$post['smtp_pass']}', //SMTP服务器密码
            'FROM_EMAIL'  => '{$post['from_email']}', //发件人EMAIL
            'FROM_NAME'   => '{$post['from_name']}', //发件人名称
            'REPLY_EMAIL' => '', //回复EMAIL（留空则为发件人EMAIL）
            'REPLY_NAME'  => '', //回复名称（留空则为发件人名称）
        ),

);
php;
                }else{

                    if(preg_match("/\'THINK_EMAIL\'/", $str) ){
                        //已结存在该配置 进行更新
                        $config_a = substr($str, 0, stripos($str, 'THINK_EMAIL')-102);
                        $config_b = substr($str, stripos($str, 'THINK_EMAIL')+596, strlen($str)-1 );
                        $config = $config_a . $email_config . $config_b;
                    }else{
                        //不存在该配置 添加配置
                        $config_a = substr($str, 0, strlen($str)-4);
                        $config = $config_a . $email_config ."\r\n);";
                    }
                    
                    
                    
                }
                
            $boolean = file_put_contents('./Application/Common/Conf/setting.php', $config);
            if($boolean) $this->success('更新成功', '',2);
            }
            

        }else{
            $setting = get_group_options('email');
            foreach ($setting as $key => $value) {
                $data[$value['name']] = $value['value'];
            }
            //dump($data);die;
            $this->assign('data', $data);
            $this->display();
        }
        
    }

    


     
    
    
 
    
    
}