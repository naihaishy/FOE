<?php
namespace Live\Controller;
use Think\Controller;
class RoomController extends Controller {
    
    
   /**  
    *  房间列表
    * 
    * @access public
    * @param int  
    * @return  int 
    */
    public function index(){
        $pre  = C('DB_PREFIX');
        $map = array(
            't1.status'=>'open',
        );
        $where = array('status'=>'open');
        
        $tag = I('get.tag');
        if(!empty($tag)){
            $map['t1.tags'] = $tag;
            $where['tags'] = $tag;
        } 
        //分页
        $model = M('Live');
        $page = A('Common/Pages')->getShowPage($model, $where);
        $show = $page->show();

        $room = M('Live')   ->alias('t1')
                            ->field('t1.*,t2.username as teacher_name')
                            ->join("left join {$pre}teacher as t2 on t1.teacher_id = t2.id")
                            ->limit($page->firstRow,$page->listRows)
                            ->where($map)
                            ->select();

        $this->assign('room', $room);
        $this->assign('show', $show);
        
        $this->display();
    }

    /**  
     *  房间更多直播
     * @access public
     * @param int  id room_id
     * @return  int 
     */
    public function more($id){
        if(empty($id) || !is_numeric($id)) $this->error('非法访问','',2);
        $teacher_id = M('LiveRoom')->find($id)['teacher_id'];
        $teacher    = M('Teacher')->field('username,description')->find($teacher_id);//教师信息

        //分页
        $model = M('Live');
        $page = A('Common/Pages')->getShowPage($model, array('room_id'=>$id, 'status'=>'open') );
        $show = $page->show();

        $lives = M('Live')->where(array('room_id'=>$id, 'status'=>'open'))->limit($page->firstRow,$page->listRows)->select();//直播列表
        $this->assign('lives', $lives);
        $this->assign('teacher', $teacher);
        $this->assign('show', $show);
        $this->display();
    }
    
    
    
    
    
    
    
    
    

    
    
    
}