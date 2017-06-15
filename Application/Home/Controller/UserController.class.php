<?php
//1.定义命名空间
namespace Home\Controller;
//2.引入父类控制器
use Think\Controller;

//3.定义控制器并且继承父类
class UserController extends CommonController{
    
    
    //学员登录
    public function login(){
        header('Location:'.U('Home/Index/login'));
    }

    /**  
     * 学员主页
     * @access public
     * @param   
     * @return   
     */
    public function index($id=''){

        if(!empty($id) && is_numeric($id)) {
            //查看其他人公开信息
            $user   =   M('Stu')->field('username,sex,avatar,description')->find($id);
        }else{
            $user   =   M('Stu')->where('id='.session('uid'))->find(); //查看自己的信息
        }
        
        
        $this->assign('user',$user);
        $this->display('usr');
    }
    
    
    //学员退出 logout
    public function logout(){
        //清除session
        session(null);
        $this->success('退出成功',U('Index/index'),3);
    }




    /**  
     * 个人中心
     * @access public
     * @param  
     * @return
     */
    public function dashboard(){
        $uid = I('get.uid');
        $rid = I('get.rid');
        if( !empty($uid ) || !empty($rid) ){
            
            if(!is_numeric($uid) || !is_numeric($rid) ) $this->error('无效的请求'); 

            switch ($rid) {
                  case 1:
                      $model = M('User');
                      break;
                  case 2:
                      $model = M('Teacher');
                      break;
                  case 3:
                      $model = M('Stu');
                      break;       
                  default:
                      $model = M('Stu');
                      break;
              }  
            if(! $model->find($uid) ) $this->error('不存在此人'); 


            if( $uid != session('uid') ){
                //查看别人信息 
                $user   =   $model->field('id, username , sex, avatar, description')->find($uid);

                //是否关注
                $map = array(
                        'user_id'   =>  session('uid'),
                        'role_id'   =>  session('rid'),
                        'friend_uid'=>  $uid,
                        'friend_rid'=>  $rid,
                    );
                if(M('Relation')->where($map)->find())  $has_followed = 'yes'; //我已经关注了对方
                else   $has_followed = 'no';

                //对方最近动态
                $activities =   M('Messages')->where(array('user_id'=>$uid, 'role_id'=>$rid, 'type'=>array('in', 'forum,course,live')))
                                            ->limit(10)->order('post_time desc')->select();
                foreach ($activities as $key => &$value) {
                    $value['message'] = str_replace('您', '他', $value['message']);
                }
                 
                
            }

        }else{
            //自己的信息
            $user  = M('Stu')->field('id, username, truename, sex, avatar, description')->find(session('uid')); 
            $activities ='';
            $has_followed=0;
        }
        
        $user['uid']=$uid;
        $user['rid']=$rid;
        
        $this->assign('user',$user);
        $this->assign('has_followed',$has_followed);
        $this->assign('activities',$activities);
        $this->display();
    }

    /**  
     * 个人资料
     * @access public
     * @param  
     * @return
     */
    public function profile(){
        $user  = M('Stu')->where('id='.session('uid'))->find();
        $this->assign('user',$user);
        $this->display();
    }


    /**  
     * 课程状态
     * @access public
     * @param  
     * @return
     */
    public function course($type='ing'){
        $pre = C('DB_PREFIX');
        if($type=='follow'){
            //关注的课程
            $map = array(
                't1.user_id'    =>  session('uid'),
                't2.status'     =>  'published',
                );
            $courses = M('CourseFollow')->alias('t1')
                                        ->distinct(true)
                                        ->field('t1.course_id,t2.*,t3.username as teacher_name')
                                        ->join("left join {$pre}course as t2 on t2.id=t1.course_id")  
                                        ->join("left join {$pre}teacher as t3 on t2.teacher_id=t3.id")                                          
                                        ->where($map)
                                        ->select();
        }else{
            if($type=='pre'){
            //即将开课的课程 已经报名了学习 状态:已发布
             $map = array(
                't1.user_id'    =>session('uid'),
                't2.status'     =>'published',
                't2.course_start_date' => array('gt', time()),
                );
            }elseif($type=='over'){
                //已经结课的课程 自己加入学习的课程
                $map = array(
                    't1.user_id'    =>session('uid'),
                    't2.status'     =>'published',
                    't2.course_end_date' => array('lt', time()),
                );
            }elseif($type=='ing'){
                //正在学习的课程
                $map = array(
                    't1.user_id'    =>session('uid'),
                    't2.status'     =>'published',
                    't2.course_start_date'  => array('lt', time()),
                    't2.course_end_date'    => array('gt', time()),
                );
            }
            $courses  = M('CourseLessonLearn')  ->alias('t1')
                                                ->distinct(true)
                                                ->field('t1.course_id,t2.*,t3.username as teacher_name')
                                                ->join("left join {$pre}course as t2 on t2.id=t1.course_id")  
                                                ->join("left join {$pre}teacher as t3 on t2.teacher_id=t3.id")                                          
                                                ->where($map)
                                                ->select();
        }
        
        //dump($courses);die;
        $this->assign('courses',$courses);
        $this->display();
    }



    /**  
     * 讨论
     * @access public
     * @param  
     * @return
     */
    public function discuss($type='lead'){
        $pre = C('DB_PREFIX');

        if($type=='lead'){
            $map = array(
                    'user_id'=>session('uid'),
                    'role_id'=>session('rid'),
                );
            $discuss = M('CourseDiscuss')->where($map)->select();

        }elseif($type=='attend'){
            $map = array(
                    't1.user_id'=>session('uid'),
                    't1.role_id'=>session('rid'),
                );
            $discuss = M('CourseDiscussReply')  ->alias('t1')
                                                ->field('t2.*')
                                                ->join("left join {$pre}course_discuss as t2 on t1.discuss_id=t2.id")
                                                ->distinct(true)
                                                ->where($map)
                                                ->select();
        }

        //dump($discuss);die;
        $this->assign('discuss', $discuss);
        $this->display();
    }


    /**  
     * OJ
     * @access public
     * @param  
     * @return
     */
    public function onlineJudge(){

        $map = array('status'=>'open');

        $problems = M('OjProblem')->where($map)->select();
        $assign = array(
                'problems'=>$problems,
            );
        $this->assign($assign);
        $this->display('oj');
    }




    /**  
     * 站内信件
     * @access public
     * @param  
     * @return
     */
    public function mail(){
    }




    /**  
     * 系统消息提醒
     * @access public
     * @param  
     * @return
     */
    public function message($type='all'){
        $map = array(
                'user_id'   =>  session('uid'),
                'role_id'   =>  session('rid'),
            );
        switch ($type) {

            case 'forum':
                $map['type']='forum';
                $page['title']='论坛通知';
                break;
            case 'qa':
                $map['type']='course';
                $map['event']= array('in', 'qa,qa_answer,qa_teacher_answer');
                $page['title']='问答消息';
                break;
            case 'system':
                $map['type']=array('in', 'account');
                $page['title']='系统消息';
                break;
            default:
                $page['title']='全部消息';
                break;
        }
        $model = M('Messages');
        $pages = A('Common/Pages')->getShowPage($model, $map);
        $show = $pages->show();
        $messages = M('Messages')->where($map)->order('post_time desc')->limit($pages->firstRow,$pages->listRows)->select();

        $this->assign('messages', $messages);
        $this->assign('show', $show);
        $this->assign('page', $page);
        $this->display();
    }



    /**  
     * 聊天 
     * @access public
     * @param  
     * @return
     */
    public function chat($id=''){

        $friends    = '';
        $messages   = '';
        $series     = '';

        if(!empty($id) && strripos($id, '_')){
            //双人聊天室
            $arr    = explode('_', $id);
            //dump($arr );die;
            $friends[0]['friend_info'] = A('Common/Users')->getUserInfo(intval($arr[2]), intval($arr[3]) ); //朋友信息
            $friends[0]['online'] = A('Common/Users')->getOnlineInfo(intval($arr[2]), intval($arr[3]) );
            $friends[0]['chat']['series']  = $id;

            $series_op = array('aaa'=>$id, 'bbb'=>$arr[2].'_'.$arr[3].'_'.$arr[0].'_'.$arr[1]);
            $map    = array('series'=>array('in', $series_op) );

            $has_exist = M('ChatRoom')->where($map)->find();

            if(! $has_exist){

                $data = array('series'=> $id);
                M('ChatRoom')->add($data);
                $messages = '';
                $series = $id;

            }else{

                $series   = $has_exist['series'];    
                $messages = $has_exist['messages'];//json 
                $messages = json_decode($messages, true);//array
            }

        }else{
            // 不进入指定双人聊天
            $friends    =   A('Common/Users')->getFriendsInfo(session('uid'), session('rid'));//所有朋友信息
        }
        

        $assign     = array(
                'friends'   =>  $friends,
                'messages'  =>  $messages,
                'series'    =>  $series,

            );
        $this->assign($assign);
        //dump($messages);die;
        $this->display();
    }



    /**  
     * 人脉 
     * @access public
     * @param  
     * @return
     */
    public function relation($type='friend'){

        switch ($type) {
            case 'friend'://我的好友
                $relations =  A('Common/Users')->getFriendsInfo(session('uid'), session('rid'));
                $page['title'] = '我的好友';
                break;
            case 'follow'://我的关注
                $relations =  A('Common/Users')->getFollowersInfo(session('uid'), session('rid'));
                $page['title'] = '我的关注';
                break;
            case 'fan'://我的粉丝
                $relations =  A('Common/Users')->getFansInfo(session('uid'), session('rid'));
                $page['title'] = '我的粉丝';
                break;
            default:
                # code...
                break;
        }

        $page['type'] = $type;

        

        $assign =   array(
                'relations' =>  $relations,
                'page'      =>  $page,
            );
        //dump($relations);die;
        $this->assign($assign);
        $this->display();
    }

    /**  
     * 人脉 
     * @access public
     * @param  
     * @return
     */
    public function relationChange($type){
        if(IS_POST){

            $fuid = I('post.fuid');
            $frid = I('post.frid');

            if($type=='follow'){
                $map = array('user_id'=>session('uid'), 'role_id'=>session('rid'), 'friend_uid'=>$fuid, 'friend_rid'=>$frid );
                $has_exists = M('Relation')->where($map)->find();//A
                if($has_exists){
                    //A已经关注过了B
                    $map_r = array('friend_uid'=>session('uid'), 'friend_rid'=>session('rid'), 'user_id'=>$fuid, 'role_id'=>$frid );
                    $op_follow = M('Relation')->where($map_r)->find();//查看B是否关注了A
                    if($op_follow){
                        //B也关注了A
                        M('Relation')->where(array('id'=>$has_exists['id']) )->setField('status','friend');//将两人变为朋友关系 A
                        M('Relation')->where(array('id'=>$op_follow['id']) )->setField('status','friend');//将两人变为朋友关系 B
                    }
                    
                    $this->ajaxReturn('已经关注过了');
                }else{
                    //A尚未关注B
                    $data = array('user_id'=>session('uid'), 'role_id'=>session('rid'), 'friend_uid'=>$fuid, 'friend_rid'=>$frid ,'status'=>'follow');
                    $result = M('Relation')->add($data);
                    if($result){
                        //A成功关注了B  
                        $con = array('friend_uid'=>session('uid'), 'friend_rid'=>session('rid'), 'user_id'=>$fuid, 'role_id'=>$frid );
                        $op_follow = M('Relation')->where($con)->find();//查看B是否关注了A
                        if($op_follow){
                            //B也关注了A
                            M('Relation')->where(array('id'=>$result ) )->setField('status','friend');//将两人变为朋友关系 A
                            M('Relation')->where(array('id'=>$op_follow['id']) )->setField('status','friend');//将两人变为朋友关系 B
                        }

                        $this->ajaxReturn('关注成功');
                    }  
                }

            }elseif($type=='unfollow'){

                $map = array('user_id'=>session('uid'), 'role_id'=>session('rid'), 'friend_uid'=>$fuid, 'friend_rid'=>$frid );
                $has_exists = M('Relation')->where($map)->find();
                if(!$has_exists){
                    //A并没有关注B
                    $this->ajaxReturn('已经取消过了');
                }else{
                    //A关注了B
                    $result = M('Relation')->delete($has_exists['id']);  //取消关注 删除
                    if($result){
                        //取消关注成功 改变A B status 不论是friend 还是follow 均改为follow 无需检测AB互相关注状态
                        $con = array('friend_uid'=>session('uid'), 'friend_rid'=>session('rid'), 'user_id'=>$fuid, 'role_id'=>$frid );
                        M('Relation')->where($con)->setField('status','follow');
                        $this->ajaxReturn('取消关注成功');
                    }
                }
            }
        }
    }



    /**  
     * 设置
     * @access public
     * @param  
     * @return
     */
    public function setting($type){
         
    }





   

 


 









    
    
     
    
     
    
    
     
    
 
}
