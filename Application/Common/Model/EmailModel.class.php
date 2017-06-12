<?php
namespace Common\Model;
use Think\Model;

class EmailModel extends Model{



    /**  
    * 数据添加
    * @access public
    * @param int  
    * @return  int post_id
    */
    public function addData($post, $file){

        if($file['error']==0){
            $post['attachment_id'] =  A('Files/Upload')->common($file, 'email', ''); //返回id 
        }
        $post['content']    = htmlspecialchars_decode($post['content']);
        $post['post_time']  = time();
        $post['from_uid']   = session('uid');
        $post['from_rid']   = session('rid');
        return $this->add($post);
    }


    /**  
     * 获取收件箱信件
     * @access public
     * @param  type :normal star trash 
     * @return
     */
    public function getReceivedEmails($uid, $rid, $type='normal'){

        $map = array(
                'to_uid'    =>  $uid,
                'to_rid'    =>  $rid,
                'status'    =>  $type,
            );
        
        
        $emails =  $this->where($map)->select();
        //发件人信息
        foreach ($emails as $key => &$value) {
            if($value['from_rid']==3){
                //发自学生
                $value['sender'] =  M('Stu')->field('id,username as name,email')->where('id='.$value['from_uid'])->find();
            }elseif ($value['from_rid']==2) {
                //发自教师
                $value['sender'] =  M('Teacher')->field('id,username as name,email')->where('id='.$value['from_uid'])->find();
            }elseif ($value['from_rid']==1) {
                //发自管理员
                $value['sender'] =  M('User')->field('id,username as name,email')->where('id='.$value['from_uid'])->find();
            }
        }

        //附件信息
        foreach ($emails as $key => &$value) {
            $is_exists = M('Files')->find($value['attachment_id']);
            if(!$is_exists){
                $value['attachment_id'] = '';
            }
        }

        return $emails;
    }


    /**  
     * 获取发件箱信件 发
     * @access public
     * @param  type: 已发送的(normal star trash) 未发送的draft
     * @return
     */
    public function getSendedEmails($uid, $rid, $type='normal,star,trash'){

        $map = array(
                'from_uid'    =>    $uid,
                'from_rid'    =>    $rid,
                'status'        =>    array('in', $type),
            );

        $emails =  $this->where($map)->select();

        //收件人信息
        foreach ($emails as $key => &$value) {
            if($value['to_rid']==3){
                //发给学生
                $value['receiver'] =  M('Stu')->field('id,username as name,email')->where('id='.$value['to_uid'])->find();
            }elseif ($value['to_rid']==2) {
                //发给教师
                $value['receiver'] =  M('Teacher')->field('id,username as name,email')->where('id='.$value['to_uid'])->find();
            }elseif ($value['to_rid']==1) {
                //发给管理员
                $value['receiver'] =  M('User')->field('id,username as name,email')->where('id='.$value['to_uid'])->find();
            }
        }

        //附件信息 检测 
        foreach ($emails as $key => &$value) {
            $is_exists ='';
            if($value['attachment_id']){
                $is_exists = M('Files')->find($value['attachment_id']);
            }
            if(!$is_exists){
                $value['attachment_id'] = '';
            }
        }

        return $emails;
    }




    /**  
     * 收件箱未读信件 收
     * @access public
     * @param  
     * @return
     */
    public function getUnreadCount($uid, $rid){
        $map = array(
                'to_uid'    =>  $uid,
                'to_rid'    =>  $rid,
                'status'    =>  array('not in', 'draft'),
                'isread'    =>  0,
            );
        return $this->where($map)->count();
    }

    /**  
     * 收件箱未读信件 收
     * @access public
     * @param  
     * @return
     */
    public function getUnread($uid, $rid, $num){
        $map = array(
                'to_uid'    =>  $uid,
                'to_rid'    =>  $rid,
                'status'    =>  array('not in', 'draft'),
                'isread'    =>  0,
            );
        $emails =  $this->where($map)->limit($num)->select();
        foreach ($emails as $key => &$value) {
            $value = $this->getEmail($value['id']);
        }
        return $emails;
    }




    /**  
     * 获取某一封信件
     * @access public
     * @param  
     * @return
     */
    public function getEmail($id){


        $email =  $this->find($id);

        if($email['from_rid']==3){
            //发自学生
            $email['sender'] =  M('Stu')->field('id,username as name,email,avatar')->where('id='.$email['from_uid'])->find();
        }elseif ($email['from_rid']==2) {
            //发自教师
            $email['sender'] =  M('Teacher')->field('id,username as name,email,avatar')->where('id='.$email['from_uid'])->find();
        }elseif ($email['from_rid']==1) {
            //发自管理员
            $email['sender'] =  M('User')->field('id,username as name,email,avatar')->where('id='.$email['from_uid'])->find();
        }

        if($email['to_rid']==3){
            //发给学生
            $email['receiver'] =  M('Stu')->field('id,username as name,email,avatar')->where('id='.$email['to_uid'])->find();
        }elseif ($email['to_rid']==2) {
            //发给教师
            $email['receiver'] =  M('Teacher')->field('id,username as name,email,avatar')->where('id='.$email['to_uid'])->find();
        }elseif ($email['to_rid']==1) {
            //发给管理员
            $email['receiver'] =  M('User')->field('id,username as name,email,avatar')->where('id='.$email['to_uid'])->find();
        }

        if($email['attachment_id'])  $email['file'] = M('Files')->find($email['attachment_id']);
        
        return $email;

    }




    /**  
     * 获取信件分类 收
     * @access public
     * @param  
     * @return
     */
    public function getCategory($uid, $rid){
        $map = array(
                'to_uid'    =>$uid,
                'to_rid'    =>$rid,
                'status'    =>'normal',
            );
        $categorys  =  $this->field('category as name')->where($map)->distinct(true)->select();
        return $categorys;
    }


    /**  
     * 获取信件标签 收
     * @access public
     * @param  
     * @return
     */
    public function getTags($uid, $rid){
        $map = array(
                'to_uid'    =>$uid,
                'to_rid'    =>$rid,
                'status'    =>'normal',
            );
        $tags  =  $this->field('tags as name')->where($map)->distinct(true)->select();
        return $tags;
    }







}