<?php
namespace Chat\Controller;
use Think\Controller;

class RoomController extends Controller {
    
    
  
    /**  
     * id  
     * @access public
     * @param  series 
     * @return 
     */
    public function idDef(){

    }


    /**  
     * 保存消息  
     * @access public
     * @param  series 
     * @return 
     */
    public function save($content){

        if(IS_POST){

            $series  = I('post.series');
            $content = I('post.content');

            $map = array('series'=> $series );

            $messages = M('ChatRoom')->where($map)->getField('messages');//json 格式
            $messages = json_decode($messages, true);//数组

            $data = array(
                    'uid'   =>  session('uid'), 
                    'rid'   =>  session('rid'), 
                    'content'=> $content, 
                    'time'  =>  time(),
                );
            if($messages){
                array_push($messages, $data);//
            }else{
                $messages[0] = $data;
            }
            

            $messages = json_encode($messages, JSON_UNESCAPED_UNICODE);//中文乱码处理 JSON_UNESCAPED_UNICODE

            $result  = M('ChatRoom')->where($map)->setField('messages', $messages);
            $this->ajaxReturn($result);

        }
        

    }




}