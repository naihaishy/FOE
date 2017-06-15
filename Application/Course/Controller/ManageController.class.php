<?php
namespace Course\Controller;
use Think\Controller;



class ManageController extends CommonController{
    
    

    /**  
     * 课程列表index
     * @access public 
     * @param  
     * @return  
     */
    public function index(){
        A('Teacher/Navbar')->navbar();
        $this->getCourseList();
        $this->display();
    }
    
  
    
    
    /*----------课程相应操作---------*/
 
    /**  
     * 增加课程
     * @access public 
     * @param  
     * @return  
     */
    public function addCourse(){
        if(IS_POST and !empty(I('post.'))){
            $post=I('post.');
            $model=D('Course');
            if(!$model->create()){
                    $this->error($model->getError(),'',1);
            }
            $post['teacher_id']     = session('uid');
            $post['created_time']   = time();
            $post['course_start_date']  = strtotime($post['course_start_date']);
            $post['course_end_date']    = strtotime($post['course_end_date']);
            $post['exam_start_date']    = strtotime($post['exam_start_date']);
            $post['exam_end_date']      = strtotime($post['exam_end_date']);
            $post['query_results_start_date']   = strtotime($post['query_results_start_date']);
            $post['query_results_end_date']     = strtotime($post['query_results_end_date']);
            
            $result=$model->addData($post);
            if($result){
                //将新创建课程的id写入session
                session('course_id',$result);
                $this->success('创建成功',U('Manage/base'),2);
            }else{
                $this->error('创建失败');
            }
        }else{
            $category = D('Course')->getCourseCategory();
            $this->assign('category',$category);
            $this->display('addcourse');
        }
    }
    
    /**  
     * 修改课程
     * 
     * @access public 
     * @param  
     * @return  
     */
    public function editCourse(){
        if(IS_POST){
            $data=I('post.');
            $map=array('id'=>$data['course_id']);
            unset($data['course_id']);

            $data['course_start_date'] = strtotime($data['course_start_date']);
            $data['course_end_date'] = strtotime($data['course_end_date']);
            $data['exam_start_date'] = strtotime($data['exam_start_date']);
            $data['exam_end_date'] = strtotime($data['exam_end_date']);
            $data['query_results_start_date'] = strtotime($data['query_results_start_date']);
            $data['query_results_end_date'] = strtotime($data['query_results_end_date']);
            //dump($data);die;
          $result = D('Course')->editData($map,$data);
          if($result){
            $this->success('保存成功');
          }else{
            $this->error('保存失败');
          }
        }
    }
    
    
    
    //删除课程
    public function deleteCourse($cid){
        A('Common/Deletes')->doo('course', $cid); //真删除
        if(!M('Course')->find($cid)) $this->success('删除成功'); 
        else $this->error('删除失败');
        //$this->changeStatus('delete',$cid);  假删除
    }
    
    //关闭课程
    public function closeCourse($cid){          
        $this->changeStatus('close',$cid);  
    }
    
    //发布课程
    public function pulishCourse($cid){
        $this->changeStatus('pulish',$cid);      
    }
        
        
        
        
        
        
        
        
        
        
    /*-----------章节 课时相应操作---------*/
    
    /**  
    * 添加章节
    * 
    * @access public 
    * @param  
    * @return  
    */
    public function addChapter(){
        if(IS_POST){
            $post=I('post.');
            $model=D('CourseChapter');
            $post['created_time']=time();
            if(!$model->create()){
                 $this->error($model->getError(),'',1);exit;
            }
            $course_id=$this->getCourseId();
            $post['course_id']=$course_id;
            $result = $model->add($post);
            if($result){
                $this->success('添加成功');
            }else{
                $this->error('添加失败');
            }
        }
      
    }
        
    /**  
    * 删除章节
    * 
    * @access public 
    * @param  
    * @return  
    */
    public function delChapter(){
        if(IS_POST){
            $chapter_id=I('post.chid');
            if(!is_numeric($chapter_id) ) $this->ajaxReturn(false); 
            $bool = M('CourseChapter')->where('id='.$chapter_id)->delete();
            if($bool){
                //删除下面所有课时
                M('CourseLesson')->where('chapter_id='.$chapter_id)->delete();
            }
            $this->ajaxReturn($bool);
        }
    }
        
    /**  
     * 课时列表
     * 
     * @access public 
     * @param  
     * @return  
     */
    public function lesson(){
        $course_id = $this->getCourseId();
        $map=array('course_id'=>$course_id);
        $chapter=D('CourseChapter')->where($map)->select();
     
        foreach($chapter as &$val){
            $chapter_id= $val['id'];
            $lesson  = M('CourseLesson')->where('chapter_id='.$chapter_id)->select();
            $val['lesson']=$lesson;
        }
        //dump($chapter);die;
        
        unset($map);
        $map=array(
            'user_id'=>session('uid'),
            'course_id'=>array("in","0, $course_id"),
            'type'=>array('in','video/mp4,video/avi'),
        );

        $videofiles =   D('CourseFiles')->field('id,title')->where($map)->select();//课时文件 该课程下的所有课时文件 以及尚未被任何课程使用的文件
        $map['type']=   array('notin','video/mp4,video/avi');
        $materialfiles= D('CourseFiles')->field('id,title')->where($map)->select();//课时文件 该课程下的所有课时文件 以及尚未被任何课程使用的文件
        
        $papers = M('CoursePaper')->where(array('course_id'=>$course_id, 'staus'=>'open'))->select();
 
        $assign =   array(
                'videofiles'    =>  $videofiles,
                'materialfiles' =>  $materialfiles,
                'chapter'       =>  $chapter,
                'papers'        =>  $papers,
            ); 
        //dump($materialfiles);die;
        $this->assign($assign );
        $this->display();
    }
        
        
    /**  
    * 完善课时信息
    * 主要处理 lesson的 content media 等字段
    * @access public 
    * @param  
    * @return  
    */
    public function completeLesson(){
        if(IS_POST){
            if( empty(I('post.media_idb')) && empty(I('post.media_id')) && empty(I('post.content')) &&  empty(I('post.exercise_id'))  ) $this->error('失败'); //目前只支持本地文件 必须提交media_id 或者图文的 content  暂不支持网络视频uri
            
            $post=I('post.');
            if(!empty($post['media_id']) ){
                $post['media_id'] = $post['media_id'];
            }else{
                $post['media_id'] = $post['media_id'];
            }
            $post['updated_time']=time();
            $map=array('id'=>$post['lesson_id'] );
            
            //unset($post['media_ida'], $post['media_idb'], $post['lesson_id'] );
            //dump($post);dump($map);die;
            $result = D('CourseLesson')->where($map)->save($post);
            if($result){
                $this->success('保存成功','',1);
            }else{
                $this->error('保存失败','',1);
            }
        }
    }
        
    /**  
    * 添加课时
    * 
    * @access public 
    * @param  
    * @return  
    */
    public function addLesson(){
        if(IS_POST){
            $post=I('post.');
            $model=D('CourseLesson');
            $course_id=$this->getCourseId();
            if(!$model->create()){
                 $this->error($model->getError(),'',1);exit;
            }
            $result = $model->addData($post,$_FILES['file'],$course_id);
            if($result){
                $this->success('添加成功','',1);
            }else{
                $this->error('添加失败');
            }
                     
        }else{
            return false;
        }
    }
        
    /**  
    * 关闭课时
    * 
    * @access public 
    * @param  
    * @return  
    */
    public function closeLesson(){
        $lesson_id=I('post.lid');
        $result = M('CourseLesson')->where('id='.$lesson_id)->setField('status','unpublished');
        $this->ajaxReturn($result);
    }
    
    /**  
    * 发布课时
    * 
    * @access public 
    * @param  
    * @return  
    */
    public function publishLesson(){
        $lesson_id=I('post.lid');
        $result=M('CourseLesson')->where('id='.$lesson_id)->setField('status','published');
        $this->ajaxReturn($result);
    }
        
    /**  
    * 删除课时
    * 尤其需要注意 要删除哪些表中的哪些字段 
    * @access public 
    * @param  
    * @return  
    */
    public function delLesson(){
        if(IS_POST){
            $lesson_id=I('post.lid');
            if(!is_numeric($lesson_id) ) $this->ajaxReturn(false); 
          $bool =D('CourseLesson')->delete($lesson_id);
          $this->ajaxReturn($bool);
        }
    }
    
    /**  
    * 获取某一个课时
    * 
    * @access public 
    * @param  $lesson_id 课时id
    * @return 
    */
    public function getOneLesson(){
        if(IS_POST){
            $lesson_id=I('post.lid');
        }elseif(IS_GET){
            $lesson_id=I('get.lid');
        }
        if(!is_numeric($lesson_id) ) $this->ajaxReturn(false); 
        $lesson =D('CourseLesson')->find($lesson_id);
        $lesson['media_title'] = $this->getOneMedia($lesson['media_id'])['title'];
        $lesson['content'] =htmlspecialchars_decode($lesson['content']);
        $this->ajaxReturn($lesson);
    }
        
        
    /**  
    * 获取某一个课时的媒体文件
    * 
    * @access private 
    * @param  $fileid 文件id
    * @return array 
    */
    private function getOneMedia($fileid){
        $file =D('CourseFiles')->find($fileid);
        return $file;
    }
    
    public function getOneMediaAjax(){
        if(IS_POST){
            $post = I('post.');
            $lesson_id = $post['lid'];
            $check  = M('LessonMaterials')->where(array('lesson_id'=>$lesson_id))->find();
            if(!$check) $this->ajaxReturn('');

            $file = D('CourseFiles')->field('id,title')->find($check['file_id']);
            $arr = array(
                'mid'=> $file['id'],
                'mtitle'=>$file['title'],
            );
            $this->ajaxReturn($arr);
        }
        
    }
        
    /**  
    * 添加课时资料
    * 
    * @access public 
    * @param  $lesson 课时id
    * @return array 
    */
    public function addLessonMaterial(){
        if(IS_POST){
            $post = I('post.');
            //dump($post);die;
            $result = M('LessonMaterials')->add($post);
            if($result){
                $this->success('添加成功','',1);
            }else{
                $this->error('添加失败','',1);
            }
            
        }

    }
        
        
        
        
        
        
        
        
        
    /*--------课程文件处理相应---------*/
    /**  
     * 课程文件列表
     * 
     * @access public 
     * @param  
     * @return  
     */
    public function files(){
        $course_id= $this->getCourseId();
        $files = M('CourseFiles')->where('course_id='.$course_id)->select();//该课程下的所有文
        $this->assign('files',$files);
        $this->display('files');
    }
        
        
    /**  
     * 添加课程文件
     * 
     * @access public 
     * @param  
     * @return  
     */
    public function addfiles(){
        $course_id= $this->getCourseId();
        if(IS_POST){
            $post=I('post.');
            $course_id= $this->getCourseId();
            $post['course_id'] = $course_id;
            $post['user_id'] = session('uid');
            //dump($post);die;
          D('CourseFiles')->saveData($post,$_FILES, $course_id);
            
        }else{
            return false;
        }
    }
        
    /**  
     * 删除课程文件
     * 
     * @access public 
     * @param  
     * @return  
     */
    public function delfiles(){
        $course_id= $this->getCourseId();
        if(I('get.id')){
            $file_id=I('get.id');
            $file = D('CourseFiles')->find($file_id);
            $file_uri =  WORKING_PATH .$file['uri'];
            $bool = unlink($file_uri);//从upload删除文件 真 删除
            $result =   D('CourseFiles')->delete($file_id);
            if($result && $bool){
                $this->success('删除成功');
            }else{
                $this->error('删除失败');
            }
        }else{
            return false;
        }
        
    }
        
    /*---------课程图片处理函数----------*/
        
    /**  
     * 课程图片
     * 
     * @access public 
     * @param  
     * @return  
     */
    public function picture(){
        
        $course_id= $this->getCourseId();
        $picture = D('Course')->field('has_picture,picture_path')->find($course_id);
        if(!$picture['has_picture']){
            $picture['uri']= "/Public/Home/assets/images/portfolio/1.jpg" ;//默认课程图片地址
        }else{
            $picture['uri']=$picture['picture_path'];
        }
        $this->assign('picture',$picture);
        $this->display();
    }
        
    /**  
     * 上传课程图片
     * 
     * @access public 
     * @param  
     * @return  
     */
    public function uploadpicture(){
        if(IS_POST){
            $course_id= $this->getCourseId();
            $result = D('Course')->upload($_FILES['course_picture'], $course_id);
            if($result){
                $this->success('上传成功','',1);
            }else{
                $this->error('上传失败','',1);
            }
        }
    } 
        
 
    /*-----------课程公告管理-----------*/

    /**  
     * 课程公告
     * @access public 
     * @param  
     * @return  
     */
    public function bulletin(){
        $course_id= $this->getCourseId();
        $bulletins = M('CourseBulletin')->where('course_id='.$course_id)->select();//该课程下的所有公告
        $this->assign('bulletins',$bulletins);
        $this->display('bulletin');
    }

    /**  
     * 添加课程公告
     * @access public 
     * @param  $lesson 课时id
     * @return array 
    */
    public function addBulletin(){
        if(IS_POST){
            $post = I('post.');
            if(!$post['content'] ) $this->error('内容不得为空','',1);
            $post['course_id'] = $this->getCourseId();
            $post['content'] = htmlspecialchars_decode($post['content']);
            $post['post_time'] = time();
            //dump($post);die;
            $result = M('CourseBulletin')->add($post);
            $result ? $this->success('添加成功','',1):$this->error('添加失败','',1);
            
        }

    }

    /**  
     * 修改课程公告
     * @access public 
     * @param  $lesson 课时id
     * @return array 
     */
    public function editBulletin(){
        if(IS_POST){
            $post = I('post.');
            $post['content'] = htmlspecialchars_decode($post['content']);
            $result = M('CourseBulletin')->save($post);           
            $result ? $this->success('更新成功','',1):$this->error('更新失败','',1);       
        }
    }

    /**  
     * 删除课程公告
     * @access public 
     * @param  $lesson 课时id
     * @return array 
     */
    public function delBulletin(){
        $course_id= $this->getCourseId();

        $id     =   I('get.id');
        if(empty($id) || !is_numeric($id) || !M('CourseBulletin')->find($id)) $this->error('不存在该公告','',2);
         
        $result =   D('CourseBulletin')->delete($id);
        $result ? $this->success('删除成功','',1):$this->error('删除失败','',1); 

    }



        
        
        
        
        
    /*-----------课程面板管理-----------*/
    
    private function common(){
        $course = $this->getCourseInfo();
        $this->assign('course',$course);
        $this->display();
    }
    
    public function base(){
        $this->common();
    }
    public function detail(){
        $this->common();
    }
    
    public function teachers(){
        $this->display();
    }
    public function students(){
        $this->display();
    }
    public function price(){
        $this->display();
    }
    public function question(){
        $this->display();
    }
    public function testpaper(){
        $this->display();
    }
    public function order(){
        $this->display();
    }
 
    /*-----------课程相关处理函数-----------*/
    
    private function getCourseList(){
        $pre = C('DB_PREFIX');
        $teacher_id=session('uid');
        $map=array(
            'teacher_id'=>$teacher_id,
            'status'=>array('in','closed,published'),
        );
        //获取该教师拥有的所有课程信息 包括分类信息
        $course = D('Course')->alias('t1')->field('t1.*,t2.name as category_name')
                                                 ->join("{$pre}course_category as t2 on t1.category_id = t2.id")
                                                 ->where($map)->select();

        $this->assign('course',$course);
    }
    
    private function getCourseId(){
        if( !empty(I('get.cid'))  && is_numeric(I('get.cid') ) ){
                $course_id=I('get.cid');
                session('course_id',$course_id);
            }elseif( session('course_id') && is_numeric(session('course_id'))){
                $course_id=$_SESSION['course_id'];
            }else{
                session('course_id','0');
                $course_id  =   0;
            }
            return $course_id;
    }
    
    private function getCourseInfo(){
        
        $course_id  =   $this->getCourseId();
        $course     =   D('Course')->find($course_id);
        return $course;
    }
    
 
    
    
    //改变课程状态 
    private function changeStatus($type='',$id){
        if(empty($type)) exit;
        switch($type){
            case 'delete' :
                $set_status='draft';
                $mess='删除';
                break;
            case 'pulish' :
                $set_status='published';
                $mess='发布';
                break;
            case 'close' :
                $set_status='closed';
                $mess='关闭';
                break;          
        }
        $result=M('Course')->where('id='.$id)->setField('status',$set_status);
        $course=M('Course')->field('title')->find($id);
        if($result){
            $this->success($mess.'课程<'.$course['title'].'>成功','',2);
        }else{
            $this->error($mess.'课程<'.$course['title'].'>失败','',2);
        }
    }
        
        
        
        
        
        
    
    
    
}