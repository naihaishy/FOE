<?php
namespace Home\Controller;
use Think\Controller;

class SettingController extends Controller{


    /**
     * 常规设置
     * Ajax
     */
    public function general(){
 
        
    }


    /**
     * 邮件设置
     * @access public
     * @param    
     * @return    
     */
    public function email(){
        $this->setting('email');
    }

    /**
     * 隐私设置
     * @access public
     * @param    
     * @return    
     */
    public function privacy(){
        $this->setting('privacy');
    }

    /**
     * 通知设置
     * @access public
     * @param    
     * @return    
     */
    public function inform(){
        $this->setting('inform');
    }

    /**
     * 账户设置
     * @access public
     * @param    
     * @return    
     */
    public function account(){
        if(IS_POST){
            $post = I('post.');
            $uid  = session('uid');

            //数据验证
            $rules = array(
                 array('stem','require','题干必须有！'),
                 array('answer','require','答案必须有！'),
                 array('password_new2','password_new1','两次密码不一致',0,'confirm'), // 验证确认密码是否和密码一致
                );
            $model = D('Stu');
            if(!$model->validate($rules)->create())  $this->error($model->getError(),'',1);

            $check = $model->field('user_pass,salt')->find($uid);

            if($check['user_pass'] === md5($post['password_old'] . $check['salt']) ){
                $salt               =   md5(mt_rand(rand(1,9),rand(88,999)));//salt 生成
                $data['salt']       =   $salt;
                $data['user_pass']   =   md5($post['password_new1'].$salt);
                $result             =   $model->where(array('id'=>$uid))->save($data);
                $result ? $this->success('保存成功','',1):$this->error('保存失败','',2);
            }else{
                $this->error('密码错误','',2);
            }
            

        }else{
            $account = M('Stu')->field('user_pass', true)->find(session('uid'));
            $this->assign('account', $account);
            $this->display();
        }
    }

    /**
     * 个人资料
     * @access public
     * @param    
     * @return    
     */
    public function profile(){
        if(IS_POST){
            $post = I('post.');
            
            $uid  = session('uid');

            $post['description'] = htmlspecialchars_decode($post['description']);

            //头像 
            $filetype   =   array('jpg', 'gif', 'png', 'jpeg', 'bmp');//上传为图像

            if($_FILES['avatar']['error']==0){
                $avatar_id  =   D('Files/Files')->upload($_FILES['avatar'], 'user', $filetype);
                $post['avatar']   = M('Files')->field('uri')->find($avatar_id)['uri'];
                session('avatar', $post['avatar']);
            }
            $result     =   M('Stu')->where('id='.$uid)->save($post);
            $result ? $this->success('更新成功','',1):$this->error('更新失败','',2);
        }else{
            $profile = M('Stu')->field('password', true)->find(session('uid'));
            $this->assign('profile', $profile);
            //dump($profile);die;
            $this->display();
        }
    }





    /**
     * 设置 通用处理
     * @access public
     * @param  string field  : email privacy 
     * @return    
     */
    private function setting($field){

        if(empty($field)) $this->error('非法访问','',2);//检查参数
    
        if(IS_POST){
            $post   = I('post.');
            $uid    = session('uid');
            $map    = array('user_id'=>$uid );
            $data   = array($field=> json_encode($post),'user_id'=>$uid );

            if($this->checkExists($uid)){
                $result = M('StuSetting')->where($map)->save($data);
            }else{
                $result = M('StuSetting')->where($map)->add($data);
            }
            $result ? $this->success('保存成功','',1):$this->error('保存失败','',2);
        }else{
            $setting    = $this->getConfig($field);  
            if(empty($setting)) $setting = " '' ";      
            $this->assign('setting', $setting);
            //dump($setting);die;
            $this->display();
        }
    }



    /**
     * 检查设置中[某项]是否存在
     * @access private
     * @param  int uid  教师id
     * @param  array field  
     * @return    
     */
    private function checkExists($uid,$field=''){
        $map = array('user_id'=>$uid );
        $has_exist = M('StuSetting')->where($map)->field($field)->find();
        if($has_exist) return true;else return false;
    }



    /**
     * 获取某项设置
     * @access private
     * @param  string field  某个字段
     * @return json  setting
     */
    private function getConfig($field=''){
        $uid = session('uid');
        $map = array('user_id'=>$uid );
        $setting  = M('StuSetting')->field($field)->where($map)->find()[$field];
        return $setting;
    }


    




}