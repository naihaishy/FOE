<?php
namespace Admin\Model;
use Common\Model\BaseModel;
/*
 ** 权限规则 用户组 Model
 */
class AuthGroupModel extends BaseModel{
	
	//自动验证规则
	protected $_validate = array(
    array('title','','该用户组已经存在!',0,'unique',1), // 在新增的时候验证name字段是否唯一
	);
	
	
	/**
	 * 传递主键id删除数据
	 * @param  array   $map  主键id
	 * @return boolean       操作是否成功
	 */
	public function deleteData($map){
		$result =$this->where($map)->delete();
		$group_map=array(
			'group_id'=>$map['id']
			);
		// 删除关联表中的组数据
		//$result=D('AuthGroupAccess')->deleteData($group_map);
		return $result;
	}
	
	
}
