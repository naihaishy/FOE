<?php
namespace Course\Controller;
use Think\Controller;

class QuestionController extends CommonController {
    
    /**  
     * 题目列表
     * @access public 
     * @param  
     * @return  
     */
    public function index(){
        $pre = C('DB_PREFIX');
        A('Teacher/Navbar')->navbar();
        $map = array('user_id'=>session('uid'));
        
        /*-----分页显示-----*/
        $model = D('CourseQuestion');
        $count = $model->where($map)->count();
        $page = new \Think\Page($count,8);
        $page->rollPage   = 6;
        $page->lastSuffix = false; 
        $page->setConfig('header',' 条记录');
        $page->setConfig('prev','上一页');
        $page->setConfig('next','下一页');
        $page->setConfig('first','首页');
        $page->setConfig('last','末页');
        $page->setConfig('theme','%TOTAL_ROW%  %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页 %FIRST%  %UP_PAGE%   %LINK_PAGE%  %DOWN_PAGE%  %END%');
        $show = $page->show();


        $this->assign('show',$show);    //分页输出链接
    
        $questions = D('CourseQuestion')->alias('t1')
                                        ->field('t1.*,t2.title as course_title')
                                        ->join("{$pre}course as t2 on t1.course_id = t2.id")
                                        ->where($map)
                                        ->limit($page->firstRow,$page->listRows) 
                                        ->select();
                                                                        
        unset($map);
        $map=array(
            'user_id'=>session('uid'),
            'course_id'=>0,
            );
        $unusequestions=D('CourseQuestion')->where($map)->select();
        
        foreach ($questions as &$val ){
            $val['type'] = $this->questype($val['type']);
        }
        
        foreach ($unusequestions as &$val ){
            $val['type'] = $this->questype($val['type']);
        }
        
        //dump($questions);die;
        
            
            $this->assign('questions',$questions);
            //dump($questions);die;
            $this->assign('unusequestions',$unusequestions);
            $this->display();
    }
    
    
    
    
    public function add(){
        $type=I('get.type');
        if(!empty($type)){
             switch($type){
                case 'chose' :
                    $this->addchose();
                break;
                case 'fill' :
                    $this->addfill();
                break;
                case 'check' :
                    $this->addcheck();
                break;
                case 'prog' :
                    $this->addprog();
                break;
                default:
                    $this->error('暂不支持此类型题目');
                break;  
             }
        }else{
            $this->display();
        }
    }
    
    
    
    /*----------题目添加函数-----------*/
    
    
    /**  
     * 添加选择题  
     * 
     * @access private
     * @param  
     * @return  
     */
    private function addchose(){
        if(IS_POST){
            $post=I('post.');
            
            $this->validateChose();
            
            //数据处理
            if( count(explode(',', $post['answer'])) > 1){
                $post['type'] = 'multi'; //多个选项
            }else{
                $post['type'] = 'radio'; //单选
            }
        
            $post['user_id'] = session('uid');
            $post['created_time'] = time();
            $post['metas'] = json_encode($post['metas'], JSON_UNESCAPED_UNICODE);
            $result = D('CourseQuestion')->addData($post);
            $result ? $this->success('添加成功','',2) : $this->error('添加失败','',2);
            
        }else{
            $course = $this->getCourse();
            $this->assign('course',$course);
            $this->display('addchose');
        }
        
    }
    
    /**  
     * 添加填空题
     * 
     * @access private
     * @param  
     * @return  
     */
    private function addfill(){
        if(IS_POST){
            $post=I('post.');
            
            $model=D('CourseQuestion');
            if(!$model->create()){ $this->error($model->getError(),'',1);   }
            
            //数据处理
            $post['type'] = 'fill';
            $post['user_id'] = session('uid');
            $post['created_time'] = time();
            $start =stripos($post['stem'], '((')+2;
            $length= stripos($post['stem'],'))') - stripos($post['stem'], '((')-2;
            $post['answer']=substr($post['stem'], $start , $length );//提取答案
            if(empty($post['answer'])){
                $this->error('答案不得为空','',1);
            }
            $post['stem'] =  preg_replace('/\(\(.+\)\)/','((answer))', $post['stem']);//替换题干中双括号中答案内容为answer

            $result = D('CourseQuestion')->addData($post);
            $result ? $this->success('添加成功','',2) : $this->error('添加失败','',2);
            
        }else{
            $course = $this->getCourse();
            $this->assign('course',$course);
            $this->display('addfill');
        }
        
    }
    
    /**  
     * 添加判断题
     * 
     * @access private
     * @param  
     * @return  
     */  
    private function addcheck(){
        
        if(IS_POST){
            $post=I('post.');
            $this->validateCheck();
            
            //数据处理
            $post['type'] = 'check';
            $post['user_id'] = session('uid');
            $post['created_time'] = time();
        
            $result = D('CourseQuestion')->addData($post);
            $result ? $this->success('添加成功','',2) : $this->error('添加失败','',2);
            
        }else{
            $course = $this->getCourse();
            $this->assign('course',$course);
            $this->display('addcheck');
        }
        
    }
    
    /**  
     * 程序的判断使用内部OJ系统模块
     * 将正常题目信息存入 question 表 同时在oj系统记录数据
     * @access private
     * @param  
     * @return  
     */ 
    private function addprog(){
        if(IS_POST){
            $post=I('post.');
            //动态验证
            $this->validateProg();
            
            $post['stem']       = htmlspecialchars_decode($post['stem']);
            $post['input']      = htmlspecialchars_decode($post['input']);
            $post['output']     = htmlspecialchars_decode($post['output']);
            $post['hints']      = htmlspecialchars_decode($post['hints']);
            $post['analysis']   = htmlspecialchars_decode($post['analysis']);
            $post['answer']     = htmlspecialchars_decode($post['answer']);
            $post['memory_limit'] = intval($post['memory_limit']);
            $post['time_limit']   = intval($post['time_limit'])  ;
            $post['sample_input']   = trim($post['sample_input']);
            $post['sample_output']  = trim($post['sample_output']);
            
            
            //将题目录入到oj系统
            $data = $post;
            $data['title']= $post['title'];
            $data['description'] = $post['stem'];
            $data['in_date'] = date('Y-m-d H:i:s', time() );
            $pid = M('OjProblem')->add($data); //返回oj_problem主键 problem_id 
            if($pid){
                //生成测试文件 sample.in sample.out
                A('Oj')->createSampleData(trim($pid), $post['sample_input'], $post['sample_output']);
                
                
                //数据处理
                $post['type'] = 'prog';
                $post['user_id'] = session('uid');
                $post['created_time'] = time();

                //metas处理
                $post['metas'] = array(
                    'title'                 =>  $post['title'],
                    'input'                 =>  $post['input'],
                    'output'                =>  $post['output'],
                    'sample_input'  =>  $post['sample_input'],
                    'sample_output' =>  $post['sample_output'],
                    'hints'                 =>  $post['hints'],
                    'memory_limit'  =>  $post['memory_limit'],
                    'time_limit'        =>  $post['time_limit'],
                    'oj'                        =>  true,//oj系统标志
                    'ojpid'                 =>  $pid,
                );
                $post['metas'] = json_encode($post['metas'], JSON_UNESCAPED_UNICODE);
                $result = D('CourseQuestion')->addData($post);
                $result ? $this->success('添加成功','',1) : $this->error('添加失败','',2);
                
            }
        }else{
                $course = $this->getCourse();
                $this->assign('course',$course);
                $this->display('addprog');
            }
    }

    
    
    private function getCourse(){
        $map =array(
            'user_id'=>session('uid'),
            'status'=>array('in','closed,published'),   
        );
        $course = D('Course')->field('id,title')->where($map)->select();
        return $course;
    }
    
    
    public function questype($type){
        if(empty($type)){
            return false;
        }
        switch($type){
            case 'chose':
                return '选择题';
            case 'radio':
                return '单选题';
            case 'multi':
                return '多选题';
            case 'fill':
                return '填空题';
            case 'check':
                return '判断题';
            case 'prog':
                return '程序题';
        }
    }
    
    
    /**  
        * Ajax 获取一个题目
        * @access public
        * @param  
        * @return  
        */ 
    public function getOneQuestion(){
        if(IS_POST){
            $qid  = I('post.pid');
            $info = D('CourseQuestion')->field('')->find($qid);
            $this->ajaxReturn($info);
        }
    }
    
    
    /*----------题目更新函数--------*/
    /**  
        * 更新题目
        * @access public
        * @param  
        * @return  
        */
    public function edit(){
        $qid = I('get.qid');
        $type = I('get.type');
        if(empty($type)) $type = D('CourseQuestion')->field('type')->find($qid)['type'];
        
        if(empty($qid)) exit;
        switch($type){
            case 'chose' :
                $this->editchose($qid);
            break;
            case 'radio' :
                $this->editchose($qid);
            break;
            case 'multi' :
                $this->editchose($qid);
            break;
            
            case 'fill' :
                $this->editfill($qid);
            break;
            case 'check' :
                $this->editcheck($qid);
            break;
            case 'prog' :
                $this->editprog($qid);
            break;
            default:
                $this->error('暂不支持此类型题目');
            break;  
        }
        
    }
    
    /**  
        * 更新选择题
        * @access private
        * @param  qid int 题目id
        * @return  
        */
    private function editchose($qid){
        if(empty($qid)) exit;
        if(IS_POST){
            $post = I('post.');
            $this->validateChose();
            
            //数据处理
            if( count(explode(',', $post['answer'])) > 1){
                $post['type'] = 'multi'; 
            }else{
                $post['type'] = 'radio'; 
            }
            $post['metas'] = json_encode($post['metas'], JSON_UNESCAPED_UNICODE);
            $post['updated_time'] = time();
            $result = D('CourseQuestion')->where('id='.$qid)->save($post);
            $result ? $this->success('更新成功','',2) : $this->error('更新失败','',2);
        }else{
            $question = D('CourseQuestion')->find($qid);
            $question['metas'] = json_decode($question['metas'], true);
            $course = $this->getCourse();
            $this->assign('course',$course);
            $this->assign('question', $question);
            //dump($question);die;
            $this->display('editchose');
        }
    }
    
    /**  
        * 更新填空题
        * @access private
        * @param  
        * @return  
        */
    private function editfill($qid){
        if(empty($qid)) exit;
        if(IS_POST){
            $post = I('post.');
            $model = D('CourseQuestion');
            if(!$model->create()){ $this->error($model->getError(),'',1);   }
            
            //数据处理
            $post['updated_time'] = time();
            $start =stripos($post['stem'], '((')+2;
            $length= stripos($post['stem'],'))') - stripos($post['stem'], '((')-2;
            $post['answer'] = substr($post['stem'], $start , $length );//提取答案
            if(empty($post['answer'])){
                $this->error('答案不得为空','',1);
            }
            $post['stem'] =  preg_replace('/\(\(.+\)\)/','((answer))', $post['stem']);//替换题干中双括号中答案内容为answer
            
            $result = D('CourseQuestion')->where('id='.$qid)->save($post);
            $result ? $this->success('更新成功','',2) : $this->error('更新失败','',2);
        }else{
            $question = D('CourseQuestion')->find($qid);
            $question['stem'] =  preg_replace('((answer))', $question['answer'], $question['stem']);//替换题干中双括号中答案内容为answer
            $course = $this->getCourse();
            $this->assign('course',$course);
            $this->assign('question', $question);
            //dump($question);die;
            $this->display('editfill');
        }
        
        
    }
    
    /**  
        * 更新判断题
        * @access private
        * @param  
        * @return  
        */
    private function editcheck($qid){
        if(empty($qid)) exit;
        
        if(IS_POST){
            $post = I('post.');
            $this->validateCheck();
            
            //数据处理
            $post['updated_time'] = time();
            //dump($post);die;
            $result = D('CourseQuestion')->where('id='.$qid)->save($post);
            $result ? $this->success('更新成功','',2) : $this->error('更新失败','',2);
        }else{
            $question = D('CourseQuestion')->find($qid);
            $course = $this->getCourse();
            $this->assign('course',$course);
            $this->assign('question', $question);
            $this->display('editcheck');
        }
    }
    
    /**  
        * 更新程序题
        * @access private
        * @param  
        * @return  
        */
    private function editprog($qid){
        if(empty($qid)) exit;
        if(IS_POST){
            
            $post=I('post.');
            $this->validateProg();//动态验证
            
            $post['stem']       = htmlspecialchars_decode($post['stem']);
            $post['input']      = htmlspecialchars_decode($post['input']);
            $post['output']     = htmlspecialchars_decode($post['output']);
            $post['hints']      = htmlspecialchars_decode($post['hints']);
            $post['analysis']   = htmlspecialchars_decode($post['analysis']);
            $post['answer']     = htmlspecialchars_decode($post['answer']);
            $post['memory_limit'] = intval($post['memory_limit']);
            $post['time_limit']   = intval($post['time_limit'])  ;
            $post['sample_input']   = trim($post['sample_input']);
            $post['sample_output']  = trim($post['sample_output']);
            
            //将题目录入到oj系统
            $data = $post;
            $data['description'] = $post['stem'];
            $data['in_date'] = date('Y-m-d H:i:s', time());
            $pid = $post['ojpid'];
            
            $bool = M('OjProblem')->where('problem_id='.$pid)->save($data); //返回oj_problem主键 problem_id 
            if($bool){
                //更新测试文件 sample.in sample.out
                A('Oj')->updateSampleData(trim($pid), $post['sample_input'], $post['sample_output']);
                
                //数据处理

                //metas处理
                $post['metas'] = array(
                    'title'                 =>  $post['title'],
                    'input'                 =>  $post['input'],
                    'output'                =>  $post['output'],
                    'sample_input'  =>  $post['sample_input'],
                    'sample_output' =>  $post['sample_output'],
                    'hints'                 =>  $post['hints'],
                    'memory_limit'  =>  $post['memory_limit'],
                    'time_limit'        =>  $post['time_limit'],
                    'oj'                        =>  true,//oj系统标志
                    'ojpid'                 =>  $pid,
                );
                $post['metas'] = json_encode($post['metas'], JSON_UNESCAPED_UNICODE);
                $result = D('CourseQuestion')->where('id='.$qid)->save($post);
                $result ? $this->success('更新成功','',2) : $this->error('更新失败','',2);
                
            }
        }else{
            $question = D('CourseQuestion')->find($qid);
            $question['metas'] = json_decode($question['metas'], true);
            $course = $this->getCourse();
            $this->assign('course',$course);
            $this->assign('question', $question);
            $this->display('editprog');
        }
    }


    /*---------动态验证函数-----------*/
    private function validateChose(){
        $rules = array(
             array('stem','require','题干必须有！'),
             array('answer','require','答案必须有！'),
            );
        $model = D('CourseQuestion');
        if(!$model->validate($rules)->create())  $this->error($model->getError(),'',1);
    }
    
    private function validateCheck(){
        $rules = array(
             array('stem','require','题干必须有！'),
             array('answer','require','答案必须有！'),
            );
        $model = D('CourseQuestion');
        if(!$model->validate($rules)->create())  $this->error($model->getError(),'',1);
    }
    private function validateProg(){
        $rules = array(
             array('title','require','标题必须有！'),
             array('stem','require','描述必须有！'),
            );
        $model = D('CourseQuestion');
        if(!$model->validate($rules)->create())  $this->error($model->getError(),'',1);
    }
    
    /**  
        * 删除题目
        * ajax(post)  url(get) 两种方式
        * @access public
        * @param  
        * @return  
        */
    public function delQuestion(){
        if(IS_POST){
            $qid = I('post.qid');
            $pid = A('Oj')->getOjPid($qid);
            if($pid) A('Oj')->delOjProblem($pid);
            $this->ajaxReturn( D('CourseQuestion')->delete($qid) );
        }elseif(IS_GET){
            $qid = I('get.qid');
            $pid = A('Oj')->getOjPid($qid);
            if($pid) A('Oj')->delOjProblem($pid);
            D('CourseQuestion')->delete($qid) ? $this->success('删除成功','',2) : $this->error('删除失败','',2);
        }
        
        
    }
    
    



    

    /**  
     * 题库flow
     * @access public
     * @param  
     * @return  
     */

    public function flows(){
        $course_id  = I('get.cid');
        if(empty($course_id) ||! is_numeric($course_id) || !M('Course')->find($course_id)) echo "<script>window.close();</script>" ;
        $course     = M('Course')->find($course_id);
        $questions  = M('CourseQuestion')->where('course_id='.$course_id)->select();

        foreach ($questions as $key => &$value) {
            $value['metas'] = json_decode($value['metas'], true);
            $value['type']  = $this->questype($value['type'] );
        }

        $this->assign('questions', $questions);
        $this->assign('course', $course);
        $this->display();
    }
    
 

    
}