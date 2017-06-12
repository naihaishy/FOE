<?php
namespace Home\Controller;
use Think\Controller;
class NewsController extends Controller{


    public function index(){

        $model = M('News');
        $page = A('Common/Pages')->getShowPage($model);
        $show = $page->show();
        $map = array('status'=>'publish');
        $news   = M('News')->alias('t1')->field('t1.*,t2.username as author_name,t3.name as category_name')
                                        ->join('left join tp_user as t2 on t1.author=t2.id')
                                        ->join('left join tp_news_category as t3 on t1.category_id =t3.id')
                                        ->order('post_time desc')
                                        ->where($map)
                                        ->limit($page->firstRow,$page->listRows)
                                        ->select();
        $assign =   array(
                'news'  =>  $news,
                'show'  =>  $show,
            );  
        $this->assign($assign);
        $this->display();
    }

    public function view($id){
        if(empty($id) || !is_numeric($id) || !M('News')->find($id)) $this->error('不存在该新闻');
        $map    = array('t1.id'=>$id, 't1.status'=>'publish');
        $news   = M('News')->alias('t1')->field('t1.*,t2.username as author_name,t3.name as category_name')
                                        ->join('left join tp_user as t2 on t1.author=t2.id')
                                        ->join('left join tp_news_category as t3 on t1.category_id =t3.id')
                                        ->where($map)
                                        ->find();
        $this->assign('news', $news);
        $this->display();
    }


}