<?php

//1.声明命名空间
namespace Course\Model;

//2.引入父类模型
use Common\Model\BaseModel;

//3.声明模型 继承父类
class CourseQuestionModel extends BaseModel{
	
	
	//自动验证规则
	protected $_validate = array(
		array('stem','require','题干必须！'), 		
		//array('metas','require','选项必须！'),
	);
	
	

	
	
	 
	 
}