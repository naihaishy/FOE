<?php

namespace Common\Controller;
use Think\Controller;

class ManualsController extends Controller{
    
    
    /**  
     * 手册列表
     * @access public
     * @param  
     * @return
     */
    public function index(){
        
        $manual = $this->getManuals();
        $show   = $this->getManualShowPage();


        $this->assign('show',$show);    //分页输出链接
        $this->assign('manual',$manual);
        $this->display();
    }
    
    
    
    /**  
     * 添加手册
     * @access public
     * @param  
     * @return
     */
    public function add(){
        if(IS_POST){
            
            //自动验证提交数据
            $rules = array(
                 array('title','require','标题必须有！'),
                 array('type','require','所属类型必须确定！'),
                 array('content','require','内容不得为空!'),
            );
            $model = M('Manual');
            if(!$model->validate($rules)->create())  $this->error($model->getError(),'',1);

            $post = I('post.');
            $post['created_user'] = session('uid').','.session('rid');
            $post['created_time'] = time();
            $post['updated_time'] = time();
            $post['content'] = htmlspecialchars_decode($post['content']);
            $result = M('Manual')->add($post);
            $result ? $this->success('添加成功'): $this->error('添加失败');
            
        }else{
            $category = M('ManualCategory')->select();
            $this->assign('category', $category);
            $this->display();
        }
    }   
    
    /**  
        * 修改手册
        * @access public
        * @param  
        * @return
        */
    public function edit($id){
        if(empty($id)) exit;
        if(IS_POST){
            $post = I('post.');
            //自动验证提交数据
            $rules = array(
                 array('title','require','标题必须有！'),
                 array('type','require','所属类型必须确定！'),
                 array('content','require','内容不得为空!'),
            );
            $model = M('Manual');
            if(!$model->validate($rules)->create())  $this->error($model->getError(),'',1);

            $post['updated_time'] = time();
            $post['content'] = htmlspecialchars_decode($post['content']);
            $result = M('Manual')->where('id='.$id)->save($post);
            $result ? $this->success('保存成功'): $this->error('保存失败');
            
        }else{
            $manual = M('Manual')->find($id);
            $category = M('ManualCategory')->select();
            $this->assign('manual', $manual);
            $this->assign('category', $category);
            $this->display();
        }
    }
    
    
    
    /**  
        * 删除手册
        * @access public
        * @param  
        * @return
        */
    public function delete($id){
        if(empty($id) || !is_numeric($id) || !M('Manual')->find($id)) $this->error('不存在此手册'); 
        $result = M('Manual')->where('id='.$id)->delete();
        $result ===false ?  $this->error('删除失败'):$this->success('删除成功');
    }
    
    
    
        
    /**  
        * 查看手册
        * @access public
        * @param  
        * @return
        */
    
    public function view($id, $map=''){
        if(empty($id)) exit; 
        if(empty($map)){
            $manual = M('Manual')->find($id);
        }else{
            $manual = M('Manual')->where($map)->find($id);
        }
        if($manual){
            $manual['tags'] = explode(',', $manual['tags']);
            //dump($manual);die;
            $this->assign('manual', $manual);
            //$this->assign('tags', $manual['tags']);
            $this->display();
        }else{
            $this->error('不存在此手册');
        } 
        
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    protected function getManuals(){
        $pre = C('DB_PREFIX');
        $manual = M('Manual') ->alias('t1')
                                ->field('t1.*,t2.name as category_name')
                                ->join("left join {$pre}manual_category as t2 on t1.category_id = t2.id")
                                ->select();
        return $manual;
    }
    
    
    protected function getManualShowPage(){
       
        $page = A('Common/Pages')->getShowPage( M('Manual'), '');

        $show = $page->show();
        
        return $show;
    }
    
    
    
    
}