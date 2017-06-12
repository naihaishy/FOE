<?php
namespace Teacher\Controller;

use Common\Controller\ManualsController;

class HelpController extends ManualsController {
    
    /**
     * 手册分类列表--教师
     * @access public
     * @param    
     * @return    
     */
    public function index(){

        $manualscat =  M('ManualCategory')->select();
        $manualcount = M('Manual')->count();
        $this->assign('helpcat', $manualscat);
        $this->assign('manualcount', $manualcount);
        $this->display();
    }


    /**
     * 分类列表
     * @access public
     * @param    
     * @return    
     */
    public function category($id){

        if(empty($id) || !is_numeric($id)) $this->error('非法访问','',1);

        $category = M('ManualCategory')->find($id);
        $map = array(
                'category_id'=>$id,
                'type'=> array('in','common,teacher'),
            );

        $manuals = M('Manual')->field('content', true)->where($map)->select();
        foreach ($manuals as $key => &$value) {
            $value['tags'] =  explode(',', $value['tags']);
        }
        
        //dump($manuals);die;
        $this->assign('manuals', $manuals);
        $this->assign('category', $category);
        $this->display();
    }
    
    

    
    
    
}