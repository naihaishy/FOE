<?php
namespace Common\Controller;
use Think\Controller;

/**
 * 消息通知 邮件和短信
 */

class InformsController extends Controller{

    /**  
     * 消息发送
     * @access public
     * @param  
     * @return
     */
    public function send($type, $event, $user_id, $role_id, $message){

        switch ($role_id){
            case 1:
                $user = M('User')->field('email,username as name')->find($user_id);
                //$setting = M('UserSetting')->
                break;
            case 2:
                //教师
                $user = M('Teacher')->field('email,username as name')->find($user_id);
                $setting = M('TeacherSetting')->where(array('teacher_id'=>$user_id) )->find();
                break;
            case 3:
                //学生
                $user = M('Stu')->field('email,username as name')->find($user_id);
                $setting = M('StuSetting')->where(array('user_id'=>$user_id) )->find();
                break;
            default:
                # code...
                break;
        }

        //判断是否接受邮件/短信通知
        $email_setting = json_decode($setting['email'], true);
        if($type=='course' && $event=='qa_add' && $email_setting['email_info_question'] =='on'){
            //允许问题通知 当有学生在您的课程里提问时,该项设置决定是否允许系统发送问题至您的邮箱
            think_send_mail($user['email'], $user['name'], '课程问答通知', $message, '');
        }

        if($type=='forum' && $email_setting['email_info_forum'] =='on'){
            //允许论坛通知  
            think_send_mail($user['email'], $user['name'], '论坛通知', $message, '');
        }




    }





}