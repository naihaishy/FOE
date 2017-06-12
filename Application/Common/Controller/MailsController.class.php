<?php
namespace Common\Controller;
use Think\Controller;

/**
 * 站内信处理类
 */

class MailsController extends Controller{

     

     
    public function _initialize(){
        if(!is_login()) $this->error('请先登录');
    }


    /**  
     * Index
     * @access public
     * @param  
     * @return
     */
    public function index(){

    }



    /**  
     * 站内信件 收
     * @access public
     * @param  
     * @return
     */
    public function inbox($type='inbox'){

        switch ($type) {
            case 'inbox':
                $this->mailinbox('normal');//收件箱 收
                break;
            case 'star':
                $this->mailinbox('star');//星标邮件  收
                break;
            case 'trash':
                $this->mailinbox('trash');//垃圾箱 收
                break;          
            default:
                $this->mailinbox('normal');
                break;
        }
    }


    /**  
     * 站内信件 发
     * @access public
     * @param  
     * @return
     */
    public function sendbox($type='sendbox'){

        switch ($type) {
            case 'sendbox':
                $this->mailsendbox();//发件箱 发
                break;
            case 'draft':
                $this->mailsendbox('draft');//草稿箱 发
                break;        
            default:
                $this->mailsendbox();
                break;
        }
    }



    /**  
     * 撰写邮件
     * @access public
     * @param  
     * @return
     */
    public function compose(){
        $model = D('Common/Email');

        if(IS_POST){
            $post = I('post.');

            //自动验证提交数据
            $rules = array(
                 array('to_uid','require','收件人必须有！'),
                 array('to_rid','require','收件人身份必须确定！'),
                 array('content','require','内容不得为空!'),
            );

            if(!$model->validate($rules)->create())  $this->error($model->getError(),'',1);

            $result = $model->addData($post, $_FILES['attachment'] );
            $result ? $this->success('发送成功','',1): $this->error('发送失败','',1);
            //dump($post);die;
        }else{

            $this->mailNavLeft();//左侧导航
            $this->display();
        }
        
    }



    /**  
     * 查看邮件
     * @access public
     * @param  
     * @return
     */
    public function view($id){
        $model = D('Common/Email');
        if(empty($id) || !is_numeric($id) || !M('Email')->find($id)) $this->error('不存在该邮件');

        $email = $model->getEmail($id);
        if($email['to_uid']==session('uid')) $model->where('id='.$id)->setField('isread', 1); //发给我的
        
        $this->assign('email', $email);
        $this->display();
    }


    /**  
     * 下载附件
     * @access public
     * @param  id fileid 
     * @return
     */
    public function downloadAttach($id){

        $data = M('Files')->find($id);
        //下载代码
        $file   =   WORKING_PATH . $data['uri'];
        header("Content-type: application/octet-stream");
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header("Content-Length: ". filesize($file));
        readfile($file);
    }


    /**  
     * 设置星标邮件 0 取消 1设置
     * @access public
     * @param  isstar 是否已经是星标 
     * @return
     */
    public function setStar($isstar,$id){
        $model = D('Common/Email');
        if($isstar){
            $model->where('id='.$id)->setField('stared','0');
        }else{
            $model->where('id='.$id)->setField('stared','1');
        }
        
    }









    /**  
     * 收件箱
     * @access private
     * @param  type 
     * @return
     */
    private function mailinbox($type='normal'){
        $model = D('Common/Email');
        $this->mailNavLeft();//左侧导航

        $emails     =  $model->getReceivedEmails(session('uid'), session('rid'), $type);

        $assign =   array(
                'emails'        =>  $emails,
                'page'          =>  array('title'=> $this->getPageTitle('inbox',$type) ),
            );
        //dump($assign);die;
        $this->assign($assign);
        $this->display();
    }


    /**  
     * 发件箱
     * @access private
     * @param  
     * @return
     */
    private function mailsendbox($type='normal,star,trash'){
        $model = D('Common/Email');
        $this->mailNavLeft();//左侧导航
        $emails  =  $model->getSendedEmails(session('uid'), session('rid'), $type ); //已经发送的信件 对方如何处理不是自己所能决定的

        $assign =   array(
                'emails'        =>  $emails,
                'page'          =>  array('title'=> $this->getPageTitle('sendbox',$type) ),
            );
        //dump($assign);die;
        $this->assign($assign);
        $this->display();
    }


    

    /**  
     * 邮件左侧导航
     * @access private
     * @param   
     * @return
     */
    private function mailNavLeft(){
        $model = D('Common/Email');
        $unread_count   =   $model->getUnreadCount(session('uid'), session('rid'));//获取未读信件数目
        $categorys  =  $model->getCategory(session('uid'), session('rid'));//
        $tags       =  $model->getTags(session('uid'), session('rid'));//

        $assign_nav =   array(
                'unread_count'  =>  $unread_count,
                'categorys'     =>  $categorys,
                'tags'          =>  $tags,
            );
        $this->assign($assign_nav);

    }





    /**  
     * 页面标题
     * @access private
     * @param  
     * @return
     */
    private function getPageTitle($domain, $type){
        if($domain =='inbox'){
            switch ($type) {
                case 'star':
                    return '星标邮件';
                case 'trash':
                    return '垃圾箱';
                default:
                    return '收件箱';
            }
        }else{
            switch ($type) {
                case 'draft':
                    return '草稿箱';
                default:
                    return '发件箱';
            }
        }
    }











    


    




    




    


    





 







}