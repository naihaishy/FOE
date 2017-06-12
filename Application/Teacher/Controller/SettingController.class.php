<?php
namespace Teacher\Controller;
use Think\Controller;

class SettingController extends Controller{


    /**
     * 常规设置
     * Ajax
     */
    public function general(){
        if(IS_POST){
            $post = I('post.');
            $tid = session('uid');
            $layout = array(
                    'collapse'    =>  $post['collapse'],
                    'fixedsidebar'=>$post['fixedsidebar'],
                    'fixednavbar'=>$post['fixednavbar'],
                    'boxedlayout'=>$post['boxedlayout'],
                    'fixedfooter'=>$post['fixedfooter'],
                );
            $data = array(
                'layout'    => json_encode($layout),
                'theme'     => $post['skinconfig'],
                );
            $map = array('teacher_id'=>$tid );
            if( $this->checkExists($tid ) ){
                $result = M('TeacherSetting')->where($map)->save($data);
            }else{
                $data['teacher_id'] = $tid;
                $result = M('TeacherSetting')->where($map)->add($data);
            }
            
            $this->ajaxReturn($result);
        }else{
            $this->display();
        }
        
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
            $tid  = session('uid');
            $rules = array(
                 array('stem','require','题干必须有！'),
                 array('answer','require','答案必须有！'),
                 array('password_new2','password_new1','两次密码不一致',0,'confirm'), // 验证确认密码是否和密码一致
                );
            $model = D('Teacher');
            if(!$model->validate($rules)->create())  $this->error($model->getError(),'',1);
            $check = $model->field('password,salt')->find($tid );

            if($check['password'] === md5($post['password_old'] . $check['salt']) ){
                $salt               =   md5(mt_rand(rand(1,9),rand(88,999)));//salt 生成
                $data['salt']       =   $salt;
                $data['password']   =   md5($post['password_new1'].$salt);
                $result             =   $model->where('id='.$tid)->save($data);
                $result ? $this->success('保存成功','',1):$this->error('保存失败','',2);
            }else{
                $this->error('密码错误','',2);
            }
            

        }else{
            $account = M('Teacher')->field('password', true)->find(session('uid'));
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
            
            $tid  = session('uid');

            $post['description'] = htmlspecialchars_decode($post['description']);

            //头像 
            $filetype   =   array('jpg', 'gif', 'png', 'jpeg', 'bmp');//上传为图像

            if($_FILES['avatar']['error']==0){
                $avatar_id  =   D('Files/Files')->upload($_FILES['avatar'], 'user', $filetype);
                $post['avatar']   = M('Files')->field('uri')->find($avatar_id)['uri'];
                session('avatar', $post['avatar']);
            }
            $result     =   M('Teacher')->where('id='.$tid)->save($post);
            $result ? $this->success('更新成功','',1):$this->error('更新失败','',2);
        }else{
            $profile = M('Teacher')->field('password', true)->find(session('uid'));
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
            $post = I('post.');
            $tid = session('uid');
            $map = array('teacher_id'=>$tid );
            $data = array($field=> json_encode($post),'teacher_id'=>$uid  );

            if($this->checkExists($tid)){
                $result = M('TeacherSetting')->where($map)->save($data);
            }else{
                $result = M('TeacherSetting')->where($map)->add($data);
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
     * @param  int tid  教师id
     * @param  array field  
     * @return    
     */
    private function checkExists($tid,$field=''){
        $map = array('teacher_id'=>$tid );
        $has_exist = M('TeacherSetting')->where($map)->field($field)->find();
        if($has_exist) return true;else return false;
    }



    /**
     * 获取某项设置
     * @access private
     * @param  string field  某个字段
     * @return json  setting
     */
    private function getConfig($field=''){
        $tid = session('uid');
        $map = array('teacher_id'=>$tid );
        $setting  = M('TeacherSetting')->field($field)->where($map)->find()[$field];
        return $setting;
    }


    




}