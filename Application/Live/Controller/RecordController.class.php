<?php
namespace Live\Controller;

use Think\Controller;

class RecordController extends CommonController {
    
    
    public function index(){
        $this->display();
    }
    
    
    /**  
        * 开始直播
        * @access public
        * @param int  
        * @return  int 
        */
    public function open(){
        $this->display();
    }
    
    /**  
        * 创建直播--处理
        * @access public
        * @param int  
        * @return  int 
        */
    public function create(){
        if(IS_POST){
            $post = I('post.');
            
            //处理数据 补全信息
            $tid = session('uid');
            $room_id = A('Setting')->getRoomId($tid);
            $post['created_time']  = time();
            $post['teacher_id']    = $tid ;
            $post['room_id']       = $room_id;
            $post['start']         = strtotime($post['start_date'].' '. $post['start_time']);  
            $post['rtmp_keys']     = $this->genRtmpKeys($room_id );
        
            
            $result = D('Live')->addData($post, $_FILES['poster']);
            session('live_id', $result);
            $result ? $this->success('创建成功', U('Live/Record/ready'), '1'): $this->error('创建失败', '', 2);
        }   
    }
    
    
    /**  
     * 直播创建成功 
     * @access public
     * @param int  
     * @return  int 
    */
    public function ready(){
        $pre = C('DB_PREFIX');
        $live_id =session('live_id');
        $data =  M('Live')->alias('t1')
                        ->join("left join {$pre}live_room as t2 on t1.room_id = t2.room_id")
                        ->find($live_id);
        //dump($data );die;
        $data['tags'] = explode(',', $data['tags']);
        $this->assign('data', $data);
        $this->display();
        }
   
    
    /**  
     * 生成RTMP Keys
     * @access protected
     * @param 
     * @return  
     */
    protected function genRtmpKeys($room_id){
        $base_str   = generate_rand_string(18);
        $base_str  .= generate_rand_string($room_id);
        $base_str   = strtolower($base_str);
        $keys = substr($base_str, mt_rand(0, floor(strlen($base_str)/5 ) ) , 15 );
        $keys = $room_id .'/'.$keys;
        return $keys;
    }

    /**  
     * 重新生成RTMP Keys
     * @access public
     * @param 
     * @return  
     */
    public function regenRtmpKeys(){
        if(IS_POST){
            $room_id = I('post.roomid');
            $live_id = I('post.liveid');
            $rtmp_keys = $this->genRtmpKeys($room_id);
            $bool = M('Live')->where('id='.$live_id)->setField('rtmp_keys', $rtmp_keys);
            if($bool) $this->ajaxReturn($rtmp_keys );
        }
    }

    
    
    
}