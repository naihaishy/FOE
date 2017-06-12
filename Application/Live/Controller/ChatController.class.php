<?php
namespace Live\Controller;
use Think\Controller;

class ChatController extends Controller{



    /**  
    * save ajax
    * @access public
    * @param    
    * @return    
    */
    public function messages($live_id){
        $map = array('live_id'=> $live_id );
        $messages = M('LiveChatMessage')->where($map)->find()['messages'];

        $messages = json_decode($messages, true );//array

        return $messages;

    }




    /**  
    * save ajax
    * @access public
    * @param    
    * @return    
    */
    public function save(){
        
        if(IS_POST){

            $live_id   = I('post.liveid');
            $content    = I('post.content');

            $map = array('live_id'=> $live_id );

            $data = array(
                    'uid'   =>  session('uid'), 
                    'rid'   =>  session('rid'), 
                    'uname' =>session('uname'),
                    'content'=> $content, 
                    'time'  =>  time(),
                );

            $has_exists = M('LiveChatMessage')->where($map)->find();
            if(!$has_exists){
                $messages[0] = $data;
                $messages = json_encode($messages, JSON_UNESCAPED_UNICODE);//中文乱码处理 JSON_UNESCAPED_UNICODE
                $add_data = array(
                        'live_id'   =>  $live_id,
                        'messages'  =>  $messages,
                    );
                $result = M('LiveChatMessage')->add($add_data);
            }else{
                //已经存在
                $messages = M('LiveChatMessage')->where($map)->getField('messages');//json 格式
                $messages = json_decode($messages, true);//数组
                if($messages){
                    array_push($messages, $data);//
                }else{
                    $messages[0] = $data;//但无消息
                }
                

                $messages = json_encode($messages, JSON_UNESCAPED_UNICODE);//中文乱码处理 JSON_UNESCAPED_UNICODE

                $result  = M('LiveChatMessage')->where($map)->setField('messages', $messages);
            }

            $this->ajaxReturn($result);

        }
    }



    
}