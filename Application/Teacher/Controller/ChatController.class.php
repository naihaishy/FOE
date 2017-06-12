<?php
//1.定义命名空间
namespace Teacher\Controller;
//2.引入父类控制器
use Think\Controller;

//3.定义控制器并且继承父类
class ChatController extends CommonController{


    /**  
     * 聊天 
     * @access public
     * @param  
     * @return
     */
    public function index($id=''){

        $friends    = '';
        $messages   = '';
        $series     = '';

        if(!empty($id) && strripos($id, '_')){
            //双人聊天室
            $arr    = explode('_', $id);
            //dump($arr );die;
            $friends[0]['friend_info'] = A('Common/Users')->getUserInfo(intval($arr[2]), intval($arr[3]) ); //朋友信息
            $friends[0]['online'] = A('Common/Users')->getOnlineInfo(intval($arr[2]), intval($arr[3]) );
            $friends[0]['chat']['series']  = $id;

            $series_op = array('aaa'=>$id, 'bbb'=>$arr[2].'_'.$arr[3].'_'.$arr[0].'_'.$arr[1]);
            $map    = array('series'=>array('in', $series_op) );

            $has_exist = M('ChatRoom')->where($map)->find();

            if(! $has_exist){

                $data = array('series'=> $id);
                M('ChatRoom')->add($data);
                $messages = '';
                $series = $id;

            }else{

                $series   = $has_exist['series'];    
                $messages = $has_exist['messages'];//json 
                $messages = json_decode($messages, true);//array
            }

        }else{
            // 不进入指定双人聊天
            $friends    =   A('Common/Users')->getFriendsInfo(session('uid'), session('rid'));//所有朋友信息
        }
        

        $assign     = array(
                'friends'   =>  $friends,
                'messages'  =>  $messages,
                'series'    =>  $series,

            );
        $this->assign($assign);
        //dump($messages);die;
        $this->display();
    }


}