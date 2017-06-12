<?php

//1.定义命名空间
namespace Teacher\Controller;
//2.引入父类控制器
use Think\Controller;

//3.定义控制器并且继承父类
class EmptyController extends Controller{
	
 
	public function _empty(){
		//展示模板
		$this->display('Empty/404');
	}
	
 
}