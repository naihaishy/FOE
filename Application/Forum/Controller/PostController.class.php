<?php
namespace Forum\Controller;
use Think\Controller;

class PostController extends Controller{


    /**
     * 初始化
     * @access public
     * @param 
     * @return 
     */
    public function _initialize(){
        $uid    =   session('uid');
        $rid    =   session('rid');
        //判断学员是否登录
        if( empty($uid) or empty($rid) ){
            //学员没有登录
            $this->error('请先登录',U('Home/Index/login'),3);
        }elseif( is_admin() || is_teacher() || is_student() ){
            
        }else{
            $this->error('请先登录',U('Home/Index/login'),3);
        }
        
    }


    /**
     * 发表帖子
     * @access public
     * @param 
     * @return 
     */
    public function index(){
        $pre = C('DB_PREFIX');
        $forum =   M('Forum')->alias('t1')
                            ->field('t1.*,t2.title as ptitle')
                            ->join("left join {$pre}forum as t2 on t1.pid =t2.id")
                            ->where("t1.pid !=0")
                            ->select(); //板块信息 只能在子版块发帖  限制用户不得选择主版块 (checkPostForum 再次进行验证过滤)
        //dump($forum);die;
        $this->assign('forum', $forum);
        $this->display();
    }

 
    /**
     * 发表帖子
     * @access public
     * @param 
     * @return 
     */
    public function add(){
        if(IS_POST){
            $post = I('post.');
            $this->checkPostForum($post['forum_id']);//检验提交fourm_id合法性
            //自动验证提交数据
            $rules = array(
                 array('title','require','标题必须有！'),
                 array('forum_id','require','所属板块必须确定！'),
                 array('content','require','内容不得为空!'),
            );
            $model = D('ForumPost');
            if(!$model->validate($rules)->create())  $this->error($model->getError(),'',1);

            $result = $model->addData($post, $_FILES['attachment'] );
            $result ? $this->success('发布成功', U('Forum/View/index/id/'.$result),1): $this->error('发布失败','',1);
        }
    }




    /**  
     * 校验forum_id 
     * 不允许在主版块下发帖 
     * @access private
     * @param int  
     * @return  mix 
     */
    private function checkPostForum($forum_id){
        if($forum_id ==0) $this->error('所属板块必须确定！','',1);
        $forum = M('Forum')->where('pid=0')->select();//主板块信息
        foreach ($forum as $key => $value) {
            if($forum_id == $key['id']){
                //若提交的forum_id在这些主版块中 只要一个
                $this->error('提交所属板块不合法！','',1);
            }
        }
    }




}