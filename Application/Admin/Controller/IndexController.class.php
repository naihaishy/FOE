<?php

//1.定义命名空间
namespace Admin\Controller;
//2.引入父类控制器
use Think\Controller;

//3.定义控制器并且继承父类
class IndexController extends CommonController{
    
 
    /**  
     * 仪表盘
     * @access public
     * @param   
     * @return   
     */
    public function Index(){
        //展示模板
        $count['student'] =M('Stu')->count();
        $count['teacher'] =M('Teacher')->count();
        $count['course'] =M('Course')->count();
        $count['university'] =M('University')->count();
        $count['forumpost'] =M('ForumPost')->count();
        $count['tforumpost'] =M('TeacherForumPost')->count();
        $count['news'] =M('News')->count();

        $pre = C('DB_PREFIX');

        $rules = M('AuthGroupAccess')->alias('t1')
                                    ->join("left join {$pre}auth_group as t2 on t2.id = t1.group_id")
                                    ->where(array('t1.uid'=>session('uid')))
                                    ->getField('rules');

        $allow  = M('AuthRule')->field('name')->where(array('id'=>array('in', $rules)))->select();
        $watch = array(
                'student'   =>'no',
                'teacher'   =>'no',
                'course'    =>'no',
                'university'=>'no',
                'forum'     =>'no',
                'live'      =>'no',
                'news'      =>'no',
            );
        foreach ($allow as $key => $value) {
            foreach ($watch as $key2 => $value2) {
                if(stripos($value['name'], $key2) > 5 && stripos($value['name'], $key2) < 8  ){
                    $watch[$key2] = 'yes';
                }
            }
            
        }
        //dump($allow);die;
        $this->assign('count', $count);
        $this->assign('watch', $watch);
        $this->display();
    }
    
 
    /**  
     * 权限规则列表
     * @access public
     * @param   
     * @return   
     */
    public function Home(){
        //展示模板
        $this->display();
    }
    
    
    
}