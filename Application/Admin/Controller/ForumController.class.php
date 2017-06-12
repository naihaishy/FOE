<?php
namespace Admin\Controller;
use Think\Controller;

class ForumController extends Controller{

    /**  
     * 初始化
     * @access public
     * @param  
     * @return
     */
    public function _initialize(){
        A('Common')->_initialize();
    }
    
    /**
     * 门户论坛管理
     * @access public 
     *
     */
    public function home(){

        $data       =   D('Forum')->getTreeData('tree','id','title');
        $setting    =   $this->getForumSetting('home');
        $this->assign('data', $data);
        $this->assign('setting', $setting);
        $this->display();
    }


    /**
     * 教师论坛管理
     * @access public 
     *
     */
    public function teacher(){
        $data       =   D('TeacherForum')->getTreeData('tree','id','title');
        $setting    =   $this->getForumSetting('teacher');
        $this->assign('data', $data);
        $this->assign('setting', $setting);
        $this->display();
    }



    /**
     * 添加门户板块
     * @access public 
     */
    public function addHomeForum(){

        $this->add('home');
    }
    /**
     * 添加教师板块
     * @access public 
     */
    public function addTeacherForum(){
        $this->add('teacher');
    }



    
    /**
     * 更新门户板块
     * @access public 
     */
    public function editHomeForum(){
        $this->edit('home');
    }
    /**
     * 更新教师板块
     * @access public 
     */
    public function editTeacherForum(){
        $this->edit('teacher');
    }


    


    /**
     * 添加板块
     * @access private
     * @param type home|teacher 
     */
    private function add($type){
        if(empty($type)) $this->error('访问错误');
        if(IS_POST){
            $post = I('post.');
            //自动验证提交数据
            if($type=='home'){

                $rules = array(
                     array('title','require','标题必须有！'),
                     array('icon','require','icon图标必须有！'),
                     array('slug','require','别名必须有!'),
                );
                $model = D('Forum');

            }elseif($type=='teacher'){

                $rules = array(
                     array('title','require','标题必须有！'),
                     array('slug','require','别名必须有!'),
                );
                $model = D('TeacherForum');
            }
            if(!$model->validate($rules)->create())  $this->error($model->getError(),'',1);

            $result = $model->addData($post);
            $result ? $this->success('添加成功','',1) : $this->error('添加失败','',1);
        }
    }


    /**
     * 更新门户板块
     * @access private 
     * @param type home|teacher 
     */
    private function edit($type){
        if(empty($type)) $this->error('访问错误');
        if(IS_POST){
            $post   =   I('post.');
            $map    =   array('id'=>$post['id']);
            if($type == 'teacher')  $model = D('TeacherForum');
            elseif($type == 'home') $model = D('Forum');
            $result =   $model->editData($map,$post);
            $result ===false ?  $this->error('修改失败','',1):$this->success('修改成功','',1);
        }
    }




    /**
     * 论坛设置板块
     * @access public 
     */
    public function setting(){
        if(IS_POST){
            $post   =   I('post.');
            $model  =   D('Setting'); 
            foreach ($post as $key => $value) {
                # code...
                $map = array('name'=>$key);
                $model->where($map)->setField('value', $value);
            }
            $this->success('修改成功','',1);
        }
    }

    /**
     * 获取论坛相关设置
     * @access public 
     */
    private function getForumSetting($type){
        $group_settings = '';
        if($type=='home'){
            $group_settings =  get_group_options('forum');
        }elseif($type=='teacher'){
            $group_settings =  get_group_options('teacherforum');
        } 
        
        foreach ($group_settings as $key => $value) {
            $setting[$value['name']] = $value['value'];
        }
        return $setting;
    }   







 

}