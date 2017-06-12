<?php
//1.声明命名空间
namespace Admin\Controller;
//2.引入父类控制器
use Think\Controller;
//3.定义类并扩展父类

class StudentController extends CommonController{
   

    /**  
     * 学员列表
     * @access public
     * @param   
     * @return   
     */
    public function index(){

        $model = M('Stu');
        
        $page = A('Common/Pages')->getShowPage($model, '' );
        
        $show = $page->show();
        
        $data = $model->limit($page->firstRow,$page->listRows)->select();
        
        $this->assign('show',$show);    //分页输出
        $this->assign('stuinfo',$data);//数据集
        $this->display(); 
    }
    
    /**  
     * 删除学生
     * @access public
     * @param   
     * @return   
     */
    public function delete($id){

      if(empty($id) || !is_numeric($id) || !M('Stu')->find($id)) $this->error('不存在该学生');
      A('Common/Deletes')->doo('user', $id, array('role_id'=>3));

      if(!M('Stu')->find($id)) $this->success('删除成功', '', 2);
      else $this->error('删除失败', '', 2);
    }
    

    /**  
     * 学员统计分析图
     * @access public
     * @param   
     * @return   
     */
    public function analyse(){
        
        $this->display('');
    }




 
/*  //datatable通用返回
  public function dataTable($options=array()){
      $options =  $this->_parseOptions($options);
      $this->options=$options;
      $data=I('get.');
      $filteredCount=$this->count();
      $count=$this->count();
      $list=$this->limit($data['start'],$data['length'])->select($options);

      if(empty($list)){
          $list=[];
      }

      $_res = [
          'data'=>$list,
          'recordsTotal'=>$count,
          'recordsFiltered'=>$filteredCount
      ];
      return $_res;
  }*/
  
  
/*  public function test(){
    
    $model=M('Stu');
    $options=array(
            
    );
    $data = $model->order('id desc')->dataTable($options);
    $this->ajaxReturn($data);
  }*/
    
    


    
}


