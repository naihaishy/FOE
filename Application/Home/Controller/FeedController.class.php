<?php
namespace Home\Controller;
use Think\Controller;
class FeedController extends Controller{


    /**
     * 生成RSS
     */
    public function index(){

        $name = 'FOE';
        $url = 'https://foe.zhfsky.com';
        $desc =  'Course';
        $RSS = new \Org\Util\Rss($name, $url, $desc, '');//站点标题的链接
        
        $map = array('status'=>'published');
        $courses = M('Course')->where($map)->order('id desc')->limit(20)->select();
        //pre($result);die;
        foreach($courses as $list){
            if( empty($list['picture_path']) ) $list['picture_path'] = '/Public/Home/assets/images/portfolio/default.png';
            $RSS->AddItem( $list['title'], 'https://foe.zhfsky.com/index.php/Course/Index/details/id/'.$list['id'], "<img width='520px' src=\"https://foe.zhfsky.com".$list['picture_path']. "\" />" . strip_tags(htmlspecialchars_decode($list['description']) ), date('Y-m-d H:i:s',$list['created_time']) );
        }
        $RSS->Display();
    }


    /**
     * 生成RSS
     */
    public function forum(){

        $name = 'FOE';
        $url = 'https://foe.zhfsky.com';
        $desc =  'Forum';
        $RSS = new \Org\Util\Rss($name, $url, $desc, '');//站点标题的链接
        
        $map = array();
        $courses = M('ForumPost')->where($map)->order('id desc')->limit(20)->select();
        //pre($result);die;
        foreach($courses as $list){
            
            $RSS->AddItem( $list['title'], 'https://foe.zhfsky.com/index.php/Forum/View/index/id/'.$list['id'], strip_tags(htmlspecialchars_decode($list['content']) ), date('Y-m-d H:i:s',$list['created_time']) );
        }
        $RSS->Display();
    }

}