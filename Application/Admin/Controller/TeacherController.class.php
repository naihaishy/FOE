<?php
//1.声明命名空间
namespace Admin\Controller;
//2.引入父类控制器
use Think\Controller;
//3.定义类并扩展父类

class TeacherController extends CommonController{
   

    /**  
     * 教师列表
     * @access public
     * @param   
     * @return   
     */
    public function index(){

        $model = M('Teacher');
        
        $page = A('Common/Pages')->getShowPage($model, '' );
        
        $show = $page->show();
        
        $data = $model->limit($page->firstRow,$page->listRows)->select();
        
        $this->assign('show',$show);    //分页输出
        $this->assign('stuinfo',$data);//数据集
        $this->display(); 
    }
    
    /**  
     * 删除教师
     * @access public
     * @param   
     * @return   
     */
    public function delete($id){

      if(empty($id) || !is_numeric($id) || !M('Teacher')->find($id)) $this->error('不存在该教师信息');
      A('Common/Deletes')->doo('user', $id, array('role_id'=>2));

      if(!M('Teacher')->find($id)) $this->success('删除成功', '', 2);
      else $this->error('删除失败', '', 2);
    }
    

    /**  
     * 统计分析图
     * @access public
     * @param   
     * @return   
     */
    public function analyse(){
        
        $this->display('');
    }
    
    
}


