<?php
namespace Admin\Controller;
use Think\Controller;
class LiveController extends CommonController{

    /**  
     * 直播列表
     * @access public
     * @param  
     * @return
     */
    public function index(){

        $model = M('Live');
        $page = A('Common/Pages')->getShowPage($model, array('checked'=>1) );
        $show = $page->show();
        $lives   = M('Live')->alias('t1')->field('t1.*,t2.username as teacher_name,t3.name as category_name')
                                        ->join('left join tp_teacher as t2 on t1.teacher_id = t2.id')
                                        ->join('left join tp_live_category as t3 on t1.category_id =t3.id')
                                        ->where(array('t1.checked'=>1))
                                        ->limit($page->firstRow,$page->listRows)
                                        ->order('t1.id asc')
                                        ->select();
        $assign =   array(
                'lives'  =>  $lives,
                'show'  =>  $show,
            );  

        $this->assign($assign);
        $this->display();
    }

    /**  
     * 审核直播
     * @access public
     * @param  
     * @return
     */
    public function check(){
        if(IS_POST){
            $post = I('post.');

            if($post['checked'] ==1){
                $data = array('checked'=>1, 'uncheck_reason'=>'','status'=>'open','release_time'=>time() );
            }else{
                if(!$post['uncheck_reason']) $this->error('需要填写未通过审核的原因');
                $data = array('checked'=>0, 'uncheck_reason'=>$post['uncheck_reason'], 'status'=>'closed' );
            }

            $result = M('Live')->where('id='.$post['id'])->save($data);

            //消息机制
            $live = M('Live')->find($post['id']);
            $source = array(
                    'title' => $live['title'],//直播标题
                    'url'   => 'https://foe.zhfsky.com/index.php/Live/Watch/index/id/'.$live['id'],
                    'reason'=> $post['uncheck_reason'],
             );

            if(!$result===false ){
                //审核成功
                if($post['checked']==1) A('Common/Messages')->send('live', 'check_pass', $source, $live['teacher_id'], 2);
                else                    A('Common/Messages')->send('live', 'check_fail', $source, $live['teacher_id'], 2);
            }

            



            $result=== false ? $this->error('审核失败'):$this->success('审核成功');
        }else{

            $model = M('Live');
            $page = A('Common/Pages')->getShowPage($model, array('checked'=>0 ) );
            $show = $page->show();
            $lives   = M('Live')->alias('t1')->field('t1.*,t2.username as teacher_name,t3.name as category_name')
                                            ->join('left join tp_teacher as t2 on t1.teacher_id = t2.id')
                                            ->join('left join tp_live_category as t3 on t1.category_id =t3.id')
                                            ->limit($page->firstRow,$page->listRows)
                                            ->where("checked=0")
                                            ->order('t1.id asc')
                                            ->select();
                                           
                                            
            $assign =   array(
                    'lives'  =>  $lives,
                    'show'  =>  $show,
                );  

            $this->assign($assign);
            $this->display();
            }
        
    }




    /**  
     * 关闭直播
     * @access public
     * @param  
     * @return
     */
    public function close($id){
        if(empty($id) || !is_numeric($id) || !M('Live')->find($id)) $this->error('不存在此直播'); 
        $result = M('Live')->where('id='.$id)->setField('status','closed');
        //消息机制
        #code 
        $result ?  $this->success('关闭直播成功'):$this->error('关闭直播失败');

    }


    /**  
     * 删除直播
     * @access public
     * @param  
     * @return
     */
    public function delete($id){
        if(empty($id) || !is_numeric($id) || !M('Live')->find($id)) $this->error('不存在此直播'); 
        A('Common/Deletes')->doo('live', $id) ;

        if(!M('Live')->find($id)) $this->success('删除成功', '', 2);
        else $this->error('删除失败', '', 2);

        //消息机制
        #code 
      
    }
    
    
    
        
 




    /**  
     * 直播分类列表
     * @access public
     * @param  
     * @return
     */
    public function category(){
        $this->checkCategoryCount();
        $category   =   M('LiveCategory')->select();
        $this->assign('category', $category);
        $this->display();
    }

    /**  
     * 添加直播分类
     * @access public
     * @param  
     * @return
     */
    public function addCategory(){
        if(IS_POST){
            $post = I('post.');
            //自动验证提交数据
            $rules = array(
                 array('name','require','标题必须有！'),
            );
            $model = M('LiveCategory');
            if(!$model->validate($rules)->create())  $this->error($model->getError(),'',1);

            $result =   M('LiveCategory')->add($post);
            $result ? $this->success('添加成功','',1) : $this->error('添加失败','',2);
        }
    }


    /**  
     * 编辑直播分类
     * @access public
     * @param  
     * @return
     */
    public function editCategory(){
        
        if(IS_POST){
            $post = I('post.');
            //自动验证提交数据
            $rules = array(
                 array('name','require','标题必须有！'),
            );
            $model = M('LiveCategory');
            if(!$model->validate($rules)->create())  $this->error($model->getError(),'',1);
            $result =   M('LiveCategory')->save($post);
            $result ? $this->success('更新成功','',1) : $this->error('更新失败','',2);
        }
        
    }
    

    /**  
     * 删除直播分类
     * @access public
     * @param  
     * @return
     */
    public function delCategory($id){
        $result =   M('LiveCategory')->delete($id);
        //消息机制
        $result ? $this->success('删除成功','',1) : $this->error('删除失败','',2);
    }


    /**  
     * 检查直播分类总数
     * @access private
     * @param  
     * @return
     */
    private function checkCategoryCount(){
        $count  =   M('Live')->alias('t1')
                            ->field('t2.id, count(*) as count ')
                            ->join('left join tp_live_category as t2 on t1.category_id=t2.id')
                            ->group('t2.id asc')
                            ->select();
        //M('Manual')
        foreach($count as $val){
            M('LiveCategory')->where('id='.$val['id'])->setField('count', $val['count']);
        }
        //return $count;
    }


}