<?php
namespace Course\Controller;
use Think\Controller;
class IndexController extends Controller {
        
    /**  
     * 显示课程
     * @access public 
     * @param  
     * @return  
     */
    public function index(){
        A('Teacher/Navbar')->navbar();
        $map = array('status'=>'published',);
        $pre = C('DB_PREFIX');
        $course = M('Course')->alias('t1')
                            ->field('t1.id,t1.title,t1.picture_path,t1.has_picture,t2.slug')
                            ->join("{$pre}course_category as t2 on t1.category_id =t2.id")
                            ->where($map)->select();
        $category = M('CourseCategory')->select();                                        
        //dump($course);die;
        $this->assign('course',$course);
        $this->assign('category',$category);
        $this->display();
    }
    
    
    /**  
     * 课程详情 
     * 
     * @access public 
     * @param  
     * @return  
     */
    public function details(){

        $course_id  =    I('get.id');

        // 根据seesion rid判断是谁在访问
        switch (session('rid')){
            case 1:
                $map = array('id'=>$course_id);// 管理员
                break;
            case 2:
                $map = array('id'=>$course_id);// 教师
                break;
            case 3:
                $map = array('checked'=>1, 'status'=>'published', 'id'=>$course_id);;// 学生
                break;
            default:
                $map = array('id'=>$course_id);
                break;
        }

        if(empty($course_id) || !is_numeric($course_id) || !M('Course')->where($map)->find())  $this->error('不存在该课程','',1);
        

        $course     =   M('Course')->where($map)->find();
        $category   =   M('CourseCategory')->find($course['category_id']);
 
        $course['category_name']    =   $category['name'];
        $course['category_id']      =   $category['id'];
        $course['require_skills']   =   explode(',',$course['require_skills']);

        $lesson = $this->getAllLesson($course_id); //所有课时
        //查找是否已经加入学习 
        $map = array(
            'user_id'=>session('uid'),
            'course_id'=>$course_id,
        );

        $hasjoined = D('CourseLessonLearn')->where($map)->find();
        if($hasjoined) $joined = 1; else $joined=0;
        $bulletins = M('CourseBulletin')->where('course_id='.$course_id)->select();

        //最近一次学习课时id
        $recent_learn  =   M('CourseLessonLearn')->where(array('user_id'=>session('uid'), 'course_id'=>$course_id ))->find()['lesson_id'];
        if(empty($recent_learn)) $recent_learn = 0;

        //课程关注
        
        if( M('CourseFollow')->where( array('course_id'=>$course_id, 'user_id'=>session('uid') ))->find() ) $followed = 1;
        else $followed = 0;
       

        $assign = array(
                'joined'    =>$joined,
                'course'    =>$course,
                'lesson'    =>$lesson,
                'bulletins' =>$bulletins,
                'recent_learn'=>$recent_learn,
                'followed'  =>$followed,

            );
        $this->assign($assign);
        //dump($lesson);die;
        $this->display('details');
    }
        

    /**  
     * 分类搜索 
     * 
     * @access public 
     * @param  
     * @return  
     */
    public function category($id=''){
        $pre = C('DB_PREFIX');
        $model = M('Course');
        $_SESSION['course_count']   =   M('Course')->where("status = 'published'")->count();
        if($id==null){
            //全部课程
            
            $page   =   A('Common/Pages')->getShowPage($model, array('status'=>'published'));
            $show = $page->show();
            $course = M('Course')->where("status = 'published'")->limit($page->firstRow,$page->listRows)->select();
            $_SESSION['cat_title_head']='全部';

        }else{
            $condition = array(
                'category_id'=>$id,
                'status'=>'published'
            );
            $page   =   A('Common/Pages')->getShowPage($model, $condition );
            $show = $page->show();
            $course = M('Course')->where($condition)->limit($page->firstRow,$page->listRows)->select();
            $category_item = M('CourseCategory')->field('name')->find($id);
            $_SESSION['cat_title_head']=$category_item['name'];
        }
        
        
        //查询课程分类
        $category = M()->field('t2.*, count(*) as count')
                         ->table("{$pre}course as t1, {$pre}course_category as t2")
                         ->where("t1.category_id = t2.id and t1.status='published' ")
                         ->group('category_id')
                         ->select(); 

        $latest_courses     = $this->getLatestCourse(3); //新开的课程  
        $popular_courses    = $this->getPopularCourse(6);//受欢迎的课程
        $assign =   array(
                'course'        =>$course,
                'category'      =>$category,
                'latest_courses'=>$latest_courses,
                'popular_courses'=>$popular_courses,
                'show'          =>  $show,
            );
        $this->assign($assign);                                          
    
        $this->display('category');
    }
    
    
    /**  
    * 报名课程 
    * 
    * @access public 
    * @param  
    * @return  
    */
    public function join($cid){
        
        if(!is_login() )  $this->error('请先登录',U('Home/Index/login'),3);
        
        if(!is_student() )  $this->error('该账号非学员账号','',1); 
        
        $uid =  session('uid');

        //查找是否已经加入学习 

        $course_id = $cid;
        $map = array(
            'user_id'=>$uid,
            'course_id'=>$course_id,
        );
        $hasjoined = D('CourseLessonLearn')->where($map)->find();
        if($hasjoined)    $this->error('你已经加入该课程的学习','',1);
        
        //补全信息
        $data = array(
                'user_id'   =>$uid,
                'course_id' =>$course_id,
                'start_time'=>time(),
                'status'    =>'learning',
                'lesson_id' => 0,
            );
        //dump($data);die;
        $result = D('CourseLessonLearn')->add($data);
        if($result){
            $this->success('加入学习成功','',2);

            M('Course')->where('id='.$course_id)->setInc('learn_count', 1);//学习人数+1 

            //消息机制
            $course = M('Course')->find($course_id);
            $source = array(
                    'student'=>array('name'=>session('uname')), //学生信息
                    'title' => $course['title'],//课程标题
                    'url'   => 'https://foe.zhfsky.com/index.php/Course/Index/details/id/'.$course_id,
             );
            A('Common/Messages')->send('course', 'join', $source, $course['teacher_id'],  2);//消息机制

            $count_arr = explode(',', get_option('course_join_count_notification') );
            $learn_count = M('Course')->where('id='.$course_id)->getField('learn_count');
            if( in_array($learn_count, $count_arr) ){
                $source['learncount'] = $learn_count;
                A('Common/Messages')->send('course', 'learncount', $source, $course['teacher_id'],  2);//消息机制
            }

        }
    }
    
    
    /**  
    * 学习课程 
    * 
    * @access public 
    * @param  
    * @return  
    */
    public function learn(){
        
        if(!is_login()) $this->error('请先登录',U('Home/Index/login'), 2);
            
        if(!is_student() && !is_teacher() ) $this->error('该账号非学员账号或者教师账号','',1);

            
        $uid = session('uid'); 
        $lesson_id =   I('get.id');
         
        if(empty($lesson_id) || !is_numeric($lesson_id))  $this->error('请先选择课时','',2); 
        
        if(! $this->courseIsOpen($lesson_id) ) $this->error('该课程尚未开始','',2);
          
         
        //获取课时信息
        $map    =   array( 
              'id'  =>  $lesson_id,
              'status'=>'published',
         );
        $lesson = M('CourseLesson')->where($map)->find();//课时信息
        
        if(!$lesson){
            $this->error('没有该课时信息');
        }else{
        
            if($lesson['type']=='test') $this->success('正在转向测试页面', U("Home/Paper/index/pid/".$lesson['exercise_id']), 3 );
            //获取所有课时    
            $allLesson = $this->getAllLesson($lesson['course_id']);
            //获取所有问答
            $questions = $this->getQuestions($lesson_id);
            //获取笔记  
            $note = $this->getNote($lesson_id);
            //获取资料
            $materials = $this->getMaterials($lesson_id);

            $course = M('Course')->find($lesson['course_id']);
             
            //媒体文件信息
            $media = M('CourseFiles')->field('title,uri,type')->find($lesson['media_id']);

            if(is_student()){
                 //更新学习进度信息
                 $course_id = $lesson['course_id'];
                 $upinfo['update_time'] =   time();
                 $upinfo['lesson_id']   =   $lesson_id;//将最近的一次学习课时id存入数据库

                 M('CourseLessonLearn')->where(array('user_id'=>$uid,'course_id'=>$course_id) )->save($upinfo); 
            }
            
             
             //dump($allLesson);die;
            $assign = array(
                    'course'    =>  $course,
                    'lesson'    =>  $lesson,
                    'questions'    =>  $questions,
                    'note'    =>  $note,
                    'materials'    =>  $materials,
                    'allLesson'    =>  $allLesson,
                    'media'    =>  $media,
                );
             $this->assign($assign);
             $this->display();
        }
     
    }


    /**  
     * 关注课程 
     * @access public 
     * @param  id 课程id
     * @return  
     */
    public function follow($id){
        if(!is_login()) $this->error('请先登录',U('Home/Index/login'), 2);
            
        if(!is_student() ) $this->error('该账号非学员账号','',1);

        if(empty($id) || !is_numeric($id) || !M('Course')->find($id) ) $this->error('不存在该课程','',1);

        $has_followed   =  M('CourseFollow')->where( array('course_id'=>$id, 'user_id'=>session('uid') ))->find();
        
        if($has_followed)  $result = M('CourseFollow')->where( array('course_id'=>$id, 'user_id'=>session('uid') ))->delete();//取消关注
        else $result    =   M('CourseFollow')->add(array('course_id'=>$id, 'user_id'=>session('uid')) );
        if($result) $this->success('操作成功');else  $this->error('操作失败','',1);
    }

    
    
    /**  
    * 获取所有课时列表 
    * 
    * @access private 
    * @param int $course_id 课程id
    * @return array 返回类型
    */
    private function getAllLesson($course_id){
        
        $map=array('course_id'=>$course_id);
        $lesson_chapter  = M('CourseChapter')->where($map)->order('created_time asc')->select();
        foreach($lesson_chapter as &$lesson){
            $condition=array(
                'chapter_id'=>$lesson['id'],
                'status'=>'published',
            );
            $lessoninfo = M('CourseLesson')->field('id,title')->where($condition)->order('created_time asc')->select();
            $lesson['list']=$lessoninfo;
        }
        //dump($lesson_chapter);die;
        return $lesson_chapter;
    }
    
    
    /**  
    * 获取课时问题 3条
    * 
    * @access private 
    * @param int $lesson_id 课时id
    * @return array 返回类型
    */
    private function getQuestions($lesson_id){
        
        $map=array('lesson_id'=>$lesson_id,'has_reply'=>'0');
        $questions  = M('LessonQuestions')->where($map)->limit('3')->order('created_time desc')->select();
        return $questions;
    }
    
    /**  
    * 获取课时问题 1条
    * 
    * @access public 
    * @param int $lesson_id 课时id
    * @return array 返回 
    */
    public function getOneQuestion(){
        $lesson_id=I('post.lid');
        $map=array('lesson_id'=>$lesson_id,'has_reply'=>'0');
        $question  = M('LessonQuestions')->field('id,title,content')->where($map)->limit('1')->order('created_time asc')->select();
        $question[0]['content'] = htmlspecialchars_decode($question[0]['content']);
        //$return = json_encode($question, JSON_UNESCAPED_UNICODE);
        $this->ajaxReturn($question);
    }
    
    
    /**  
    * 添加问题
    * 
    * @access public 
    * @param int $lesson_id 课时id
    * @return int id  ajax返回问题的id
    */
    public function addQuestions(){
        $pre = C('DB_PREFIX');
        $post=I('post.');
        $post['user_id']    =session('uid');
        $post['created_time']=time();
        $post['updated_time']=time();
        $result  = M('LessonQuestions')->add($post);
        if($result){
            //消息机制
            $question = M('LessonQuestions')->alias('t1')
                    ->field('t1.title as question_title,t1.user_id,t2.id,t3.title as title,t3.teacher_id')
                    ->join("left join {$pre}course_lesson as t2 on t1.lesson_id=t2.id ")
                    ->join("left join {$pre}course as t3 on t2.course_id=t3.id")
                    ->where('t1.id='.$result)
                    ->find();

            $source = array(
                    'url'   =>  'https://foe.zhfsky.com/index.php/Course/Index/learn/id/'.$question['id'],
                    'title' =>  $question['title'],
                    'question'=>array('title'=>$question['question_title'], 'url'=>'https://foe.zhfsky.com/index.php/Course/Lquestion/detail/id/'.$result),
                    'student' =>array('name'=>session('uname') ), 
                );
            A('Common/Messages')->send('course', 'qa_add', $source, $question['teacher_id'], 2 );//消息机制
        }
        $this->ajaxReturn($result);
         
    }
    
    /**  
    * 添加回答
    * @access public 
    * @param int $lesson_id 课时id
    * @return int id  ajax返回问题的id
    */
    public function addAnswers(){
        $pre  = C('DB_PREFIX');
        $post=I('post.');
        $post['user_id']    =session('uid');
        $post['post_time']=time();
        $result  = M('LessonAnswers')->add($post);
        if($result){
            M('LessonQuestions')->where('id='.$post['question_id'])->setField('has_reply','1');

            //消息机制
            $question = M('LessonQuestions')->alias('t1')->field('t1.title as question,t1.user_id,t2.id,t3.title as title')
                    ->join("left join {$pre}course_lesson as t2 on t1.lesson_id=t2.id ")
                    ->join("left join {$pre}course as t3 on t2.course_id=t3.id")
                    ->where('t1.id='.$post['question_id'])
                    ->find();

            $source = array(
                    'url'   =>  'https://foe.zhfsky.com/index.php/Course/Index/learn/id/'.$question['id'],
                    'title' =>  $question['title'],
                    'question'=>$question['question'],
                );
            A('Common/Messages')->send('course', 'qa_answer', $source, $question['user_id'], 3 );//消息机制
        }
        $this->ajaxReturn($result);
         
    }
    
    
    /**  
    * 获取笔记 
    * 一门课时一份笔记
    * @access private 
    * @param int $lesson_id 课时id
    * @return array 返回类型
    */
    private function getNote($lesson_id){
        
        $map=array('lesson_id'=>$lesson_id, 'user_id'=>session('uid') );
        $note  = M('LessonNotes')->where($map)->find();
        return $note;
    }
    
    
    /**  
    * 更新笔记
    * 
    * @access public 
    * @param int $lesson_id 课时id
    * @return boolen
    */
    public function updateNote(){

        $post=I('post.');
        //查找是否已经创建了该课时的笔记
        $map=array('lesson_id'=>$post['lesson_id'],'user_id'=>session('uid'));
        $hasNote = M('LessonNotes')->where($map)->find();
        if($hasNote){
            //已经存在 更新即可
            $post['update_time']=time();
            $result = M('LessonNotes')->where($map)->save($post);//成功返回true 1
        }else{
            //创建笔记
            $post['user_id']    =session('uid');
            $post['created_time']=time();
            $result  = M('LessonNotes')->add($post);//返回note主键id
        }
        $this->ajaxReturn($result);
         
    }
    
    /**  
    * 获取课时资料 
    * 
    * @access private 
    * @param int $lesson_id 课时id
    * @return array 返回类型
    */
    private function getMaterials($lesson_id){
        $pre = C('DB_PREFIX');
        $map=array('lesson_id'=>$lesson_id );
        $materials  = M('LessonMaterials')->alias('t1')
                                        ->field('t1.*,t2.title,t2.uri')
                                        ->join("left join {$pre}course_files as t2 on t1.file_id = t2.id")
                                        ->where($map)
                                        ->select();
        return $materials;
    }


    /**  
     * 最新发布的课程  
     * @access private 
     * @param  
     * @return  
    */
    private function getLatestCourse($num){
        $map =array('status'=>'published');
        $courses = M('Course')->where($map)->limit($num)->order('release_date desc')->select();
        return $courses;
    }

    /**  
     * 最受欢迎的课程  
     * @access private 
     * @param  
     * @return  
    */
    private function getPopularCourse($num){
        $map =array('status'=>'published');
        $courses = M('Course')->where($map)->limit($num)->order('learn_count desc')->select();
        return $courses;
    }

    /**  
     * 判断课程是否开始  
     * @access private 
     * @param  id 课时id 
     * @return  
    */
    private function courseIsOpen($id){
        $pre  = C('DB_PREFIX');
        $course = M('CourseLesson')->alias('t1')
                                    ->join("left join {$pre}course as t2 on t1.course_id=t2.id ")
                                    ->where(array('t1.id'=>$id))
                                    ->find();
        if( $course['course_start_date'] < time() ) return  true;
        else return  false;
    }
    

        
        
        
    
}