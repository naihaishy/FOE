<?php
namespace University\Controller;
use Think\Controller;

class CourseController extends Controller {
    


    public function index() {
        # code...
        $id = I('get.id');
        if(empty($id) || !is_numeric($id) || !M('University')->find($id) ) $this->error('不存在该学校','',2);

        
        

        $university = M('University')->find($id);
        $map = array('university_id'=> $id );
        //分页
        $model =  M('Course');
        $page = A('Common/Pages')->getShowPage($model, $map);
        $show = $page->show();

        $courses = $model->where($map)->limit($page->firstRow,$page->listRows)->select();

        

        $this->assign('university',$university);
        $this->assign('courses',$courses);
        $this->assign('show',$show);
        //dump($universities);die;
        $this->display();
    }
}