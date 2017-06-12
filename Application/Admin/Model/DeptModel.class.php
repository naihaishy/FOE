<?php

//1.声明命名空间
namespace Admin\Model;

//2.引入父类模型
use Think\Model;

//3.声明模型 继承父类
class DeptModel extends Model{
	//开启批量验证
	//protected $patchValidate= true;
	//字段映射自定义
	protected $_map  =array(
		//映射规则
		//键是表单中的name值 = 值是数据库中字段名
		'abc'=>'name',
		'dip'=>'pid'
		
		
	);
	//自动验证定义
	protected $_validate        =   array(
		array('name','require','名字不能为空!'),
		array('name','','名字已经存在',0,'unique'),
		array('pid','is_numeric','必须是数字',0,'function'),
		
	); 
}