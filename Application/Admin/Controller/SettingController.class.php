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
        if(IS_POST){
            $post  = I('post.');
            foreach ($post as $key => $value) {
                $map = array('name'=> $key);
                $data = array('name'=>$key,'value'=>$value,'group'=>'system');
                $has_exists = M('Setting')->where($map)->find();
                if($has_exists){
                    M('Setting')->where($map)->save($data);
                }else{
                    M('Setting')->add($data);
                }
            }

            $this->setConfigFile($post);
            $this->success('更新成功');
            
        }else{
            //展示模板
            $data_arr = get_group_options('system');
            foreach ($data_arr as $key => $value) {
                $data[$value['name']] = $value['value'];
            }
            //dump($data );die;
            $this->assign('data', $data);
            $this->display('systems');
        }
        
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
                foreach ($post as $key => $value) {
                    $map    = array('name'=> $key);
                    $data   = array('name'=>$key,'value'=>$value,'group'=>'email');
                    $has_exists = M('Setting')->where($map)->find();
                    if($has_exists){
                        M('Setting')->where($map)->save($data);
                    }else{
                        M('Setting')->add($data);
                    }
                }

            $this->setConfigFile($post);
            $this->success('更新成功', '',2);

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

    /**  
     * 配置处理
     * @access private
     * @param  
     * @return
     */
    private function setConfigFile($data){

        $str = file_get_contents('./Application/Common/Conf/setting.php'); //原有配置

        if( $str==false || empty($str) ){
            //初始化该文件
            #init code 
        }else{
            foreach ($data as $key => $value) {
                //查找是否存在配置项
                if(stripos($str, $key)){
                    //已经存在该项配置 更新
                    $item   = "'".strtoupper($key)."'=>'".$value."',"; 
                    $start  = stripos($str, $key);
                    $config = substr_replace($str, $item, $start-1, 60);
                }else{
                    //不存在该配置项 初始化
                    #init item code 
                }
            }
        }

        file_put_contents('./Application/Common/Conf/setting.php', $config);

    }

    


     
    
    
 
    
    
}