<?php
namespace Common\Controller;
use Think\Controller;

/**
 * 系统端消息处理类
 */

class MessagesController extends Controller{



    /**  
     * Index
     * @access public
     * @param  
     * @return
     */
    public function index(){

    }


    /**  
     * 消息发送
     * @access public
     * @param  
     * @return
     */
    public function send($type, $event, $source, $user_id, $role_id){
        
        
        switch ($type) {
            case 'course':
                $message = $this->courseMes($event, $source, $role_id);
                break;
            case 'live':
                $message = $this->liveMes($event, $source, $role_id);
                break;
            case 'forum':
                $message = $this->forumMes($event, $source);
                break;
            case 'account':
                $message = $this->accountMes($event);
                break;
            case 'authority':
                $message = $this->authorityMes($event, $source, $role_id);
                break;
            default:
                # code...
                break;
        }

        $result = $this->sendMessage($type, $event, $user_id, $role_id, $message);
        if($result) A('Common/Informs')->send($type, $event, $user_id, $role_id, $message);
    }

    /**  
     * 消息发送
     * @access private
     * @param  
     * @return
     */
    private function sendMessage($type, $event, $user_id, $role_id, $message){
        //$this->checkSetting($type, $event, $user_id, $role_id); //用户是否设置了相关提醒
        $data = array(
                'type'      =>  $type,
                'event'     =>  $event,
                'user_id'   =>  $user_id,
                'role_id'   =>  $role_id,
                'message'   =>  $message,
                'post_time' =>  time(),
            );
        $result = M('Messages')->add($data);
        return $result;
    }


    /**  
     * 检查用户设置
     * @access private
     * @param  
     * @return
     */
    private function checkSetting($type, $event, $user_id, $role_id){
        switch ($role_id) {
            case 1:
                # code...
                break;
            case 2:
                $setting = M('TeacherSetting')->where(array('teacher_id'=>$user_id))->find();
                break;
            case 3:
                $setting = M('StuSetting')->where(array('user_id'=>$user_id))->find();
                break;
            default:
                # code...
                break;
        }

        $setting = json_decode($setting['inform']);
         
        if($type=='forum'){
            if($event=='reply' && $setting['inform_forum_post_reply']=='on') return true;
            if($event=='stick' && $setting['inform_forum_post_stick']=='on') return true;
            if($event=='unstick' && $setting['inform_forum_post_unstick']=='on') return true;
            if($event=='essence' && $setting['inform_forum_post_essence']=='on') return true;
            if($event=='unessence' && $setting['inform_forum_post_unessence']=='on') return true;
            if($event=='delete' && $setting['inform_forum_post_delete']=='on') return true;
            if($event=='edit' && $setting['inform_forum_post_edit']=='on') return true;
        }
        if($type=='account'){
            if($event=='login' && $setting['inform_account_login']=='on') return true;
            if($event=='changepassword' && $setting['inform_account_changepass']=='on') return true;
        }
       /* if($type=='course'){
            if($event=='login' && $setting['inform_account_login']=='on') return true;
            if($event=='changepassword' && $setting['inform_account_changepass']=='on') return true;
        }*/

    }



    /**  
     * 账户消息
     * @access private
     * @param  
     * @return
     */
    private function accountMes($event){
        switch ($event) {
            case 'login':
                $message = "您于".date('Y年m月d日 H:i:s',time())."在".get_client_ip()."登录了该系统";
                break;
            case 'changepassword':
                $message = "您于".date('Y年m月d日 H:i:s',time())."修改了您的密码";
                break;
            case 'logout':
                $message = "您于".date('Y年m月d日 H:i:s',time())."退出了该系统";
                break;
            case 'activate':
                $message = "您于".date('Y年m月d日 H:i:s',time())."成功激活了该账号";
                break;
            default:
                # code...
                break;
        }

        return $message;
    }



    /**  
     * 论坛消息
     * @access private
     * @param  source  array 消息源 论坛帖子 [url title  reply_username  reason] 
     * @return
     */
    private function forumMes($event, $source){
        switch ($event) {
            case 'reply':
                $message = "<strong>".$source['reply_username']."</strong>回复了您的帖子<a href='".$source['url']."'>".$source['title']."</a> ";
                break;
            case 'thumbup':
                $message = "<strong>".$source['reply_username']."</strong>点赞了您的帖子<a href='".$source['url']."'>".$source['title']."</a> ";
                break;
            case 'stick':
                $message = "您的帖子<a href='".$source['url']."'>".$source['title']."</a>被管理员置顶了";
                break;
            case 'unstick':
                $message = "您的帖子<a href='".$source['url']."'>".$source['title']."</a>被管理员取消置顶了";
                break;
            case 'essence':
                $message = "您的帖子<a href='".$source['url']."'>".$source['title']."</a>被管理员加精华了";
                break;
            case 'unessence':
                $message = "您的帖子<a href='".$source['url']."'>".$source['title']."</a>被管理员取消了精华";
                break;
            case 'delete':
                $message = "您的帖子<a href='".$source['url']."'>".$source['title']."</a>被删除了.原因如下:".$source['reason'];
                break;
            case 'edit':
                $message = "您的帖子<a href='".$source['url']."'>".$source['title']."</a>被管理员修改了.原因如下:".$source['reason'];
                break;
            default:
                # code...
                break;
        }

        return $message;
    }



    /**  
     * 课程消息
     * @access private
     * @param  source  mixed 消息源 课程 [url title  left_time  student[name] reason] 
     * @return
     */
    private function courseMes($event, $source, $role){
        if($role=='teacher' ||  $role===2){
            switch ($event) {
                case 'check_pass':
                    $message = "您开设的课程 <a href='".$source['url']."'>".$source['title']."</a>通过了审核";
                    break;
                case 'check_fail':
                    $message = "您开设的课程 <a href='".$source['url']."'>".$source['title']."</a>没有通过审核，原因：".$source['reason'].".请修改后再次提交审核";
                    break;
                case 'close':
                    $message = "您开设的课程 <a href='".$source['url']."'>".$source['title']."</a>于".date("Y年m月d日", time())."被管理员关闭了";
                    break;
                case 'pre':
                    $message = "您开设的课程 <a href='".$source['url']."'>".$source['title']."</a>还有".$source['left_time']."就要开课了,请做好准备";
                    break;
                case 'over':
                    $message = "您开设的课程 <a href='".$source['url']."'>".$source['title']."</a>还有".$source['left_time']."就要结课了,请做好准备";
                    break;
                case 'exam':
                    $message = "您开设的课程 <a href='".$source['url']."'>".$source['title']."</a>还有".$source['left_time']."就要考试了,请做好准备";
                    break;
                case 'join':
                    $message = $source['student']['name']."加入了您开设的课程 <a href='".$source['url']."'>".$source['title']."</a>";
                    break;
                case 'learncount':
                    $message = "您开设的课程 <a href='".$source['url']."'>".$source['title']."</a>学习总数达到了".$source['learncount']."人!";
                    break;
                case 'qa_add':
                    $message = $source['student']['name']."在您的课程 <a href='".$source['url']."'>".$source['title']."</a>下提出了如下问题:<strong>".$source['question']['title']."</strong><a href='".$source['question']['url']."' >点击回复</a>";
                default:
                    # code...
                    break;
            }
        }elseif ($role=='student' ||  $role===3) {
            switch ($event) {
                case 'pre':
                    $message = "您加入学习的课程 <a href='".$source['url']."'>".$source['title']."</a>还有".$source['left_time']."就要开课了,请做好准备";
                    break;
                case 'over':
                    $message = "您加入学习的课程 <a href='".$source['url']."'>".$source['title']."</a>还有".$source['left_time']."就要结课了,请做好准备";
                    break;
                case 'exam':
                    $message = "您加入学习的课程 <a href='".$source['url']."'>".$source['title']."</a>还有".$source['left_time']."就要考试了,请做好准备";
                    break;
                case 'qa_answer':
                    $message = "您在课程 <a href='".$source['url']."'>".$source['title']."</a>的问题<strong>".$source['question']."</strong>有人回答了";
                case 'qa_teacher_answer':
                    $message = "您在课程 <a href='".$source['url']."'>".$source['title']."</a>的问题<strong>".$source['question']."</strong>被老师回答了";
                    break;
                case 'bulletin':
                    $message = "您加入学习的课程 <a href='".$source['url']."'>".$source['title']."</a>发布了公告,请前往课程公告区查看";
                    break;
                default:
                    # code...
                    break;
            }
        }

        return $message;
        
    }





    /**  
     * 直播消息
     * @access private
     * @param  source  array 消息源 直播 [url title  left_time teacher_name reason] 
     * @return
     */
    private function liveMes($event, $source, $role){

        if($role=='teacher' ||  $role===2){

            switch ($event) {
                case 'check_pass':
                    $message = "您发布的直播 <a href='".$source['url']."'>".$source['title']."</a>通过了审核";
                    break;
                case 'check_fail':
                    $message = "您发布的直播 <a href='".$source['url']."'>".$source['title']."</a>没有通过审核，原因：".$source['reason'].".请修改后再次提交审核";
                    break;
                case 'pre':
                    $message = "您发布的直播<a href='".$source['url']."'>".$source['title']."</a>还有".$source['left_time']."就要开始了,请做好准备";
                    break;
                case 'shutdown':
                    $message = "您发布的直播 <a href='".$source['url']."'>".$source['title']."</a>由于".$source['reason']."等原因被关闭了";
                    break;
                default:
                    # code...
                    break;
            }

        }elseif ($role=='student' ||  $role===3) {

            switch ($event) {
                case 'pre':
                    $message = "您关注的直播<a href='".$source['url']."'>".$source['title']."</a>还有".$source['left_time']."就要开始了,请做好准备";
                    break;
                case 'shutdown':
                    $message = "您关注的直播 <a href='".$source['url']."'>".$source['title']."</a>由于".$source['reason']."等原因被关闭了";
                    break;
                case 'open':
                    $message = "您关注的教师<strong>".$source['teacher_name']."</strong>发布了新的直播<a href='".$source['url']."'>".$source['title']."</a>";
                    break;
                default:
                    # code...
                    break;
            }
        }

        return $message;
        
    }


    /**  
     * 权限消息 仅针对管理员
     * @access private
     * @param  
     * @return
     */
    private function authorityMes($event, $source, $role){
        if($role != '3' ) exit;
        switch ($event) {
            case 'change':
                $message = "您于".date('Y年m月d日 H:i:s',time())."被超级管理员授予了<strong>".$source['authority']."</strong>权限";
                break;
            default:
                # code...
                break;
        }

        return $message;
    }






}