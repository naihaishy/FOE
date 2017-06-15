<?php
namespace Teacher\Controller;

use Think\Controller;

class ForumController extends Controller {
    
    
    public function _initialize(){
        if(!is_login()) $this->error('请先登录', U('Home/Teacher/login'), 2);
        if(is_student()  ) $this->error('对不起,你没有权限', '', 2);
        if(is_admin()  ) { 
            # 限制访问其他页面
        }
    }
   /**  
    *  主版块 论坛
    * @access public
    * @param int  
    * @return  int 
    */
    public function index(){
        
        $forum = M('TeacherForum')->where('pid = 0')->select();
        $total_post_count = 0;//总帖子数
        foreach ($forum as $key => &$value) {
            $value['post_count']    = $this->getPostCount($value['id']);
            $value['view_count']    = $this->getViewCount($value['id']);
            $value['reply_count']   = $this->getReplyCount($value['id']);
            $total_post_count       = $total_post_count + $value['post_count'];
        }

        $this->assign('forum', $forum);
        $this->assign('total_post_count', $total_post_count);
        //dump($forum);die;
        $this->display();
    }



    /**  
    *  主版块下的所有子板块 
    * @access public
    * @param int  id 板块id
    * @return  int 
    */
    public function sub($id){

        if(!$this->checkForum($id)) $this->error('不存在该板块','',1);//板块合法性检验

        $forum      = M('TeacherForum')->find($id);//该主版块信息
        $subforums  = M('TeacherForum')->where('pid = '.$id)->select();//该主版块下子版块信息

        $total_post_count = $this->getPostCount($id); //该主版块总帖子数

        foreach ($subforums as $key => &$value) {
            $value['post_count']    = $this->getPostCount($value['id']);
            $value['view_count']    = $this->getViewCount($value['id']);
            $value['reply_count']   = $this->getReplyCount($value['id']);
        }


        $this->assign('forum', $forum);
        $this->assign('subforum', $subforums);
        $this->assign('total_post_count', $total_post_count);
        $this->display();
    }


    /**  
    *  子板块下帖子列表  
    * @access public
    * @param int  子版块id
    * @return  int 
    */
    public function lists($id){

        if(!$this->checkForum($id)) $this->error('不存在该板块','',1);//板块合法性检验

        $subforum   = M('TeacherForum')->find($id);//该子版块信息
        $subforum['total_count'] = M('TeacherForumPost') ->where('forum_id='.$id)->count();



        $stick_posts    = $this->getStickPosts($id,  get_option('teacherforum_lists_stickpost_num')); //置顶帖5篇
        $essence_posts  = $this->getEssencePosts($id,get_option('teacherforum_lists_essencepost_num')); //精华帖5篇

        /*-----分页显示-----*/
        $model = D('TeacherForumPost');
        $count = $model->where('forum_id='.$id)->count();
        $page = new \Think\Page($count, get_option('teacherforum_lists_latestpost_num') );
        $page->rollPage   = 6;
        $page->lastSuffix = false; 
        $page->setConfig('header',' 条记录');
        $page->setConfig('prev','上一页');
        $page->setConfig('next','下一页');
        $page->setConfig('first','首页');
        $page->setConfig('last','末页');
        $page->setConfig('theme','%TOTAL_ROW%  %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页 %FIRST%  %UP_PAGE%   %LINK_PAGE%  %DOWN_PAGE%  %END%');
        $show = $page->show();


        $posts   =  M('TeacherForumPost')->where('forum_id='.$id)
                                        ->limit($page->firstRow,$page->listRows)
                                        ->order('created_time desc')
                                        ->select(); //该子版块下的 最新帖


        $assign = array(
                'subforum'      => $subforum,
                'stick_posts'   => $stick_posts,
                'essence_posts' => $essence_posts,
                'posts'  => $posts,
                'show'          => $show
            );

        $this->assign($assign);
        //dump($posts);die;
        $this->display();
    }


    /**  
    * 发帖
    * 规则: 不允许在主版块下发帖  
    * @access public
    * @param int  
    * @return  int 
    */
    public function post(){
        $pre = C('DB_PREFIX');
        if(IS_POST){
            $post = I('post.');
            //参数验证
            
            $this->checkPostForum($post['forum_id']);//不允许在主版块下发帖
            $rules = array(
                 array('title','require','标题必须有！'),
                 array('forum_id','require','所属板块必须确定！'),
                 array('content','require','内容不得为空!'),
            );
            $model = D('TeacherForumPost');
            if(!$model->validate($rules)->create())  $this->error($model->getError(),'',1);


            $result = $model->addData($post, $_FILES['attachment'] );
            $result ? $this->success('发布成功', U('Teacher/Forum/view/id/'.$result),1): $this->error('发布失败','',1);

        }else{
            $forum =   M('TeacherForum')->alias('t1')
                                        ->field('t1.*,t2.title as ptitle')
                                        ->join("left join {$pre}teacher_forum as t2 on t1.pid =t2.id")
                                        ->where("t1.pid !=0")
                                        ->select();//板块信息 主版块下的子版块  限制用户不得选择主版块 (checkPostForum 再次进行验证过滤)
            //dump($forum);die;
            $this->assign('forum', $forum);
            $this->display();
        }
        
    }

    /**  
    * 帖子内容
    * 不允许在主版块下发帖 
    * @access public
    * @param int  post_id
    * @return  mix 
    */
    public function view($id){

        if(empty($id) || !is_numeric($id))      $this->error('非法访问','',1);
        if(! M('TeacherForumPost')->find($id) ) $this->error('不存在该帖子','',1); //检验帖子id合法性

        $post           =   M('TeacherForumPost')->find($id);
        $author         =   $this->getPostAuthor($post['role_id'], $post['user_id']);//该帖子发布者信息
        $replys         =   $this->getReplys($id);//该帖子下所有回复
        $attachment     =   $this->getAttachment($post['attachment_id']);//附件信息
        $forum_info     =   $this->getForumInfo($id); //该帖子所属板块信息


        M('TeacherForumPost')->where('id='.$id)->setInc('view_count',1); //浏览数+1  暂时这么处理
        $assign         = array(
                'post'      => $post,
                'author'    => $author,
                'replys'    => $replys,
                'attachment'=> $attachment,
                'forum_info'=> $forum_info ,

            );
        //dump($replys );die;
        $this->assign($assign);
        $this->display();
    }


    /**
     * 获取发帖人信息
     * @access private
     * @param rid 用户角色id
     * @param uid 用户 id
     * @return 
     */
    private function getPostAuthor($rid,$uid){
         switch ($rid) {
             case '3':
                 //学生
                 $author = M('Stu')->field('username as name, avatar, description')->find($uid);
                 break;
            case '2':
                 //教师
                 $author = M('Teacher')->field('username as name, avatar, description')->find($uid);
                 break;
            case '1':
                 //管理员
                 $author = M('User')->field('username as name, avatar, description')->find($uid);
                 break;
             
             default:
                 # code...
                 break;
         }
         if(!$author['avatar']) $author['avatar'] = "/Public/Home/assets/images/blogdetails/1.png";
         return $author;
    }


    /**
     * 获取回复内容 及回复者信息
     * @access private
     * @param  
     * @return 
     */
    private function getReplys($post_id){

        $replys = M('TeacherForumReply')->where('post_id='.$post_id)->select(); //该帖子下的所有回复

        foreach ($replys as $key => &$value) {
            //回复者信息
            if($value['role_id']=='1'){
                //该回复来自管理人员
                $info = M('User')->field('truename,avatar')->find($value['user_id']);
                $value['user_name']     = $info['truename'];
                $value['user_avatar']   = $info['avatar'];
                $value['user_role']     = 'admin';

            }elseif($value['role_id']=='2'){
                //该回复来自教师
                $info = M('Teacher')->field('name,avatar')->find($value['user_id']);
                $value['user_name']    = $info['name'];
                $value['user_avatar']  = $info['avatar'];
                $value['user_role']    = 'teacher';
            }elseif($value['role_id']=='3'){
                //该回复来自学生
                $info = M('Stu')->field('user_name,avatar')->find($value['user_id']);
                $value['user_name']    = $info['user_name'];
                $value['user_avatar']  = $info['avatar'];
                $value['user_role']    = 'student';
            }

            if($value['quotes']){
                //该回复引用了其他人的回复  quotes是引用的回复id
                $value['quotes'] = M('TeacherForumReply')->find($value['quotes']);
                if($value['quotes']['role_id']=='1'){
                    //该引用的回复来自管理员
                    $info = M('User')->field('truename,avatar')->find($value['quotes']['user_id']);
                    $value['quotes']['user_name']    = $info['truename'];
                    $value['quotes']['user_avatar']  = $info['avatar'];
                    $value['quotes']['user_role']    = 'admin';

                }elseif($value['quotes']['role_id']=='2'){
                    //该引用的回复来自教师
                    $info = M('Teacher')->field('name,avatar')->find($value['quotes']['user_id']);
                    $value['quotes']['user_name']    = $info['name'];
                    $value['quotes']['user_avatar']  = $info['avatar'];
                    $value['quotes']['user_role']    = 'teacher';

                }elseif($value['quotes']['role_id']=='3'){
                    //该引用的回复来自学生
                    $info = M('Stu')->field('user_name,avatar')->find($value['quotes']['user_id']);
                    $value['quotes']['user_name']    = $info['user_name'];
                    $value['quotes']['user_avatar']  = $info['avatar'];
                    $value['quotes']['user_role']    = 'teacher';

                }

            }
        }


        return $replys;
        
    }

    /**
     * 获取附件信息
     * @access private
     * @param  
     * @return 
     */
    private function getAttachment($attactment_id=''){
        if(empty($attactment_id) || !is_numeric($attactment_id) ) return false;
        return M('Files')->find($attactment_id);
    }

    /**
     * 获取所属板块信息
     * @access private
     * @param  int post_id 帖子id
     * @return 
     */
    private function getForumInfo($post_id){
        $pre = C('DB_PREFIX');
        $forum_id   = M('TeacherForumPost')->field('forum_id')->find($post_id)['forum_id'];
        $forum_info = M('TeacherForum')->alias('t1')
                                    ->field('t1.*,t2.title as ptitle')
                                    ->join("left join {$pre}teacher_forum as t2 on t1.pid =t2.id")
                                    ->where('t1.id='.$forum_id)
                                    ->find();
        return $forum_info;
    }


    /**  
    * 引用回复 ajax
    * @access private
    * @param int  
    * @return  mix 
    */
    public function reply(){
        if(IS_POST){
            $post = I('post.');
            $data =array(
                    'post_id'=> $post['postid'], 
                    'user_id'=> session('uid'), 
                    'role_id'=> session('rid'),
                    'post_time'=>time(),
                    'content'=>htmlspecialchars_decode($post['replycontent']),
                    'quotes'=> $post['replyto'],
                );
           $result =  M('TeacherForumReply')->add($data);
           M('TeacherForumPost')->where('id='.$post['postid'])->setInc('reply_count',1); //回复数+1
           $this->ajaxReturn($result);
        }
    }

    /**
     * 直接回复
     * @access public
     * @param   
     * @return    
     */
    public function replyT(){
        if(IS_POST){
            $post = I('post.');
            $post['content'] = htmlspecialchars_decode($post['content'] );
            $post['post_time'] =time();
            $post['user_id'] = session('uid');
            $post['role_id'] = session('rid');
            //dump($post);die;
            $result =  M('TeacherForumReply')->add($post);
            M('TeacherForumPost')->where('id='.$post['post_id'])->setInc('reply_count',1); //回复数+1
            $result ? $this->success('回复成功'):$this->error('回复失败');
        }
    }


    /**  
    * 点赞 ajax
    * 记录 uid 一个uid只能点赞一次 
    * @access public
    * @param int  
    * @return  mix 
    */
    /*public function upvote(){

        if(IS_POST){
            $post = I('post.');
            $post['teacher_id'] = session('uid');
 
            $result =  M('TeacherForumUpvote')->add($post);
            $result = M('TeacherForumPost')->where('id='.$post['post_id'])->setInc('upvote_count',1); //点赞数+1
            $this->ajaxReturn($result);
        }
 
    }*/




    /**  
    * 校验forum_id 
    * 不允许在主版块下发帖 
    * @access private
    * @param int  
    * @return  mix 
    */
    private function checkPostForum($forum_id){
        if($forum_id ==0) $this->error('所属板块必须确定！','',1);
        $forum = M('TeacherForum')->where('pid=0')->select();//主板块信息
        foreach ($forum as $key => $value) {
            if($forum_id == $key['id']){
                //若提交的forum_id在这些主版块中 只要一个
                $this->error('提交所属板块不合法！','',1);
            }
        }

    }

    /**  
    * 检验板块合法性
    * @access private
    * @param int forum_id 板块id 
    * @return  boolean  
    */
    private function checkForum($forum_id){
        if(empty($forum_id) || !is_numeric($forum_id)) return false;
        $is_exists = M('TeacherForum')->find($forum_id);
        if($is_exists){
            return true;
        }else{
          return false;  
        } 
    }


    /**  
    * 某个板块下的所有帖子数 
    * @access private
    * @param int forum_id 板块id 
    * @return  int count
    */

    private function getPostCount($forum_id){
        $pre = C('DB_PREFIX');
        if($this->checkForum($forum_id)){  //forum_id 合法性校验

            $forum = M('TeacherForum')->find($forum_id);//该板块信息
            if($forum['pid']==0){
                //主版块 获取下级所有子版块帖子数
                $count  =  M('TeacherForum')->alias('t1')
                                            ->join("right join {$pre}teacher_forum_post as t2 on t2.forum_id=t1.id")
                                            ->where('t1.pid='.$forum_id)
                                            ->count();
            }else{
                //子版块 直接获取帖子数
                $count = M('TeacherForumPost')->where('forum_id='.$forum_id)->count();
            }

            return $count;
        }

    }


    /**  
    * 某个板块下的所有浏览数 
    * @access private
    * @param int  forum_id 板块id 
    * @return  int 
    */
    private function getViewCount($forum_id){
        $pre = C('DB_PREFIX');
        if($this->checkForum($forum_id)){  //forum_id 合法性校验

            $forum = M('TeacherForum')->find($forum_id);//该板块信息
            if($forum['pid']==0){
                //主版块 获取下级所有子版块帖子浏览数
                $count  =  M('TeacherForum')->alias('t1')
                                            ->field('sum(view_count) as count')
                                            ->join("left join {$pre}teacher_forum_post as t2 on t2.forum_id=t1.id")
                                            ->where('t1.pid='.$forum_id)
                                            ->select();
            }else{
                //子版块 直接获取帖子浏览数
                $count = M('TeacherForumPost')->field('sum(view_count) as count')->where('forum_id='.$forum_id)->select();
            }

            return $count[0]['count'];
        }

    }



    /**  
    * 某个板块下的所有回复数 
    * @access private
    * @param int  
    * @return  int 
    */
    private function getReplyCount($forum_id){
        $pre = C('DB_PREFIX');
        if($this->checkForum($forum_id)){  //forum_id 合法性校验

            $forum = M('TeacherForum')->find($forum_id);//该板块信息
            if($forum['pid']==0){
                //主版块 获取下级所有子版块帖子回复数
                $count  =  M('TeacherForum')->alias('t1')
                                            ->field('sum(reply_count) as count')
                                            ->join("left join {$pre}teacher_forum_post as t2 on t2.forum_id=t1.id")
                                            ->where('t1.pid='.$forum_id)
                                            ->select();
            }else{
                //子版块 直接获取帖子回复数
                $count = M('TeacherForumPost')->field('sum(reply_count) as count')->where('forum_id='.$forum_id)->select();
            }

            return $count[0]['count'];
        }

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
        $pre = C('DB_PREFIX');
        if($forum_id == 0){

            $map = array('status'=>$type); //不指定板块的相应类型帖子 [置顶|精华|最新]
            $posts = M('TeacherForumPost')->where($map)->limit($num)->order('created_time desc')->select();

        }else{

            if( M('Forum')->find($forum_id)['pid']==0 ){
                //该板块为主版块 查询其所有子版块的 相应类型帖子 [置顶|精华|最新]
                $map = array('t1.status'=>$type, 't3.id'=>$forum_id);
                $posts = M('TeacherForumPost')->alias('t1')
                                        ->field('t1.*')
                                        ->join("left join {$pre}teacher_forum as t2 on t1.forum_id =t2.id")
                                        ->join("left join {$pre}teacher_forum as t3 on t2.pid = t3.id")
                                        ->where($map)->limit($num)->order('created_time desc')->select();
            }else{
                //该板块为子版块 直接查询其相应类型帖子 [置顶|精华|最新]
                $map = array('status'=>$type, 'forum_id'=>$forum_id);
                $posts = M('TeacherForumPost')->where($map)->limit($num)->order('created_time desc')->select();
            }
        }
        return $posts;
    }


    

    
    
    
}