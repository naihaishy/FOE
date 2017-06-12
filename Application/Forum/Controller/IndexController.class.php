<?php
namespace Forum\Controller;
use Think\Controller;
class IndexController extends Controller {




    /**
     * 论坛首页 
     * 主版块 + 置顶/精华帖共10篇 + 最新帖5篇 
     * @access public
     * @param 
     * @return 
     */
    public function index(){
        $map = array('status'=>'open','pid'=>0);
        $forums = M('Forum')->where($map)->select();
        $stick_posts    = $this->getStickPosts(0,   get_option('forum_index_stickpost_num') );
        $essence_posts  = $this->getEssencePosts(0, get_option('forum_index_essencepost_num') );
        $latest_posts   = $this->getLatestPosts(0,  get_option('forum_index_latestpost_num') );

        $assign = array(
                'forums'        => $forums,
                'stick_posts'   =>$stick_posts,
                'essence_posts' =>$essence_posts,
                'latest_posts'  =>$latest_posts,
            );
        
        $this->assign($assign);
        $this->display();
    }


    /**
     * 子论坛
     * @access public
     * @param 
     * @return 
     */
    public function sub($id){
        if(!$this->checkForum($id)) $this->error('对不起，不存在该板块','',1); //板块合法性检验
        
        $pforum = M('Forum')->find($id);

        $map    = array('status'=>'open','pid'=>$id);
        $forums = M('Forum')->where($map)->select(); //该主版块下所有子版块

        $stick_posts    = $this->getStickPosts($id,   get_option('forum_sub_stickpost_num'));
        $essence_posts  = $this->getEssencePosts($id, get_option('forum_sub_essencepost_num'));
        $latest_posts   = $this->getLatestPosts($id,  get_option('forum_sub_latestpost_num'));

        $assign = array(
                'pforum'        =>$pforum,
                'forums'        => $forums,
                'stick_posts'   =>$stick_posts,
                'essence_posts' =>$essence_posts,
                'latest_posts'  =>$latest_posts,
            );
        //dump($stick_posts);die;
        $this->assign($assign);
        $this->display();
    }

    /**  
    *  子板块下帖子列表  
    * @access public
    * @param int  子版块id
    * @return  int 
    */
    public function lists($id){

        if(!$this->checkForum($id)) $this->error('对不起，不存在该板块','',1); //板块合法性检验

        $subforum   = M('Forum')->find($id);//该子版块信息
        $subforum['total_posts_count'] = M('ForumPost') ->where('forum_id='.$id)->count();//改子版块下总帖子数
        

        $stick_posts    = $this->getStickPosts($id,  get_option('forum_lists_stickpost_num')); //置顶帖5篇
        $essence_posts  = $this->getEssencePosts($id,get_option('forum_lists_essencepost_num')); //精华帖5篇

        /*-----分页显示-----*/

        $model = D('ForumPost');
        $page = A('Common/Pages')->getShowPage($model, array('forum_id'=>$id),  get_option('forum_lists_latestpost_num'));
        $show = $page->show();
    
        $posts   =  M('ForumPost')  ->where(array('forum_id'=>$id))
                                    ->limit($page->firstRow,$page->listRows)
                                    ->order('created_time desc')
                                    ->select(); //该子版块下的 最新帖



        $assign = array(
                'subforum'      => $subforum,
                'stick_posts'   => $stick_posts,
                'essence_posts' => $essence_posts,
                'latest_posts'  => $posts,
                'show'          => $show
            );

        $this->assign($assign);
        $this->display();
    }


    /**  
    *  标签  
    * @access public
    * @param string  tag
    * @return  int 
    */
    public function tags($tag){
        if(empty($tag)) $this->redirect(U('Forum/Index/index'));
        $model = D('ForumPost');

        /*-----标签处理-----*/
        $posts_tmp  = $model->field('id,tags')->select();
        $post_ids   = '';
        $tags = array();
        foreach ($posts_tmp as $key => &$value) {
            $value['tag_flag'] = '';
            if($value['tags']){
                $tags = array_unique(array_merge($tags, explode(',', $value['tags'])));
                $arr = explode(',', $value['tags']);
                if(in_array($tag, $arr)){
                    $value['tag_flag'] = 'yes';
                }
            }
            if($value['tag_flag'] == 'yes'){
                $post_ids  = $post_ids .','. $value['id'];
            }
        }
        //dump($tags);die;
        if(!in_array($tag, $tags)) $this->error('不存在该标签');
        $arrid = array_filter(explode(',', $post_ids));//该tag下 所有文章id (array)
        $map = array('id'=>array('in', $arrid) );

        /*-----分页显示-----*/
        $page   =   A('Common/Pages')->getShowPage($model, $map);
        $show = $page->show();


        $posts   =  $model  ->where($map)
                            ->limit($page->firstRow,$page->listRows)
                            ->order('created_time desc')
                            ->select(); //该子版块下的 最新帖

        $assign = array(
                'posts' => $posts,
                'tag'   => $tag,
                'show'  => $show
            );
        //dump($posts);die;
        $this->assign($assign);
        $this->display();

    }






    /**
     * 获取置顶帖
     * @access private
     * @param int forum_id 板块id
     * @param int num 数目
     * @return array
     */
    private function getStickPosts($forum_id, $num){        
    
        return $this->getPosts($forum_id, $num, 'stick');
    }


    /**
     * 获取精华帖  
     * @access private
     * @param int forum_id 板块id
     * @param int num 数目
     * @return array
     */
    private function getEssencePosts($forum_id, $num){

        return $this->getPosts($forum_id, $num, 'essence');
    }


    /**
     * 获取最新帖(正常帖)
     * @access private
     * @param int forum_id 板块id
     * @param int num 数目
     * @return array
     */
    private function getLatestPosts($forum_id, $num){

        return $this->getPosts($forum_id, $num, 'normal');
    }


    /**
     * 获取帖子 
     * 类型 数目 板块 
     * @access private
     * @param int forum_id 板块id
     * @param int num 数目
     * @param string type 类型 stick essencs normal
     * @return array
     */
    private function getPosts($forum_id, $num, $type){

        if($forum_id == 0){

            $map = array('status'=>$type); //不指定板块的相应类型帖子 [置顶|精华|最新]
            $posts = M('ForumPost')->where($map)->limit($num)->order('created_time desc')->select();

        }else{

            if( M('Forum')->find($forum_id)['pid']==0 ){
                //该板块为主版块 查询其所有子版块的 相应类型帖子 [置顶|精华|最新]
                $map = array('t1.status'=>$type, 't3.id'=>$forum_id);
                $posts = M('ForumPost')->alias('t1')
                                        ->field('t1.*')
                                        ->join('left join tp_forum as t2 on t1.forum_id =t2.id')
                                        ->join('left join tp_forum as t3 on t2.pid = t3.id')
                                        ->where($map)->limit($num)->order('created_time desc')->select();
            }else{
                //该板块为子版块 直接查询其相应类型帖子 [置顶|精华|最新]
                $map = array('status'=>$type, 'forum_id'=>$forum_id);
                $posts = M('ForumPost')->where($map)->limit($num)->order('created_time desc')->select();
            }
        }

        return $posts;
    }



    /**  
    * 检验板块合法性
    * @access private
    * @param int forum_id 板块id 
    * @return  boolean  
    */
    private function checkForum($forum_id){
        if(empty($forum_id) || !is_numeric($forum_id)) return false;
        $is_exists = M('Forum')->where("status='open'")->find($forum_id);
        if($is_exists){
            return true;
        }else{
          return false;  
        } 
    }








    



}