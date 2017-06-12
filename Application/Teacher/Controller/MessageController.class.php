<?php
namespace Teacher\Controller;
use Think\Controller;

class MessageController extends CommonController {


     /**  
     * 系统消息提醒
     * @access public
     * @param  
     * @return
     */
    public function index($type='all'){
        $map = array(
                'user_id'   =>  session('uid'),
                'role_id'   =>  session('rid'),
            );

        switch ($type) {
            case 'forum':
                $map['type']='forum';
                $page['title']='论坛通知';
                break;
            case 'qa':
                $map['type']='course';
                $map['event'] = array('in', 'qa_add');
                $page['title']='问答消息';
                break;
            case 'system':
                $map['type']=array('in', 'account');
                $page['title']='系统消息';
                break;
            case 'course':
                $map['type']='course';
                $page['title']='课程消息';
                break;
            case 'live':
                $map['type']='live';
                $page['title']='直播消息';
                break;
            default:
                $page['title']='全部消息';
                break;
        }

        $messages = M('Messages')->where($map)->order('post_time desc')->select();
        $this->assign('messages', $messages);
        $this->assign('page', $page);
        $this->display();
    }


}