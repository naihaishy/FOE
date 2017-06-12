<?php

namespace Admin\Controller;
use Common\Controller\ManualsController;

class ManualController extends ManualsController{
    

    /**  
     * 初始化
     * @access public
     * @param  
     * @return
     */
    public function _initialize(){
        A('Common')->_initialize();
    }
    
    /**  
     * 手册分类列表
     * @access public
     * @param  
     * @return
     */
    public function category(){
        $this->checkCategoryCount();
        $category   =   M('ManualCategory')->select();
        //dump($count);die;
        $this->assign('category', $category);
        $this->display();
    }

    /**  
     * 添加手册分类
     * @access public
     * @param  
     * @return
     */
    public function addCategory(){
        if(IS_POST){
            $post = I('post.');
            $result =   M('ManualCategory')->add($post);
            $result ? $this->success('添加成功','',1) : $this->error('添加失败','',2);
        }
    }
    /**  
     * 编辑手册分类
     * @access public
     * @param  
     * @return
     */
    public function editCategory(){
        
        if(IS_POST){
            $post = I('post.');
            $result =   M('ManualCategory')->save($post);
            $result ? $this->success('更新成功','',1) : $this->error('更新失败','',2);
        }
        
    }
    

    /**  
     * 删除手册分类
     * @access public
     * @param  
     * @return
     */
    public function delCategory($id){
        $result =   M('ManualCategory')->delete($id);
        $result ? $this->success('删除成功','',1) : $this->error('删除失败','',2);
    }

    /**  
     * 检查手册分类总数
     * @access private
     * @param  
     * @return
     */
    private function checkCategoryCount(){
        $count = M('Manual')->alias('t1')
                            ->field('t2.id, count(*) as count ')
                            ->join('left join tp_manual_category as t2 on t1.category_id=t2.id')
                            ->group('t2.id asc')
                            ->select();
        //M('Manual')
        foreach($count as $val){
            M('ManualCategory')->where('id='.$val['id'])->setField('count', $val['count']);
        }
        //return $count;
    }




    
    
    
}