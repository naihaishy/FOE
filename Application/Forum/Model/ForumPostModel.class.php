<?php
namespace Forum\Model;
use Think\Model;

class ForumPostModel extends Model{



    /**  
    * 数据添加
    * @access public
    * @param int  
    * @return  int post_id
    */
    public function addData($post, $file){

        if($file['error']==0){
            $post['attachment_id'] =  A('Files/Upload')->common($file, 'forum', ''); //返回id 
        }
        $post['content']   = htmlspecialchars_decode($post['content']);
        $post['created_time']   = time();
        $post['updated_time']   = time();
        $post['user_id']   = session('uid');
        $post['role_id']   = session('rid');
        return $this->add($post);
    }
}