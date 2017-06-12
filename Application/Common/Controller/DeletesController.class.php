<?php
namespace Common\Controller;

use Think\Controller;

class DeletesController extends Controller{


    /**
     * ActionName
     */
    public function index(){
        
        $this->display();
    }


    /**  
     * 删除操作
     * @access public
     * @param  
     * @return
     */
    public function doo($type, $id, $condition=''){

        switch ($type) {
            case 'user':
                $this->user($id, $condition);
                break;
            case 'course':
                $this->course($id);
                break;
            case 'live':
                $this->live($id);
                break;
            case 'forum':
                $this->forum($id);
                break;            
            default:
                # code...
                break;
        }
    }


    /**  
     * 删除用户
     * @access public
     * @param  
     * @return
     */
    private function user($id, $condition){
        switch ($condition['role_id']) {
            case '1':
                //管理员
                $this->delAdmin($id);
                break;
            case '2':
                //教师
                $this->delTeacher($id);
                break;
            case '3':
                //学生
                $this->delStudent($id);
                break;
            default:
                # code...
                break;
        }
    }

    /**  
     * 删除管理员
     * @access public
     * @param  
     * @return
     */
    private function delAdmin($id, $role_id=1){
        M('User')->delete($id);

        //文件删除操作
        $files = M('Files')->where(array('upload_user'=>$id.','.$role_id ))->select();
        foreach ($files as $key => $value) {
            unlink($value['uri']); //删除文件
        }
        M('Files')->where(array('upload_user'=>$id.','.$role_id ))->delete(); //删除管理员上传的文件

        M('Messages')->where(array('user_id'=>$id, 'role_id'=>$role_id))->delete(); //删除消息提醒

        M('Email')->where(array('from_uid'=>$id, 'from_rid'=>$role_id))->delete(); //删除发送的邮件
        M('Email')->where(array('to_uid'=>$id, 'to_rid'=>$role_id))->delete(); //删除收到的邮件

        M('Relation')->where(array('user_id'=>$id, 'role_id'=>$role_id))->delete(); //删除关注信息
        M('Relation')->where(array('friend_uid'=>$id, 'friend_rid'=>$role_id))->delete(); //删除被关注信息

        //管理员发布的其他内容如何处理? 更改所有者还是直接删除? 


    }

    /**  
     * 删除教师
     * @access public
     * @param  
     * @return
     */
    private function delTeacher($id, $role_id=2){
        M('Teacher')->delete($id);
        M('TeacherSetting')->where(array('user_id'=>$id))->delete();

        $courses = M('Course')->where(array('teacher_id'=>$id))->select();
        foreach ($courses as $key => $value) {
            $this->course($value['id']); //删除课程
        }

        $lives = M('Live')->where(array('teacher_id'=>$id))->select();
        foreach ($lives as $key => $value) {
            $this->live($value['id']); //删除直播
        }

        M('LiveRoom')->where(array('teacher_id'=>$id))->delete();//该教师的直播间信息


        M('Messages')->where(array('user_id'=>$id, 'role_id'=>$role_id))->delete(); //删除消息提醒


        //文件删除操作
        $files = M('Files')->where(array('upload_user'=>$id.','.$role_id ))->select();
        foreach ($files as $key => $value) {
            unlink($value['uri']); //删除文件
        }
        M('Files')->where(array('upload_user'=>$id.','.$role_id ))->delete(); //删除用户上传的文件


        //教师论坛删除相关操作
        $teacher_forum_posts = M('TeacherForumPost')->where(array('user_id'=>$id, 'role_id'=>$role_id))->select();
        foreach ($teacher_forum_posts as $key => $value) {
            $this->forum('home', $value['id']); //删除该帖子及相关信息
        }

        //教师论坛回复相关删除
        $teacher_forum_replys = M('TeacherForumReply')->where(array('user_id'=>$id, 'role_id'=>$role_id))->select();//该教师的回复
        foreach ($teacher_forum_replys as $key => $value) {
            M('TeacherForumPost')->where(array('id'=>$value['post_id']))->setDec('reply_count', 1); //该帖子的回复数-1
        }
        M('TeacherForumReply')->where(array('user_id'=>$id, 'role_id'=>$role_id))->delete(); //删除该教师对别人的所有回复  

        //教师论坛点赞相关删除
        $teacher_upvotes = M('TeacherForumUpvote')->where(array('user_id'=>$id, 'role_id'=>$role_id))->select(); //该教师的所有点赞
        foreach ($teacher_upvotes as $key => $value) {
            M('TeacherForumReply')->where(array('id'=>$value['reply_id']))->setDec('thumbup_count',1); //该回复点赞数-1
        }
        M('TeacherForumUpvote')->where(array('user_id'=>$id, 'role_id'=>$role_id))->delete(); //删除教师的点赞记录

        //门户论坛帖子相关删除
        $forum_posts = M('ForumPost')->where(array('user_id'=>$id, 'role_id'=>$role_id))->select();
        foreach ($forum_posts as $key => $value) {
            $this->forum('home', $value['id']); //删除该帖子及相关信息
        }

        //门户论坛回复相关删除
        $forum_replys = M('ForumReply')->where(array('user_id'=>$id, 'role_id'=>$role_id))->select();//该用户的回复
        foreach ($forum_replys as $key => $value) {
            M('ForumPost')->where(array('id'=>$value['post_id']))->setDec('reply_count', 1); //该帖子的回复数-1
        }
        M('ForumReply')->where(array('user_id'=>$id, 'role_id'=>$role_id))->delete(); //删除该用户对别人的回复  

        //门户论坛点赞相关删除
        $upvotes = M('ForumUpvote')->where(array('user_id'=>$id, 'role_id'=>$role_id))->select(); //该用户的所有点赞
        foreach ($upvotes as $key => $value) {
            M('ForumReply')->where(array('id'=>$value['reply_id']))->setDec('thumbup_count',1); //该回复点赞数-1
        }
        M('ForumUpvote')->where(array('user_id'=>$id, 'role_id'=>$role_id))->delete(); //删除用户的点赞记录

 

        M('Email')->where(array('from_uid'=>$id, 'from_rid'=>$role_id))->delete(); //删除用户发送的邮件
        M('Email')->where(array('to_uid'=>$id, 'to_rid'=>$role_id))->delete(); //删除用户收到的邮件

        M('Relation')->where(array('user_id'=>$id, 'role_id'=>$role_id))->delete(); //删除关注信息
        M('Relation')->where(array('friend_uid'=>$id, 'friend_rid'=>$role_id))->delete(); //删除被关注信息


      
      

       
    }


    /**  
     * 删除学生
     * @access public
     * @param  
     * @return
     */
    private function delStudent($id, $role_id=3){
        M('Stu')->delete($id); //删除学生账号
        M('StuSetting')->where(array('user_id'=>$id))->delete($id); //删除学生的个人设置信息
        M('CourseDiscuss')->where(array('user_id'=>$id, 'role_id'=>$role_id))->delete(); //删除学生的课程讨论
        M('CourseDiscussReply')->where(array('user_id'=>$id, 'role_id'=>$role_id))->delete(); //删除学生的课程讨论回复
        M('CourseFollow')->where(array('user_id'=>$id))->delete(); //删除学生的课程关注
        M('CourseLessonLearn')->where(array('user_id'=>$id))->delete(); //删除学生的课程学习记录
        M('CoursePaperTest')->where(array('user_id'=>$id))->delete(); //删除学生的课程测试记录

        M('Email')->where(array('from_uid'=>$id, 'from_rid'=>$role_id))->delete(); //删除用户发送的邮件
        M('Email')->where(array('to_uid'=>$id, 'to_rid'=>$role_id))->delete(); //删除用户收到的邮件

        //文件删除操作
        $files = M('Files')->where(array('upload_user'=>$id.','.$role_id ))->select();
        foreach ($files as $key => $value) {
            unlink($value['uri']); //删除文件
        }
        M('Files')->where(array('upload_user'=>$id.','.$role_id ))->delete(); //删除用户上传的文件

        //论坛帖子相关删除
        $forum_posts = M('ForumPost')->where(array('user_id'=>$id, 'role_id'=>$role_id))->select();
        foreach ($forum_posts as $key => $value) {
            $this->forum('home', $value['id']); //删除该帖子及相关信息
        }

        //论坛回复相关删除
        $forum_replys = M('ForumReply')->where(array('user_id'=>$id, 'role_id'=>$role_id))->select();//该用户的回复
        foreach ($forum_replys as $key => $value) {
            M('ForumPost')->where(array('id'=>$value['post_id']))->setDec('reply_count', 1); //该帖子的回复数-1
        }
        M('ForumReply')->where(array('user_id'=>$id, 'role_id'=>$role_id))->delete(); //删除该用户对别人的回复  

        //论坛点赞相关删除
        $upvotes = M('ForumUpvote')->where(array('user_id'=>$id, 'role_id'=>$role_id))->select(); //该用户的所有点赞
        foreach ($upvotes as $key => $value) {
            M('ForumReply')->where(array('id'=>$value['reply_id']))->setDec('thumbup_count',1); //该回复点赞数-1
        }
        M('ForumUpvote')->where(array('user_id'=>$id, 'role_id'=>$role_id))->delete(); //删除用户的点赞记录   

        M('LessonAnswers')->where(array('user_id'=>$id, 'metas'=>''))->delete(); //删除学生的课程提问回答

        M('LessonNotes')->where(array('user_id'=>$id))->delete(); //删除学生的课程笔记

        M('LessonQuestions')->where(array('user_id'=>$id))->delete(); //删除学生的课程提问

        M('Messages')->where(array('user_id'=>$id, 'role_id'=>$role_id))->delete(); //删除消息提醒

        //OJ 删除处理
        $solutions = M('OjSolution')->where(array('user_id'=>$id, 'role_id'=>$role_id))->select();
        foreach ($solutions as $key => $value) {
            M('OjSourceCode')->where(array('solution_id'=>$value['solution_id']))->delete();//删除提交的oj 源代码
            M('OjCompileinfo')->where(array('solution_id'=>$value['solution_id']))->delete();//删除编译错误信息
            M('OjRuntimeinfo')->where(array('solution_id'=>$value['solution_id']))->delete();//删除运行错误信息
        }
        M('OjSolution')->where(array('user_id'=>$id, 'role_id'=>$role_id))->delete(); //删除学生OJ提交记录
 

        M('Relation')->where(array('user_id'=>$id, 'role_id'=>$role_id))->delete(); //删除关注信息
        M('Relation')->where(array('friend_uid'=>$id, 'friend_rid'=>$role_id))->delete(); //删除被关注信息

    }




    /**  
     * 删除课程
     * @access public
     * @param  id 课程id
     * @return
     */
    private function course($id){

        $course = M('Course')->find($id);//该课程信息

        M('Course')->delete($id);//删除课程
        M('CourseCategory')->where(array('id'=>$course['category_id']))->setDec('count', 1);//课时分类统计数-1
        M('CourseBulletin')->where(array('course_id'=>$id))->delete();//删除课程公告
        M('CourseChapter')->where(array('course_id'=>$id))->delete();//删除章节
        M('CourseLesson')->where(array('course_id'=>$id))->delete(); //删除课时
        M('CourseLessonLearn')->where(array('course_id'=>$id))->delete(); //删除学生课时学习记录
        M('CourseFollow')->where(array('course_id'=>$id))->delete();//删除课程关注

        //课程讨论删除相关操作
        $discuss = M('CourseDiscuss')->where(array('course_id'=>$id))->select(); //课程讨论
        foreach ($discuss as $key => $value) {
            M('CourseDiscussReply')->where(array('discuss_id'=>$id))->delete(); //删除讨论回复
        }
        M('CourseDiscuss')->where(array('course_id'=>$id))->delete(); //删除课时讨论


        //课程文件删除相关操作
        $files = M('CourseFiles')->where(array('course_id'=>$id))->select(); //课程文件
        foreach ($files as $key => $value) {
            unlink($value['uri']); //删除文件
        }
        M('CourseFiles')->where(array('course_id'=>$id))->delete(); //删除课时文件记录



        //试卷删除相关操作
        $papers   = M('CoursePaper')->where(array('course_id'=>$id))->select(); //试卷
        foreach ($papers as $key => $value) {
            M('CoursePaperTest')->where(array('paper_id'=>$value['id']))->delete(); //删除试卷提交记录
        }
        M('CoursePaper')->where(array('course_id'=>$id))->delete(); //删除课程试卷


        //题库删除相关操作
        $questions  = M('CourseQuestion')->where(array('course_id'=>$id))->select();
        foreach ($questions as $key => $value) {
            if(!empty($value['metas'])){
                $metas  = json_decode($value['metas'], true);
                if(!empty($metas['ojpid'])){
                    //该题是OJ类型题目
                    A('Course/Oj')->delOjProblem($metas['ojpid']);
                }
            }
        }
        M('CourseQuestion')->where(array('course_id'=>$id))->delete(); //删除课程题库
 

    }


    /**  
     * 删除直播
     * @access public
     * @param   
     * @return
     */
    public function live($id){
        $live = M('Live')->find($id);//该直播信息
        M('LiveCategory')->where(array('id'=>$live['category_id']))->setDec('count', 1);//直播分类统计数-1
        M('LiveChatMessage')->where(array('live_id'=>$id))->delete();//该直播的聊天信息
        M('Live')->delete($id)
    }


    /**  
     * 删除论坛帖子
     * @access public
     * @param   
     * @return
     */
    public function forum($type='home', $id){
        if($type=='teacher'){
            //教师论坛
            $replys = M('TeacherForumReply')->where(array('post_id'=>$id))->seletc(); //该帖子下的所有回复
            foreach ($replys as $key => $value) {
                M('TeacherForumUpvote')->where(array('reply_id'=>$value['id']))->delete();//删除对回复的点赞记录
            }
            M('TeacherForumPost')->delete($id);//删除该帖子
            M('TeacherForumReply')->where(array('post_id'=>$id))->delete();//删除该帖子下回复

        }else{
            //门户论坛
            $replys = M('ForumReply')->where(array('post_id'=>$id))->seletc(); //该帖子下的所有回复
            foreach ($replys as $key => $value) {
                M('ForumUpvote')->where(array('reply_id'=>$value['id']))->delete();//删除对回复的点赞记录
            }
            M('ForumPost')->delete($id);//删除该帖子
            M('ForumReply')->where(array('post_id'=>$id))->delete();//删除该帖子下回复

        }
    }








    
}