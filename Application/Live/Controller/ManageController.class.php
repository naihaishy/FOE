<?php
namespace Live\Controller;

use Think\Controller;

class ManageController extends CommonController {
    
    
    
    /**  
        * 直播列表
        * @access public
        * @param int  
        * @return  int 
        */
    
    public function index(){
        $map  = array('teacher_id'=>session('uid'), );
        $live = M('Live')->where($map)->select();
        
        $this->assign('live', $live);
        $this->display();
    }
    
    /**  
        * 直播预览
        * @access public
        * @param int  
        * @return  int 
        */
    public function view($id=''){
        if(empty($id)) exit;
        $id = intval($id);
        $live = M('Live')->find($id);
        if(!$live) $this->error('非法访问','',2);
        $room = M('LiveRoom')->where('room_id='. $live['room_id'])->find();
        $this->assign('live', $live);
        $this->assign('room',$room);
        $this->display();
    }


    /**  
     * 直播实时消息
     * @access public
     * @param int  
     * @return  int 
     */
    public function message($id=''){
        if(empty($id) || !is_numeric($id) || !M('Live')->find($id) ) $this->error('不存在该直播','',2);

        $live = M('Live')->find($id);

        $room = M('LiveRoom')->where(array('room_id'=>$live['room_id']))->find();
        $user = array('uname'=>session('uname'));

        $messages = A('Live/Chat')->messages($id);

        $assign = array(
                'live'           =>$live,
                'room'           =>$room,
                'user'           =>$user,
                'messages'      =>$messages,
            );
        
        $this->assign($assign);
        $this->display();
    }

    /**  
     * 上传录播
     * @access public
     * @param int  
     * @return  int 
     */
    public function uploadvod($id){

        if(empty($id) || !is_numeric($id) || !M('Live')->find($id) ) $this->error('不存在此直播','',2);

        if(IS_POST){
            $room_id    =   M('Live')->find($id)['room_id'];
            $result     =   D('Live')->addVod($_FILES, $room_id, $id);
        }else{
           
            $live   =   M('Live')->find($id);
            $this->assign('live',$live);
            $this->display();
        }
    }

    
 
    
    
    /**  
        * 直播删除
        * @access public
        * @param int  
        * @return  int 
        */
    public function del($id=''){
        if(empty($id)) exit;
        $id = intval($id);
        $live = M('Live')->find($id);
        if(!$live) $this->error('非法访问','',2);
        $result = M('Live')->delete($id);
        $result ? $this->success('删除成功','',2): $this->error('删除失败','',2);
    }
    
    /**  
        * 直播设置 --显示
        * @access public
        * @param int  
        * @return  int 
        */
    public function edit(){

        $pre = C('DB_PREFIX');
        
        $live_id = I('get.id');
        if(empty($live_id)) $this->error('非法访问','',2);
            
        if(IS_POST){

            $post = I('post.');

            $live_id   = $post['id'];
            $tid        = session('uid');
            $room_id    = A('Setting')->getRoomId($tid);

            $post['start']    = strtotime($post['start_date'].' '. $post['start_time']);
            $map = array('teacher_id' =>$tid , 'id'=>$live_id, 'room_id'=>$room_id);
            $result = D('Live')->where($map)->editData($post, $_FILES['poster'], $room_id);
            empty($result) ? $this->error('更新失败', '', 1): $this->success('更新成功', '', 1);
        }else{
            $data =M('Live')->alias('t1')
                            ->join("left join {$pre}live_room as t2 on t1.room_id = t2.room_id")
                            ->find($live_id);
            $this->assign('data', $data);
            $this->display();
        }
        
        
    }
    
 
    /**  
     * RTMP自检
     * @access private
     * @param int  
     * @return  int 
     */
    private function checkRtmp($live_id, $room_id){
        $rtmp   = M('LiveRoom')->field('rtmp')->where('room_id='.$room_id)->find()['rtmp'];
        $rtmp_keys =  M('Live')->field('rtmp_keys')->find($live_id)['rtmp_keys'];
        if(!$rtmp){
            $rtmp = 'rtmp://fms.zhfsky.com/live';//目前这样写 后期再斟酌
            M('LiveRoom')->where('room_id='.$room_id)->setField('rtmp', $rtmp);
        }
        if(!$rtmp_keys){
            $rtmp_keys = A('Record')->genRtmpKeys($room_id);
            M('LiveRoom')->where('id='.$live_id)->setField('rtmp_keys', $rtmp_keys);
        }
    }
    
    
    
    
    
    
    





}