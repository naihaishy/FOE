<?php
namespace Course\Controller;
use Think\Controller;

/**
* 课时问题处理类
*/
class LquestionController extends Controller{
    

    /**
     * 问题列表
     * @access public
     * @param    
     * @return    
     */
    public function index(){
        $pre = C('DB_PREFIX');
        $map = array('t1.teacher_id' => session('uid'),'t1.status'=>'published','t2.status'=>'published');
        $lquestion =  M('Course')   ->alias('t1')
                                    ->field('t1.title as course_title,t2.id as lesson_id,t2.title as lesson_title,t3.*')
                                    ->join("right join {$pre}course_lesson as t2 on t1.id= t2.course_id")
                                    ->join("right join {$pre}lesson_questions as t3 on t2.id= t3.lesson_id")
                                    ->where($map)
                                    ->select(); 

        //dump($lquestion);die;
        $this->assign('lquestion', $lquestion);
        $this->display();
    }

    /**
     * 问题详情
     * @access public
     * @param  qid 问题id
     * @return    
     */
    public function detail($id){
        $pre  = C('DB_PREFIX');
        if(empty($id)) $this->error('非法访问');
        //查询问题信息及提问者信息 
        $question = M('LessonQuestions')->alias('t1')
                                        ->field('t1.*,t2.truename as name')
                                        ->join("left join {$pre}stu as t2 on t1.user_id = t2.id")
                                        ->where('t1.id='.$id)
                                        ->find();
        //dump($info);die;

        $answers = M('LessonAnswers')->where(array('question_id'=>$id))->select();//该问题下的所有回复
        
        foreach ($answers as $key => &$value) {
            //回复者信息
            if($value['metas']=='teacher'){
                //该回复来自教师
                $value['user_name']  = M('Teacher')->find($value['user_id'])['username'];
                $value['user_role']  ='teacher';

            }else{
                //该回复来自学生
                $value['user_name']  = M('Stu')->find($value['user_id'])['username'];
                $value['user_role']  ='student';
            }

            if($value['quotes']){
                //该回复引用了其他人的回复  quotes是引用的回复id
                $value['quotes'] = M('LessonAnswers')->find($value['quotes']);
                if(!$value['quotes']){
                    //没有找到引用的回复 说明不存在 自检
                    $value['quotes'] = 0; 
                }else{
                   if($value['quotes']['metas']=='teacher'){
                        //该引用的回复来自教师
                        $value['quotes']['user_name']  = M('Teacher')->find($value['quotes']['user_id'])['username'];
                        $value['quotes']['user_role']  ='teacher';
                    }else{
                        //该引用的回复来自学生
                        $value['quotes']['user_name']  = M('Stu')->find($value['quotes']['user_id'])['username'];
                        $value['quotes']['user_role']  ='student';
                    } 
                }
                

            }
        }

        //dump($answers);die;

        $this->assign('question', $question);
        $this->assign('answer', $answers);
        $this->display();
    }

    /**
     * 置顶问题
     * @access public
     * @param 
     * @return    
     */
    public function stickTop(){
        if(IS_POST){
            $post   = I('post.');
            $result = M('LessonQuestions')->where('id='.$post['qid'])->setField('sticktop',1);
            $this->ajaxReturn($result);
        }
    }


    /**
     * 删除问题
     * @access public
     * @param  qid 问题id
     * @return    
     */
    public function delQuestion(){
        $qid = I('get.id');
        $result = M('LessonQuestions')->delete($qid);
        M('LessonAnswers')->where('question_id='.$qid)->delete(); //删除该问题下的所有回复
        $result ? $this->success('删除成功'):$this->error('删除失败');
    }




    /**
     * 引用回复 ajax
     * @access public
     * @param  qid 问题id
     * @return    
     */
    public function reply(){
        if(IS_POST){
            $post = I('post.');
            $data =array(
                    'question_id'=> $post['questionid'], 
                    'user_id'=> session('uid'), 
                    'post_time'=>time(),
                    'metas'=>'teacher',
                    'answer'=>$post['replycontent'],
                    'quotes'=> $post['replyto'],
                );
           $result =  M('LessonAnswers')->add($data);

           if($result){
                M('LessonQuestions')->where('id='.$post['questionid'])->setField('teacher_reply',1);//

            }

           $this->ajaxReturn($result);
        }
    }


    /**
     * 直接回复
     * @access public
     * @param  qid 问题id
     * @return    
     */
    public function replyT(){
        $pre = C('DB_PREFIX');
        if(IS_POST){
            $post = I('post.');
            $post['metas'] = 'teacher';
            $post['post_time'] =time();
            $post['user_id'] = session('uid');
            //dump($post);die;
            $result =  M('LessonAnswers')->add($post);
            if($result){
                M('LessonQuestions')->where('id='.$post['question_id'])->setField('teacher_reply',1);
                
                //消息机制
                $question = M('LessonQuestions')->alias('t1')
                                                ->field('t1.title as question,t1.user_id,t2.id,t3.title as title')
                                                ->join("left join {$pre}course_lesson as t2 on t1.lesson_id=t2.id ")
                                                ->join("left join {$pre}course as t3 on t2.course_id=t3.id ")
                                                ->where('t1.id='.$post['question_id'])
                                                ->find();

                $source = array(
                        'url'   =>  'https://foe.zhfsky.com/index.php/Course/Index/learn/id/'.$question['id'],
                        'title' =>  $question['title'],
                        'question'=>$question['question'],
                    );
                A('Common/Messages')->send('course', 'qa_teacher_answer', $source, $question['user_id'], 3 );//消息机制

            }
            
            $result ? $this->success('回复成功'):$this->error('回复失败');
        }
    }

    

    /**
     * 删除回复
     * @access public
     * @param  aid 回答id
     * @return    
     */
    public function delAnswer(){
        $aid = I('get.aid');
        $result = M('LessonAnswers')->delete($aid); 
        $result ? $this->success('删除成功','',1):$this->error('删除失败','',1);
    }

    


}