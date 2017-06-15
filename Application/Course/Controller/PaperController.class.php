<?php
namespace Course\Controller;
use Think\Controller;

class PaperController extends CommonController {
    
    
    /**  
     * 试卷列表
     * @access public 
     * @param  
     * @return  
     */
    public function index(){
        $pre = C('DB_PREFIX');
        A('Teacher/Navbar')->navbar();
        $map=array('user_id'=>session('uid'),);
        $papers = D('CoursePaper') ->alias('t1')
                                    ->field('t1.*,t2.title as course_title')
                                    ->join("{$pre}course as t2 on t1.course_id = t2.id")
                                    ->where($map)
                                    ->select();
      
                                                    
        $this->assign('papers',$papers);        
        $this->display();
    }
    
    /*---------试卷相应处理---------*/
    
     
    /**  
     * 添加试卷
     * @access public 
     * @param  
     * @return  
     */
    public function add(){
        if(IS_POST){
            $post=I('post.');
            $model = D('CoursePaper');
            if(!$model->create())  $this->error($model->getError(),'',1);

            $post['created_time']   =   time();
            $post['user_id']        =   session('uid');
            $post['paper_start_date']   = strtotime($post['paper_start_date']);
            $post['paper_end_date']     = strtotime($post['paper_end_date']);
            //dump($post);die;
            $result = $model->addData($post);
            if($result){
                session('paper_id',$result);
                $post['type'] == 'hand' ? $this->success('创建成功,请继续设置',U('Course/Paper/hand'),1) : $this->success('创建成功 请继续设置',U('Course/Paper/auto'),1);
            }else{
                $this->error('创建失败');
            }
        }else{
            $map=array(
                'teacher_id'=>session('uid'),
                'status'=>array('in','closed,published'),
            );
            $course = M('Course')->field('id,title')->where($map)->select();  
            $this->assign('course',$course);
            $this->display();
        }
    }
    
    /**  
     * 删除试卷
     * @access public 
     * @param  
    * @return  
     */
    public function delete(){
        $paper_id=I('post.id');
        if(!is_numeric($paper_id)) $this->ajaxReturn(false);  
        $bool = M('CoursePaper')->where('id='.$paper_id)->delete();
        $this->ajaxReturn($bool);
        //return $bool;
        
    }
    
    
    
    
    /**  
     * 系统组卷 创建试卷 更新试卷
     * @access public 
     * @param  
     * @return  
     */
    public function auto(){
        if(IS_POST){
            $post=I('post.');
            $map=array(
                'id'=>$post['id'],
                'user_id'=>session('uid'),
            );
            //dump($post);die;
            //数据处理  
            $post['time_limit'] = $post['time_limit_num']. ',' .$post['time_limit_unit'];
            $post['exam_categ_setting'] =array(
                'radio'=>array($post['er-radio-num'], $post['er-radio-unit']),
                'multi'=>array($post['er-multi-num'], $post['er-multi-unit']),
                'check'=>array($post['er-check-num'], $post['er-check-unit']),
                'fill'=> array($post['er-fill-num'], $post['er-fill-unit']),
                'prog'=> array($post['er-prog-num'], $post['er-prog-unit']),
            );
            $post['exam_categ_setting'] = json_encode($post['exam_categ_setting']);
            
            if($post['gener_method']=='difficulty'){
                $post['diff_setting'] = $post['diff-easy'].','.$post['diff-medi'].','.$post['diff-hard'];
            }elseif($post['gener_method']=='random'){
                $post['diff_setting'] ='';//随机模式下 置空
            }
        
            
            //销毁不需要变量
            unset($post['id'], $post['time_limit_num'], $post['time_limit_unit'], $post['diff-easy'], $post['diff-medi'], $post['diff-hard'], 
                $post['er-radio-num'], $post['er-radio-unit'], 
                $post['er-multi-num'], $post['er-multi-unit'],
                $post['er-check-num'], $post['er-check-unit'],
                $post['er-fill-num'],  $post['er-fill-unit'],
                $post['er-prog-num'],  $post['er-prog-unit']
            );
            //dump($post);die;
            $result =   D('CoursePaper')->editData($map,$post); 
            $result === false ?  $this->error('保存失败','',2) : $this->success('保存成功','',1);
            
        }else{
            if( empty(session('paper_id')) ){
                $this->error('请先添加试卷',U('Course/Paper/add'),2);
            }
            $id=session('paper_id');
            $data = $this->getPaper($id);
            //dump($data);die;
            $this->assign('data',$data);
            $this->display('auto');
        }

    }


    /**  
     * 手工组卷 创建试卷  更新试卷
     * @access public 
     * @param  
     * @return  
     */
    public function hand(){
        if(IS_POST){
            $post = I('post.');
            $map =array(
                'id'        =>  $post['id'],
                'user_id'   =>  session('uid'),
            );
            //数据处理  
            $post['time_limit'] = $post['time_limit_num']. ',' .$post['time_limit_unit'];

            $questions_list_old = M('CoursePaper')->find($post['id'])['questions_list'];

    


            if( $post['question_chose'] && $questions_list_old ){

                $post['questions_list'] = implode(',', array_unique(explode(',', $questions_list_old.','.$post['question_chose'] ) ) );

             }elseif( !$questions_list_old && $post['question_chose']){

                $post['questions_list'] = implode(',', array_unique(explode(',', $post['question_chose'] ) ) );

             }else{

                $post['questions_list']  = $questions_list_old;
             }


            $post['questions_list'] = implode(',', array_unique(explode(',', $questions_list_tmp) ) );

            //dump($post);die;
            $result =   D('CoursePaper')->editData($map,$post);
            $result === false ?  $this->error('保存失败','',2) : $this->success('保存成功','',1);
            
        }else{

            if( empty(session('paper_id')) )  $this->error('请先添加试卷',U('Course/Paper/add'),2);
            $id     =   session('paper_id');
            $data   =   $this->getPaper($id); //该分试卷信息
            $data['questions_list'] = explode(',', $data['questions_list']);
            
            $map = array(
                'id'=> array('in',$data['questions_list']),
                'user_id'=>session('uid'),
            );

            $questions = D('CourseQuestion')->field('id, type, stem')->where($map)->select(); //已经选择的题目
            unset($map);
            $map = array(
                    'course_id' =>  $data['course_id'],
                    'user_id'   =>  session('uid'),
                    'id'        =>  array('not in',$data['questions_list']), //排除已经选择的题目
                );
            $questions_chose = D('CourseQuestion')->field('id,stem')->where($map)->select();
            foreach($questions as &$question){
                $question['type'] = A('Course/Question')->questype($question['type']);
            }
            
            //dump($questions);die;
            $this->assign('data',$data);
            $this->assign('questions',$questions);
            $this->display('hand');
        }
    }   
    
    public function delQuestion(){
        if(IS_POST){
            $post           =  I('post.');
            $questions_list = M('CoursePaper')->find($post['paperid'])['questions_list'];
            $arr            = explode(',', $questions_list);
            $key            = array_search($post['questionid'], $arr);
            unset($arr[$key]);
            array_filter($arr);
            $data['questions_list']  = implode(',', $arr);
            $map            = array('id'=>$post['paperid']);
            $result         = D('CoursePaper')->editData($map,$data);
            if($result)     $this->ajaxReturn('删除成功');else $this->ajaxReturn('删除失败');
            

        }
    }


    /**  
     * 编辑试卷
     * @access public 
     * @param  
     * @return  
     */
    public function edit(){

        $paper_id = I('get.id');
        if(empty($paper_id) || !is_numeric($paper_id) || !M('CoursePaper')->find($paper_id) ) $this->error('不存在该试卷','',2);
        session('paper_id',$paper_id);
        D('CoursePaper')->find($paper_id)['type'] === 'hand' ? $this->hand() : $this->auto();
    }



    /**  
     * 试卷批阅列表 
     * @access public 
     * @param  id test id
     * @return  
     */
    public function tests(){
        $pre = C('DB_PREFIX');
        $tests  = M('CoursePaperTest')->alias('t1')
                                        ->join("left join {$pre}course_paper as t2 on t2.id=t1.paper_id")
                                        ->join("left join {$pre}course as t3 on t2.course_id=t3.id")
                                        ->where(array('t3.teacher_id'=>session('uid')))->select();
        $assign = array(
                'paper' =>  $paper,
                'tests' =>  $tests,

            );
        $this->assign($assign);
        $this->display();
    }

    /**  
     * 试卷批阅 主观题批阅 
     * 客观题是否也允许教师批阅(填空题 程序题) ? 
     * @access public 
     * @param  id test id
     * @return  
     */
    public function review($id){
        if(empty($id) || !is_numeric($id) || !M('CoursePaperTest')->find($id)) $this->error('没有找到该试卷','',2);

        $paper  = M('CoursePaper')->find($id);
        $tests  = M('CoursePaperTest')->where(array('paper_id'=>$id))->select();
        $assign = array(
                'paper' =>  $paper,
                'tests' =>  $tests,

            );
        $this->assign($assign);
        $this->display();
    }

 
    
    
    
    /**  
        * 获取一份试卷信息
        * 
        * @access private 
        * @param  int $paper_id 试卷id
        * @return  array 
        */
    private function getPaper($paper_id){
        
        $data = D('CoursePaper')->find($paper_id);  
        //处理数据
        if(!empty($data['time_limit'])){
            $data['time_limit'] = explode(',',$data['time_limit']);
            $data['time_limit_num']=$data['time_limit'][0];
            $data['time_limit_unit']=$data['time_limit'][1];
        }else{
            $data['time_limit_num']='0';
            $data['time_limit_unit']='m';
        }
        
        if(!empty($data['diff_setting'])){
            $data['diff_setting'] = explode(',',$data['diff_setting']);
            $data['diff-easy']=$data['diff_setting'][0];
            $data['diff-medi']=$data['diff_setting'][1];
            $data['diff-hard']=$data['diff_setting'][2];
            $data['diff_chose']='diff';//标志 选择 按照难易程度组织题目
        }else{
            $data['diff-easy']=0;
            $data['diff-medi']=0;
            $data['diff-hard']=0;
            $data['diff_chose']='random';//标志 选择 
        }
        
        
        if(!empty($data['exam_categ_setting'])){
            $data['exam_categ_setting'] = json_decode($data['exam_categ_setting'], true);
            $data['er-radio-num'] =$data['exam_categ_setting']['radio'][0];
            $data['er-radio-unit']=$data['exam_categ_setting']['radio'][1];
            $data['er-multi-num'] =$data['exam_categ_setting']['multi'][0];
            $data['er-multi-unit']=$data['exam_categ_setting']['multi'][1];
            $data['er-check-num'] =$data['exam_categ_setting']['check'][0];
            $data['er-check-unit']=$data['exam_categ_setting']['check'][1];
            $data['er-fill-num'] = $data['exam_categ_setting']['fill'][0];
            $data['er-fill-unit']= $data['exam_categ_setting']['fill'][1];
            $data['er-prog-num'] = $data['exam_categ_setting']['prog'][0];
            $data['er-prog-unit']= $data['exam_categ_setting']['prog'][1];
        }else{
            $data['er-radio-num']=0;
            $data['er-radio-unit']=0;
            $data['er-multi-num']=0;
            $data['er-multi-unit']=0;
            $data['er-check-num']=0;
            $data['er-check-unit']=0;
            $data['er-fill-num']=0;
            $data['er-fill-unit']=0;
            $data['er-prog-num']=0;
            $data['er-prog-unit']=0;
        }
        
        return $data;
        
    }


 
    
    





    
}