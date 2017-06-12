<?php
namespace Live\Controller;

use Think\Controller;

class SettingController extends CommonController {
    
    
   /**  
     *  参数设置
     * 
     * @access public
     * @param int  
     * @return  int 
     */
    public function index(){
        $tid = session('uid');
        $setting = M('LiveRoom')->where('teacher_id='.$tid)->find();
        $this->assign('data', $setting);
        $this->display();
    }
    
    /**  
     * 房间id生成 
     * 生成规则: 教师ID + 6位随机数字
     * @access private
     * @param int  
     * @return  boolean 
     */
    private function initRoomId($tid){
        
        if(!$tid) $tid = session('uid');
        if( $this->checkRoom($tid) ) exit;//已经存在则退出初始化
        
        $num = generate_numcode(6); //生成6位随机数字
        $room_id =  $tid . $num;
        $data =  array(
            'room_id'       => $room_id,
            'teacher_id'    => $tid,
            'rtmp'          => 'rtmp://fms.zhfsky.com/live/',
        );
        $result = M('LiveRoom')->add($data);
        if($result){
            return true;
        }else{
            return false;
        }
        
    }
    
    /**  
     * 房间id 获取 
     * @access private
     * @param int  tid 教师id
     * @return  int 
     */
    public function getRoomId($tid){
        $tid ? : session('uid');//没有指定tid则从session中取
        $info = M('LiveRoom')->field('room_id')->where('teacher_id='.$tid)->find();
        if(empty($info)){
            $bool = $this->initRoomId($tid);//不存在则初始化
            empty($bool) ? $this->initRoomId($tid):''; //初始化失败再次初始化
            $info = M('LiveRoom')->field('room_id')->where('teacher_id='.$tid)->find();
        }
         
        return $info['room_id'];
    }
        
    /**  
     * 检查房间
     * @access private
     * @param int  tid 教师id
     * @return  int 
     */
    private function checkRoom($tid){
        $has_exist = M('LiveRoom')->where('teacher_id='.$tid)->find();
        if($has_exist){
            return true;
        }else{
            return false;
        } 
    }
    
    

    
    
    
}