<?php
namespace Forum\Controller;
use Think\Controller;

class ViewController extends Controller{

    /**
     * 帖子内容
     * @access public
     * @param 
     * @return 
     */
    public function index($id){
        if(!$this->checkForumPost($id)) $this->error('不存在该帖子','',2);//检验帖子id合法性

        $post           =   M('ForumPost')->find($id);
        $post['tags']   =   explode(',', $post['tags']);
        $author         =   $this->getPostAuthor($post['role_id'], $post['user_id']);//该帖子发布者信息
        $replys         =   $this->getReplys($id);//该帖子下所有回复
        $attachment     =   $this->getAttachment($post['attachment_id']);//附件信息
        $forum_info     =   $this->getForumInfo($id); //该帖子所属板块信息
        $wonful_reply   =   $this->getWonderfulReply(6);
        $hottags        =   $this->getHotTags(12);
        if(is_admin()) $is_admin = 'yes'; else $is_admin='no';//管理员身份判断
        $assign         = array(
                'post'      => $post,
                'author'    => $author,
                'replys'    => $replys,
                'forum_info'=> $forum_info ,
                'wful_reply'=> $wonful_reply,
                'hottags'   => $hottags,
                'attachment'=> $attachment,
                'is_admin'  => $is_admin,

            );
        //dump($replys);die;
        M('ForumPost')->where('id='.$id)->setInc('view_count',1); //浏览数+1  暂时这么处理
        $this->assign($assign);
        $this->display();
    }


    /**  
    * 引用回复 ajax
    * @access private
    * @param int  
    * @return  mix 
    */
    public function reply(){
        if(!is_login()) $this->ajaxReturn('请先登录');
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
            $result =  M('ForumReply')->add($data);
            M('ForumPost')->where('id='.$post['postid'])->setInc('reply_count',1); //回复数+1

            //消息机制
            $forum_post = M('ForumPost')->find($post['postid']);
            $source =array(
                    'url'   =>'https://foe.zhfsky.com/index.php/Forum/View/index/id/'.$post['postid'].'.html#reply_'.$result,
                    'reply_username'=>session('uname'),
                    'title' => $forum_post['title'],
                );
            if($result) A('Common/Messages')->send('forum', 'reply', $source, $forum_post['user_id'], $forum_post['role_id'] );//消息机制

            if($result) $this->ajaxReturn('回复成功');
        }
    }

    /**
     * 直接回复
     * @access public
     * @param   
     * @return    
     */
    public function replyT(){
        if(!is_login()) $this->error('请先登录','',1);
        if(IS_POST){
            $post = I('post.');
            $post['content'] = htmlspecialchars_decode($post['content'] );
            if(!$post['content'] ) $this->error('内容不得为空','',1);
            $post['post_time'] =time();
            $post['user_id'] = session('uid');
            $post['role_id'] = session('rid');
            //dump($post);die;
            $result =  M('ForumReply')->add($post);
            M('ForumPost')->where('id='.$post['post_id'])->setInc('reply_count',1); //回复数+1

            //消息机制
            $forum_post = M('ForumPost')->find($post['post_id']);
            $source =array(
                    'url'   =>'https://foe.zhfsky.com/index.php/Forum/View/index/id/'.$post['post_id'].'.html#reply_'.$result,
                    'reply_username'=>session('uname'),
                    'title' => $forum_post['title'],
                );
            if($result) A('Common/Messages')->send('forum', 'reply', $source, $forum_post['user_id'], $forum_post['role_id'] );//消息机制

            $result ? $this->success('回复成功'):$this->error('回复失败');
        }
    }



    /**
     * 加精华 ajax
     * @access public
     * @param   
     * @return    
     */
    public function essence(){
        if(!is_admin()) $this->ajaxReturn('你不是管理员');
        if(IS_POST){
            $map = array('id'=> I('post.postid'));
            if( M('ForumPost')->where($map)->getField('status') == 'essence'){
                $result = M('ForumPost')->where( $map )->setField('status','normal');

                //消息机制
                $forum_post = M('ForumPost')->find(I('post.postid') );
                $source =array(
                    'url'   =>  'https://foe.zhfsky.com/index.php/Forum/View/index/id/'.$forum_post['id'],
                    'title' =>  $forum_post['title'],
                );
                if($result) A('Common/Messages')->send('forum', 'unessence', $source, $forum_post['user_id'], $forum_post['role_id'] );//消息机制

                $result === false ? $this->ajaxReturn('加精华取消失败'):$this->ajaxReturn('加精华取消成功');
            }else{
               $result = M('ForumPost')->where( $map )->setField('status','essence');

               //消息机制
               $forum_post = M('ForumPost')->find(I('post.postid') );
               $source =array(
                    'url'   =>'https://foe.zhfsky.com/index.php/Forum/View/index/id/'.$forum_post['id'],
                    'title' => $forum_post['title'],
                );
               if($result)  A('Common/Messages')->send('forum', 'essence', $source, $forum_post['user_id'], $forum_post['role_id'] );//消息机制

               $result === false ? $this->ajaxReturn('加精华失败'):$this->ajaxReturn('加精华成功');
            }
            
        }
    }


    /**
     * 置顶 ajax
     * @access public
     * @param   
     * @return    
     */
    public function stick(){
        if(!is_admin()) $this->ajaxReturn('你不是管理员');
        if(IS_POST){
            $map = array('id'=> I('post.postid'));
            if( M('ForumPost')->where($map)->getField('status') == 'stick'){
                $result = M('ForumPost')->where( $map )->setField('status','normal');

                //消息机制
                $forum_post = M('ForumPost')->find(I('post.postid') );
                $source =array(
                    'url'   =>  'https://foe.zhfsky.com/index.php/Forum/View/index/id/'.$forum_post['id'],
                    'title' =>  $forum_post['title'],
                );
                if($result) A('Common/Messages')->send('forum', 'unstick', $source, $forum_post['user_id'], $forum_post['role_id'] );//消息机制


                $result === false ? $this->ajaxReturn('置顶取消失败'):$this->ajaxReturn('置顶取消成功');
            }else{
               $result = M('ForumPost')->where( $map )->setField('status','stick');

               //消息机制
                $forum_post = M('ForumPost')->find(I('post.postid') );
                $source =array(
                    'url'   =>  'https://foe.zhfsky.com/index.php/Forum/View/index/id/'.$forum_post['id'],
                    'title' =>  $forum_post['title'],
                );
                if($result) A('Common/Messages')->send('forum', 'stick', $source, $forum_post['user_id'], $forum_post['role_id'] );//消息机制

               $result === false ? $this->ajaxReturn('置顶失败'):$this->ajaxReturn('置顶成功');
            }
            
        }
    }

    /**
     * 置顶 ajax
     * @access public
     * @param   
     * @return    
     */
    public function delete(){

        if(!is_admin()) $this->ajaxReturn('你不是管理员');

        if(IS_POST){

            $id = I('post.postid');
            $forum_post = M('ForumPost')->find($id);
            if(!$forum_post) $this->ajaxReturn('不存在该帖子');
            $result = M('ForumPost')->delete($id);
            if($result)  M('ForumReply')->where(array('post_id'=>$id) )->delete(); //删除所有相关回复
            
            //消息机制
            $source =array(
                'url'   =>  'https://foe.zhfsky.com/index.php/Forum/View/index/id/'.$forum_post['id'],
                'title' =>  $forum_post['title'],
                'reason'=>  I('post.reason'),
            );
            if($result) A('Common/Messages')->send('forum', 'delete', $source, $forum_post['user_id'], $forum_post['role_id'] );//消息机制

            $result === false ? $this->ajaxReturn('删除失败'):$this->ajaxReturn('删除成功');
             
        }
    }




    /**
     * 收藏该贴 ajax
     * @access public
     * @param   
     * @return    
     */
    public function collect(){
 
    }



    /**
     * 点赞 ajax
     * @access public
     * @param   
     * @return    
     */
    public function thumbUp(){

        if( empty(session('uid')) || empty(session('rid')) )  $this->ajaxReturn('请先登录');

        if(IS_POST){
            
            $post = I('post.');
            $data =array(
                    'reply_id'=> $post['replyid'], 
                    'user_id'=> session('uid'), 
                    'role_id'=> session('rid'),
                );

            $map = array('reply_id'=>$post['replyid'], 'user_id'=> session('uid'), 'role_id'=>session('rid') );

            if( M('ForumUpvote')->where($map)->find() ){
                $this->ajaxReturn('你已经点过赞了');
            } else{
                $result =  M('ForumUpvote')->add($data);
                
                if($result){
                    M('ForumReply')->where('id='.$post['replyid'])->setInc('thumbup_count',1); //点赞数+1
                    $this->ajaxReturn('点赞成功!');
                }
                
            }
            
        }
    }




    /**
     * 检验帖子id合法性
     * @access private
     * @param 
     * @return 
     */
    private function checkForumPost($id){
        if(empty($id) || !is_numeric($id) ) return false;
        if(M('ForumPost')->find($id)) return true;
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
                 $author = M('Stu')->field('id,username as name,avatar,description')->find($uid);
                 $author['user_role'] = 'student';
                 $author['rid'] = 3;
                 break;
            case '2':
                 //教师
                 $author = M('Teacher')->field('id,username as name,avatar,description')->find($uid);
                 $author['user_role'] = 'teacher';
                 $author['rid'] = 2;
                 break;
            case '1':
                 //管理员
                 $author = M('User')->field('id,username as name,avatar,description')->find($uid);
                 $author['user_role'] = 'admin';
                 $author['rid'] = 1;
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

        $replys = M('ForumReply')->where('post_id='.$post_id)->select(); //该帖子下的所有回复

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
                $info = M('Teacher')->field('username,avatar')->find($value['user_id']);
                $value['user_name']    = $info['username'];
                $value['user_avatar']  = $info['avatar'];
                $value['user_role']    = 'teacher';
            }elseif($value['role_id']=='3'){
                //该回复来自学生
                $info = M('Stu')->field('username,avatar')->find($value['user_id']);
                $value['user_name']    = $info['username'];
                $value['user_avatar']  = $info['avatar'];
                $value['user_role']    = 'student';
            }

            if($value['quotes']){
                //该回复引用了其他人的回复  quotes是引用的回复id
                $value['quotes'] = M('ForumReply')->find($value['quotes']);
                if($value['quotes']['role_id']=='1'){
                    //该引用的回复来自管理员
                    $info = M('User')->field('truename,avatar')->find($value['quotes']['user_id']);
                    $value['quotes']['user_name']    = $info['truename'];
                    $value['quotes']['user_avatar']  = $info['avatar'];
                    $value['quotes']['user_role']    = 'admin';

                }elseif($value['quotes']['role_id']=='2'){
                    //该引用的回复来自教师
                    $info = M('Teacher')->field('username,avatar')->find($value['quotes']['user_id']);
                    $value['quotes']['user_name']    = $info['username'];
                    $value['quotes']['user_avatar']  = $info['avatar'];
                    $value['quotes']['user_role']    = 'teacher';

                }elseif($value['quotes']['role_id']=='3'){
                    //该引用的回复来自学生
                    $info = M('Stu')->field('username,avatar')->find($value['quotes']['user_id']);
                    $value['quotes']['user_name']    = $info['username'];
                    $value['quotes']['user_avatar']  = $info['avatar'];
                    $value['quotes']['user_role']    = 'teacher';

                }

            }

            //该回复的点赞信息
            if(is_login()){
                $map = array(
                        'reply_id'=> $value['id'], 
                        'user_id'=> session('uid'), 
                        'role_id'=> session('rid') 
                    );
                if( M('ForumUpvote')->where($map)->find() ){
                    //该用户已经点过赞了
                    $value['upvote'] = 'yes'; 
                }else{
                    $value['upvote'] = 'no'; 
                }
            }else{
                //未登录用户
               $value['upvote'] = 'no'; 
            }
            
        }


        return $replys;
        
    }



    /**
     * 获取所属板块信息
     * @access private
     * @param  int post_id 帖子id
     * @return 
     */
    private function getForumInfo($post_id){
        $pre = C('DB_PREFIX');
        $forum_id   = M('ForumPost')->field('forum_id')->find($post_id)['forum_id'];
        $forum_info = M('Forum')->alias('t1')
                                ->field('t1.*,t2.title as ptitle')
                                ->join("left join {$pre}forum as t2 on t1.pid =t2.id")
                                ->where('t1.id='.$forum_id)
                                ->find();
        return $forum_info;
    }


    /**
     * 获取精彩评论 
     * 根据点赞人数判断  /学生的评论
     * @access private
     * @param 
     * @return 
     */
    private function getWonderfulReply($num){
        $replys = M('ForumReply')->order('thumbup_count desc')->where('role_id=3')->limit($num)->select();

        foreach ($replys as $key => &$value) {
            //回复者信息 学生
            $info = M('Stu')->field('username,avatar')->find($value['user_id']);
            $value['user_name']    = $info['username'];
            $value['user_avatar']  = $info['avatar'];
            $value['user_role']    = 'student';
        }
        return $replys;
    }


    /**
     * 获取热门标签
     * @access private
     * @param   
     * @return 
     */
    private function getHotTags($num){
        $tags = M('ForumTag')->order('count desc')->limit($num)->select();
        return $tags;
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

















}