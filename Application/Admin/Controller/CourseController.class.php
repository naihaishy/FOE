<?php
namespace Admin\Controller;
use Think\Controller;
class CourseController extends CommonController{


     
    /**  
     * 课程列表
     * @access public
     * @param  
     * @return
     */
    public function index(){
        $pre = C('DB_PREFIX');
        $model = M('Course');
        $page = A('Common/Pages')->getShowPage($model, array('checked'=>1) );
        $show = $page->show();
        $courses   = M('Course')->alias('t1')->field('t1.*,t2.username as teacher_name,t3.name as category_name')
                                        ->join("left join {$pre}teacher as t2 on t1.teacher_id = t2.id")
                                        ->join("left join {$pre}course_category as t3 on t1.category_id =t3.id")
                                        ->where(array('t1.checked'=>1))
                                        ->limit($page->firstRow,$page->listRows)
                                        ->order('t1.id asc')
                                        ->select();
        $assign =   array(
                'courses'  =>  $courses,
                'show'  =>  $show,
            );  

        $this->assign($assign);
        $this->display();
    }

    /**  
     * 审核课程
     * @access public
     * @param  
     * @return
     */
    public function check(){
        if(IS_POST){
            $post = I('post.');

            if($post['checked'] ==1){
                $data = array('checked'=>1, 'uncheck_reason'=>'','status'=>'published','release_date'=>time() );
            }else{
                if(!$post['uncheck_reason']) $this->error('需要填写未通过审核的原因');
                $data = array('checked'=>0, 'uncheck_reason'=>$post['uncheck_reason'], 'status'=>'closed' );
            }

            $result = M('Course')->where('id='.$post['id'])->save($data);

            //消息机制
            $course = M('Course')->find($post['id']);
            $source = array(
                    'title' => $course['title'],//课程标题
                    'url'   => 'https://foe.zhfsky.com/index.php/Course/Index/details/id/'.$course['id'],
                    'reason'=> $post['uncheck_reason'],
             );

            if(!$result===false ){
                //审核成功
                if($post['checked']==1) A('Common/Messages')->send('course', 'check_pass', $source, $course['teacher_id'], 2);
                else                    A('Common/Messages')->send('course', 'check_fail', $source, $course['teacher_id'], 2);
            }

            
            $result=== false ? $this->error('审核失败'):$this->success('审核成功');
        }else{
            $pre = C('DB_PREFIX');
            $model = M('Course');
            $page = A('Common/Pages')->getShowPage($model, array('checked'=>0 ) );
            $show = $page->show();
            $courses   = M('Course')->alias('t1')->field('t1.*,t2.username as teacher_name,t3.name as category_name')
                                            ->join("left join {$pre}teacher as t2 on t1.teacher_id = t2.id")
                                            ->join("left join {$pre}course_category as t3 on t1.category_id =t3.id")
                                            ->limit($page->firstRow,$page->listRows)
                                            ->where("checked=0")
                                            ->order('t1.id asc')
                                            ->select();
                                           
                                            
            $assign =   array(
                    'courses'  =>  $courses,
                    'show'  =>  $show,
                );  

            $this->assign($assign);
            $this->display();
            }
        
    }




    /**  
     * 关闭课程
     * @access public
     * @param  
     * @return
     */
    public function close($id){
        if(empty($id) || !is_numeric($id) || !M('Course')->find($id)) $this->error('不存在此课程'); 
        $result = M('Course')->where('id='.$id)->setField('status','closed');
        //消息机制
        $result ?  $this->success('关闭课程成功'):$this->error('关闭课程失败');
        if($result){
            $this->success('关闭课程成功', '', 1);
            //消息机制 源
            $course = M('Course')->find($id);
            $source = array(
                    'title' => $course['title'],//课程标题
                    'url'   => 'https://foe.zhfsky.com/index.php/Course/Index/details/id/'.$course['id'],
             );
            A('Common/Messages')->send('course', 'close', $source, $course['teacher_id'], 2);//消息机制 发送

        }else{
            $this->error('关闭课程失败', '', 1);
        }

    }


    /**  
     * 删除课程
     * @access public
     * @param   
     * @return   
     */
    public function delete($id){

      if(empty($id) || !is_numeric($id) || !M('Course')->find($id)) $this->error('不存在该课程');
      A('Common/Deletes')->doo('course', $id) ;

      if(!M('Course')->find($id)) $this->success('删除成功', '', 2);
      else $this->error('删除失败', '', 2);
    }
    
    
    
        
 




    /**  
     * 课程分类列表
     * @access public
     * @param  
     * @return
     */
    public function category(){
        $this->checkCategoryCount();
        $category   =   M('CourseCategory')->select();
        $this->assign('category', $category);
        $this->display();
    }

    /**  
     * 添加课程分类
     * @access public
     * @param  
     * @return
     */
    public function addCategory(){
        if(IS_POST){
            $post = I('post.');
            //自动验证提交数据
            $rules = array(
                 array('name','require','标题必须有！'),
            );
            $model = M('CourseCategory');
            if(!$model->validate($rules)->create())  $this->error($model->getError(),'',1);

            $result =   M('CourseCategory')->add($post);
            $result ? $this->success('添加成功','',1) : $this->error('添加失败','',2);
        }
    }


    /**  
     * 编辑课程分类
     * @access public
     * @param  
     * @return
     */
    public function editCategory(){
        
        if(IS_POST){
            $post = I('post.');
            //自动验证提交数据
            $rules = array(
                 array('name','require','标题必须有！'),
            );
            $model = M('CourseCategory');
            if(!$model->validate($rules)->create())  $this->error($model->getError(),'',1);
            $result =   M('CourseCategory')->save($post);
            $result ? $this->success('更新成功','',1) : $this->error('更新失败','',2);
        }
        
    }
    

    /**  
     * 删除课程分类
     * @access public
     * @param  
     * @return
     */
    public function delCategory($id){
        $result =   M('CourseCategory')->delete($id);
        //消息机制
        $result ? $this->success('删除成功','',1) : $this->error('删除失败','',2);
    }


    /**  
     * 检查课程分类总数
     * @access private
     * @param  
     * @return
     */
    private function checkCategoryCount(){
        $pre = C('DB_PREFIX');
        $count  =   M('Course')->alias('t1')
                            ->field('t2.id, count(*) as count ')
                            ->join("left join {$pre}course_category as t2 on t1.category_id=t2.id")
                            ->group('t2.id asc')
                            ->select();
        //M('Manual')
        foreach($count as $val){
            M('CourseCategory')->where('id='.$val['id'])->setField('count', $val['count']);
        }
        //return $count;
    }


}