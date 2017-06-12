<?php
namespace Live\Controller;

use Think\Controller;

class StaticsController extends Controller {


    /**  
    *   
    * @access public
    * @param int  
    * @return  int 
    */

    public function index(){
        
    }



    /**  
    * 直播统计详情
    * @access public
    * @param int  
    * @return  int 
    */

    public function detail($id=''){
        if(empty($id) || !is_numeric($id) || ! M('Live')->find($id) ) $this->error('不存在该直播');

        $live = M('Live')->find($id);

        $this->assign('live', $live);
        $this->display();

    }

    public function getData(){
        if(IS_POST){
            $id = I('post.id');
            $info = M('Live')->find($id);
            $data = array('time'=>time(), 'data'=>$info['watch_num']);
            $this->ajaxReturn( $data);
        }
    }




}