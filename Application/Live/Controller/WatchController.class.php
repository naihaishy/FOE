<?php
namespace Live\Controller;
use Think\Controller;
class WatchController extends Controller {
    
    
   /**  
        *  观看
        * 
        * @access public
        * @param int  
        * @return  int 
        */
    public function index(){
        $id = I('get.id') ? : exit();  //live id

        $pre = C('DB_PREFIX');
        $live =  M('Live')->alias('t1')
                        ->field('t1.*,t2.username as teacher_name,t2.description as teacher_description,t2.avatar as teacher_avatar')
                        ->join("left join {$pre}teacher as t2 on t1.teacher_id = t2.id")
                        ->where('t1.id='.$id)
                        ->find();
        $live['is_start'] = $this->startCheck($id);
        $room = M('LiveRoom')->where('room_id='. $live['room_id'])->find();
        $tags = $this->getTags();
        $similar_video = $this->getSimilarVideo($live['tags'], $live['id']);

        $user = array('uname'=>session('uname'),'uid'=>session('uid') );
        $vod_uri = json_encode(explode(',', $live['vod_uri']), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $messages = A('Live/Chat')->messages($id);

        $assign = array(
                'live'           =>$live,
                'room'           =>$room,
                'tags'           =>$tags,
                'user'           =>$user,
                'similar_video'=>$similar_video,
                'vod_uri'       =>$vod_uri,
                'messages'      =>$messages,
            );
        
        $this->assign($assign);
        //dump($vod_uri);die;
        $this->display();
    }
    
    
    /**  
        *  获取所有标签
        * 
        * @access private
        * @param 
        * @return  
        */
    private function getTags(){
        $map = array('status'=>'open');
        $tags = M('Live')->field('tags')->where($map)->distinct(true)->select();
        return $tags;
    }
    
    /**  
        *  获取相似直播
        *  标签/分类/标题 等作为相似度检测的依据
        * @access private
        * @param tag 
        * @param id 直播id 
        * @return  
        */
    private function getSimilarVideo($tag, $id){
        $map = array(
            'tags'=> $tag,
            'id'=> array('not in',$id),
        );
        return  M('Live')->where($map)->limit(5)->select();
    }
    
    
    private function SimilarityCheck(){
        
    }
    
    
    private function overCheck($live_id){
        $live = M('Live')->find($live_id);
        if($live['is_over']) return true;
        $timestep = 8*60*60;
        if(($live['start'] + $timestep) <= time()){
            M('Live')->where('id='.$live_id)->setField('is_over',1);
            return true;
        }
    }
    
    private function startCheck($live_id){
        $live = M('Live')->find($live_id);
        if($live['is_over']) return false;
        if($live['start']  <= time() )  return true;
    }


    /**  
     *  获取所有标签
     * @access private
     * @param 
     * @return  
     */

    
    
    

    
    
    
}