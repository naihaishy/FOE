<?php
namespace University\Controller;
use Think\Controller;
class IndexController extends Controller {
    


    public function index() {
        # code...
        $universities = M('University')->select();
        foreach ($universities as $key => &$value) {
            $value['course_count'] = M('Course')->where('university_id='.$value['id'])->count();
        }
        $this->assign('universities',$universities);
        //dump($universities);die;
        $this->display();
    }
}