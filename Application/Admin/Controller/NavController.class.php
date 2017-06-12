<?php

//1.定义命名空间
namespace Admin\Controller;
//2.引入父类控制器
use Think\Controller;

//3.定义控制器并且继承父类

/**
 * 后台菜单管理
*/
class NavController extends CommonController{
	
	//菜单列表
	public function index(){
		$data=D('AdminNav')->getTreeData('tree','order_number,id');
		$assign=array(
			'data'=>$data
			);
		$this->assign($assign);
		$this->display();
	}
	
	
	//添加菜单 
	public function add(){
		$data=I('post.');
		/*if(empty($data['name']) || empty($data['mca'])){
			$this->error('不得为空','',1);
		}*/
		$result=D('AdminNav')->addData($data);
		if ($result) {
			$this->success('添加成功',U('Admin/Nav/index'));
		}else{
			$this->error('添加失败');
		}
	}
	
	//修改菜单 
	public function edit(){
		$data=I('post.');
		if(empty($data['name']) or empty($data['mca'])){
			$this->error('不得为空','',1);die;
		}
		$map=array('id'=>$data['id']);
		$result=D('AdminNav')->editData($map,$data);
		if ($result) {
			$this->success('修改成功',U('Admin/Nav/index'));
		}else{
			$this->error('修改失败');
		}
	}
	
	//删除菜单
	public function delete($id){
		$map=array('id'=>$id);
		$result=D('AdminNav')->deleteData($map);
		if ($result) {
			$this->success('删除成功',U('Admin/Nav/index'));
		}else{
			$this->error('删除失败');
		}
	}
	
	//菜单排序
	public function order(){
		$data=I('post.');
		$result=D('AdminNav')->orderData($data);
		if ($result) {
			$this->success('排序成功',U('Admin/Nav/index'));
		}else{
			$this->error('排序失败');
		}
	}
 
	
	
}


 

 
