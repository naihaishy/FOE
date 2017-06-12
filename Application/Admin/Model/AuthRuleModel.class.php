<?php
namespace Admin\Model;
use Common\Model\BaseModel;
/*
 ** 权限规则 Rule Model
 */
class AuthRuleModel extends BaseModel{
	
	
	//自动验证规则
	protected $_validate = array(
		array('title','require','权限名必须！'), //默认情况下用正则进行验证
	  	array('name','require','权限必须！'), //默认情况下用正则进行验证
    	array('name','','该权限已经存在!',0,'unique',1), // 在新增的时候验证name字段是否唯一
	);
	
	
	/**
	 * 删除数据
	 * @param	array	$map	where语句数组形式
	 * @return	boolean			操作是否成功
	 */
	public function deleteData($map){
		//防止直接删除上级权限
		$count=$this
			->where(array('pid'=>$map['id']))
			->count();
		if($count!=0){
			return false;
		}
		$result=$this->where($map)->delete();
		return $result;
	}
	
}
