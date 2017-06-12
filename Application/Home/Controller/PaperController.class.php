<?php

namespace Home\Controller;
use Think\Controller;

class PaperController extends Controller{
    
    
    /**  
        * 试卷
        * 
        * @access public
        * @param  
        * @return  
        */
    public function index(){
        if(!is_login()) $this->error('请先登录', U('Home/Index/login'),2);
        if(! is_student() && !is_teacher() ) $this->error('访问出错','',2);
        $paper_id = I('get.pid');
        $exam = $this->getPaper($paper_id);
        $paper = D('CoursePaper')->find($paper_id);
        $paper['time_limit'] = explode(',', $paper['time_limit']);
        $numarr  = $this->getPaperCatNum($paper);
        $unitarr = $this->getPaperCatUnit($paper);
        $this->assign('exam',$exam);
        $this->assign('paper',$paper);
        $this->assign('numarr',$numarr);
        $this->assign('unitarr',$unitarr);
        $this->display();
    }
    
    
    /**  
    * 生成试卷
    * 
    * @access private
    * @param  int paper_id 
    * @return  arr
    */
    private function getPaper($paper_id){
        
        $paper = D('CoursePaper')->field('gener_method')->find($paper_id);
        
        //组卷方式 随机 难易程度
        if( $paper['gener_method']=='difficulty' ){
            $exam_paper = $this->getDiffPaper($paper_id);
        }elseif($paper['gener_method']=='random'){
            $exam_paper = $this->getRandPaper($paper_id);
        }
        
        return $exam_paper;
    }
    
    
    /**  
    * 随机组卷
    * 
    * @access private
    * @param  int paper_id 试卷id
    * @return  
    */
    private function getRandPaper($paper_id){
        
        $paper = D('CoursePaper')->find($paper_id);
        $arr =  $this->getPaperCatNum($paper);
        return $this->getPaperQuestion($paper['course_id'], 'rand', $arr);
    }
    
    /**  
     * 难易程度组卷
     * 
     * @access private
     * @param  int paper_id 试卷id
     * @return  diff_setting 为空返回false 存在 返回数组
     */
    private function getDiffPaper($paper_id){
    
        $paper = D('CoursePaper')->find($paper_id);
        
        if( empty($paper['diff_setting'])){return false; }  
        
        
        $diff_set_arr = explode(',' , $paper['diff_setting'] );
        $diff_easy = $diff_set_arr[0]/100; //难易程度百分比
        $diff_medi = $diff_set_arr[1]/100;
        $diff_hard = $diff_set_arr[2]/100;
          
          
        $numarr = $this->getPaperCatNum($paper);
        
        $arr =array(
            'radio'=>array('easy'=>round($numarr['radio'] * $diff_easy), 'medi'=>round($numarr['radio'] * $diff_medi), 'hard'=>round($numarr['radio'] * $diff_hard) ),
            'multi'=>array('easy'=>round($numarr['multi'] * $diff_easy), 'medi'=>round($numarr['multi'] * $diff_medi), 'hard'=>round($numarr['multi'] * $diff_hard) ),
            'check'=>array('easy'=>round($numarr['check'] * $diff_easy), 'medi'=>round($numarr['check'] * $diff_medi), 'hard'=>round($numarr['check'] * $diff_hard) ),
            'fill' =>array('easy'=>round($numarr['fill']  * $diff_easy), 'medi'=>round($numarr['fill']  * $diff_medi), 'hard'=>round($numarr['fill']  * $diff_hard) ),
            'prog' =>array('easy'=>round($numarr['prog']  * $diff_easy), 'medi'=>round($numarr['prog']  * $diff_medi), 'hard'=>round($numarr['prog']  * $diff_hard) ),
        );
            
        return $this->getPaperQuestion($paper['course_id'], 'diff', $arr);
    }
    
    
    /**  
    * 获取试卷题目
    * 
    * @access private
    * @param  $course_id 课程id
    * @param  string method 组卷方式 rand or diff
    * @param  array arr  题目数目数组 按照一定格式传递过来 
    * $arr=array('radio'=>12,'multi'=>12,....); 二维数组 rand 
    * $arr=array('radio'=>array('easy'=>3,'medi'=>4,'diff'=>3), 'multi'...  ); 三维数组 diff
    * @return  array  $questions=array('radio'=>....) 返回格式一致
    */
    private function getPaperQuestion($course_id, $method, $arr ){
        
        $model = D('CourseQuestion');
        
        if($method=='rand'){
            unset($map);
            $map=array('course_id'=>$course_id, 'type'=>'radio');
            $radio = $model->where($map)->limit($arr['radio'])->select();//据说大数据量时效率低下 后期优化
            $map['type']='multi';
            $multi = $model->where($map)->limit($arr['multi'])->select();
            $map['type']='check';
            $check = $model->where($map)->limit($arr['check'])->select();
            $map['type']='fill';
            $fill = $model->where($map)->limit($arr['fill'])->select();
            $map['type']='prog';
            $prog =$model->where($map)->limit($arr['prog'])->select();
            unset($map);
            
            $questions =array(
                'radio'=>$radio,
                'multi'=>$multi,
                'check'=>$check,
                'fill' =>$fill ,
                'prog' =>$prog ,
            );
            
        }elseif($method=='diff'){
            unset($map);
            $map=array('course_id'=>$course_id, 'type'=>'radio', 'difficulty'=>'easy');
            $radio_easy = $model->where($map)->limit($arr['radio']['easy'])->select();
            $map['difficulty']='normal';
            $radio_medi = $model->where($map)->limit($arr['radio']['medi'])->select();
            $map['difficulty']='hard';
            $radio_hard =$model->where($map)->limit($arr['radio']['hard'])->select();
            
            unset($map);
            $map=array('course_id'=>$course_id, 'type'=>'multi', 'difficulty'=>'easy');
            $multi_easy = $model->where($map)->limit($arr['multi']['easy'])->select();
            $map['difficulty']='normal';
            $multi_medi = $model->where($map)->limit($arr['multi']['medi'])->select();
            $map['difficulty']='hard';
            $multi_hard = $model->where($map)->limit($arr['multi']['hard'])->select();
            
            unset($map);
            $map=array('course_id'=>$course_id, 'type'=>'check', 'difficulty'=>'easy');
            $check_easy = $model->where($map)->limit($arr['check']['easy'])->select();
            $map['difficulty']='normal';
            $check_medi = $model->where($map)->limit($arr['check']['medi'])->select();
            $map['difficulty']='hard';
            $check_hard = $model->where($map)->limit($arr['check']['hard'])->select();
            
            unset($map);
            $map=array('course_id'=>$course_id, 'type'=>'fill', 'difficulty'=>'easy');
            $fill_easy = $model->where($map)->limit($arr['fill']['easy'])->select();
            $map['difficulty']='normal';
            $fill_medi = $model->where($map)->limit($arr['fill']['medi'])->select();
            $map['difficulty']='hard';
            $fill_hard = $model->where($map)->limit($arr['fill']['hard'])->select();
            
            unset($map);
            $map=array('course_id'=>$course_id, 'type'=>'prog', 'difficulty'=>'easy');
            $prog_easy = $model->where($map)->limit($arr['prog']['easy'])->select();
            $map['difficulty']='normal';
            $prog_medi = $model->where($map)->limit($arr['prog']['medi'])->select();
            $map['difficulty']='hard';
            $prog_hard = $model->where($map)->limit($arr['prog']['hard'])->select();
            
            $questions =array(
                'radio'=>array_merge($radio_easy, $radio_medi, $radio_hard),
                'multi'=>array_merge($multi_easy, $multi_medi, $multi_hard),
                'check'=>array_merge($check_easy, $check_medi, $check_hard),
                'fill' =>array_merge($fill_easy , $fill_medi , $fill_hard ),
                'prog' =>array_merge($prog_easy , $prog_medi , $prog_hard),
            );
            
        }
        
        //将原生态question写入session 数据过大问题导致被destroy
        //session("exam_paper_questions",$questions);
        S('exam_paper_questions', $questions, 3600); //使用缓存 
        
        return $this->formatMetas($questions);      //处理metas 返回数据
    }
    
    /**  
    * 获取试卷各类型题目数目
    * 
    * @access private
    * @param  array paper 试卷信息
    * @return  arr 
    */
    private function getPaperCatNum($paper){
        
        $exam_categ_set = $paper['exam_categ_setting']; 
        $exam_categ_set_arr = json_decode($exam_categ_set, true);
        $arr = array(
            'radio'=>$exam_categ_set_arr['radio'][0],
            'multi'=>$exam_categ_set_arr['multi'][0],
            'check'=>$exam_categ_set_arr['check'][0],
            'fill' =>$exam_categ_set_arr['fill'][0],
            'prog' =>$exam_categ_set_arr['prog'][0],
        );
        return $arr;
    }
    
    /**  
    * 获取试卷各类型题目分值
    * 
    * @access private
    * @param array paper 试卷信息
    * @return  arr
    */
    private function getPaperCatUnit($paper){
        $exam_categ_set = $paper['exam_categ_setting']; 
        $exam_categ_set_arr = json_decode($exam_categ_set, true);
        $arr = array(
            'radio'=>$exam_categ_set_arr['radio'][1],
            'multi'=>$exam_categ_set_arr['multi'][1],
            'check'=>$exam_categ_set_arr['check'][1],
            'fill' =>$exam_categ_set_arr['fill'][1],
            'prog' =>$exam_categ_set_arr['prog'][1],
        );
        return $arr;
    }
    
    /**  
    * 处理metas
    * 1.将选择题选项数组化 2.将html代码解码 3.将填空题替换为输入框 
    * 4. 5. 6.
    * @access private
    * @param  array questions 题目信息  $questions=array('radio'=>  )   
    * @return  arr  questions
    */
    private function formatMetas($questions){
        
        foreach($questions['radio'] as &$val){
            $val['metas'] = json_decode($val['metas'] , true);
            for($i=0;$i < count($val['metas']) ;$i++){
                $val['metas'][$i]= htmlspecialchars_decode($val['metas'][$i]);
            }
        }

        foreach($questions['fill'] as &$val){
            $val['stem'] =preg_replace("/\(\(answer\)\)/", "<input type='text' name='ans[".$val['id']."]'/>", $val['stem']);
            $val['metas'] = json_decode($val['metas'] , true);
            for($i=0;$i < count($val['metas']) ;$i++){
                $val['metas'][$i]= htmlspecialchars_decode($val['metas'][$i]);
            }
        }
        
        foreach($questions['prog'] as &$val){
            $val['metas'] = json_decode($val['metas'] , true);
            $val['metas'] = str_replace("\r\n","<br/>", $val['metas']);
        }
        
        return $questions;
    }
    
    
    /**  
    * 考试交卷
    * 
    * @access public
    * @param  int paper_id 试卷id
    * @return  
    */
    public function submit(){
        if(IS_POST){
            $post =I('post.');
            //dump($post );die;
            session('myanswer',$post);//原生post数据


            //保存记录
            $data = array(
                    'answer'    =>  json_encode($post, JSON_UNESCAPED_UNICODE),
                    'post_time' =>  time(),
                    'user_id'   =>  session('uid'),
                    'paper_id'  =>  $post['paper_id'],
                    'has_reviewed'=>0,
                );
             
            $check = M('CoursePaperTest')->where(array('paper_id'=>$post['paper_id'], 'user_id'=>session('uid'))->find();//检查是否已经存在 两次考试 ？ 
            if($check){
                M('CoursePaperTest')->where(array('paper_id'=>$post['paper_id'], 'user_id'=>session('uid'))->save($data);
            }else{
                M('CoursePaperTest')->add($data);
            }

            $paper_id = $post['paper_id'];

            //检查设置 是否允许立即批阅 一般来说 自动组卷类型试卷 系统可以自动批阅 手工组卷类型 教师批阅
            #code here 
            
            $this->result($post, $paper_id);
        }
    }
    
    /**  
    * 显示结果
    * 
    * @access public
    * @param  int paper_id 试卷id
    * @return  
    */
    public function result($post, $paper_id){
        $score = $this->calculate($post, $paper_id);
        $data  =  array('score'=>$score, 'has_reviewed'=>1);
        M('CoursePaperTest')->where(array('paper_id'=>$paper_id, 'user_id'=>session('uid'))->setField($data);
        $this->success('你的成绩为:'.$score, U('Home/Paper/details',array('pid'=>$paper_id )), 6);
    }
    
    
    
    
    /**  
    * 试卷结果处理
    * 
    * @access private
    * @param  data 提交的数据
    * @param int paper_id 
    * @return  
    */
    
    private function calculate($data, $paper_id){
                
      $model = M('CourseQuestion');
      
        //进行答案对比  并对每个类型题目数目统计
        $radio_right_num=0;
        $radio_wrong_num=0;
        $multi_right_num=0;
        $multi_wrong_num=0;
        $check_right_num=0;
        $check_wrong_num=0;
        $fill_right_num=0;
        $fill_wrong_num=0;
        $prog_right_num=0;
        $prog_wrong_num=0;
        
        foreach($data['ans'] as $key=> $val){
          $answer = $model->field("answer,type")->find($key);
          $model->where('id='.$key)->setInc('finished_times', 1);//题目完成次数+1
             $type = $answer['type'];
             switch($type){
                    case 'radio':
                            if( $val==$answer['answer'] ){
                                $res[$key]['res'] = 'yes';
                                $passed = true;
                                $radio_right_num ++;
                            }else{
                                $res[$key]['res'] = 'no';
                                $radio_wrong_num ++;
                            }
                    break;
                    case 'multi':
                            if( $val==$answer['answer'] ){
                                $res[$key]['res'] = 'yes';
                                $passed = true;
                                $multi_right_num ++;
                            }else{
                                $res[$key]['res'] = 'no';
                                $multi_wrong_num ++;
                            }
                    break;
                    case 'check':
                            if($val==$answer['answer'] ){
                                $res[$key]['res'] = 'yes';
                                $passed = true;
                                $check_right_num ++;
                            }else{
                                $res[$key]['res'] = 'no';
                                $check_wrong_num ++;
                            }
                    break;
                    case 'fill':
                            if($val==$answer['answer']){
                                $res[$key]['res'] = 'yes';
                                $passed = true;
                                $fill_right_num ++;
                            }else{
                                    $res[$key]['res'] = 'no';
                                    $passed = true;
                                $fill_wrong_num ++;
                            }
                    break;
                    case 'prog':
                            $qid = $key;
                            $source = $val;
                            $sid = A('Course/Oj')->ojSubmitAns($qid, $source);
                            sleep(2);
                            $result = A('Course/Oj')->checkResult($sid);
                            if($result==true){
                                $res[$key]['res'] = 'yes';
                                $passed = true;
                                $prog_right_num ++;
                            }else{
                                    $res[$key]['res'] = 'no';
                                $prog_wrong_num ++;
                            }
                    break;
                }
              
              if($passed == true){
                $model->where('id='.$key)->setInc('passed_times', 1); //题目通过次数+1
              }
       }
       session('myresult',$res);

       //保存记录
       $result = json_encode($res);
       M('CoursePaperTest')->where(array('paper_id'=>$paper_id, 'user_id'=>session('uid'))->setField('result', $result);

      
    

      
       
      //总分数统计 sum() 每个类型答对题目数*每个类型每题分值 
       
        $paper =  D('CoursePaper')->find($paper_id);
            
        $unitsarr = $this->getPaperCatUnit($paper);//各个类型题目 每题分值
        
        $radio_score = $unitsarr['radio'] * $radio_right_num;//详细统计 后面可做统计图 分布图
        $multi_score = $unitsarr['multi'] * $multi_right_num;
        $check_score = $unitsarr['check'] * $check_right_num;
        $fill_score  = $unitsarr['fill']  * $fill_right_num ;
        $prog_score  = $unitsarr['prog']  * $prog_right_num ;
        
        
        $score = $radio_score + $multi_score + $check_score + $fill_score + $prog_score;
    
        return $score;
        
    }
    
    
    
    /**  
    * 显示正确结果及解析
    * 
    * @access public
    * @param paper_id 
    * @return  
    */
    public function details(){
        $paper_id =I('get.pid');
        $paper = D('CoursePaper')->find($paper_id);
        $paper['time_limit'] = explode(',', $paper['time_limit']);
        $numarr  = $this->getPaperCatNum($paper);
        $unitarr = $this->getPaperCatUnit($paper);
        
        //$questions =  session("exam_paper_questions");//原生态session
        $questions =  S("exam_paper_questions");//缓存
        //dump($questions);die;
        //$myanswer  =  session('myanswer');
        if(empty($questions)) $this->error('非法访问');
        
        //处理metas 
        foreach($questions['radio'] as &$val){
            $val['metas'] = json_decode($val['metas'] , true);
            $val['answer'] = $this->chartonum($val['answer'] ) ;
            for($i=0;$i < count($val['metas']) ;$i++){
                $val['metas'][$i]= htmlspecialchars_decode($val['metas'][$i]);
            }
        }
        
        foreach($questions['fill'] as &$val){
            $val['stem'] =preg_replace("/\(\(answer\)\)/", "<input type='text' name='ans[".$val['id']."]' value='".$val['answer']."' />", $val['stem']);
        }
        
        foreach($questions['prog'] as &$val){
            $val['metas'] = json_decode($val['metas'] , true);
        }
        
        
        
    
        
        $myresult = session('myresult');
        $myanswer = session('myanswer');
        //dump(session('myresult'));die;
        //dump($questions);die;
        $assign = array(
            'exam' => $questions,
            'paper'=>$paper,
            'numarr' => $numarr,
            'unitarr'=> $unitarr,
            'myresult'=> $myresult,
            'myanswer'=>$myanswer,
        );
        
        $this->assign($assign);
        $this->display();
    }
    
    
     /**  
    * 获取该试卷里问题id 
    * 通过session 或者知道数组获取
    * @access private
    * @param array arr 问题数组  
    * @return  
    */
    private function getQuestionId($arr){
        foreach($arr['radio'] as $val){
            $qid[] = $val['id'];
        }
        foreach($arr['multi'] as $val){
            $qid[] = $val['id'];
        }
        foreach($arr['check'] as $val){
            $qid[] = $val['id'];
        }
        foreach($arr['fill'] as $val){
            $qid[] = $val['id'];
        }
        foreach($arr['prog'] as $val){
            $qid[] = $val['id'];
        }
        return $qid;
        
    }
    
    
    private function chartonum($char){
        switch($char){
            case 'A':
            return 1;
            case 'B':
            return 2;
            case 'C':
            return 3;
            case 'D':
            return 4;
        }
    }
 
    
 
}
