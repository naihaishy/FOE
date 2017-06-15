<?php
namespace Admin\Model;
use Common\Model\BaseModel;
/*
 ** 权限规则 Group Access Model
 */
class AuthGroupAccessModel extends BaseModel{
	
	
	//自动验证规则
	protected $_validate = array(
    array('uid','','该用户已是管理员!',0,'unique',1), // 在新增的时候验证name字段是否唯一
	);
 
 
 /**
	 * 根据group_id获取全部用户id
	 * @param  int $group_id 用户组id
	 * @return array         用户数组
	 */
	public function getUidsByGroupId($group_id){
		$user_ids=$this
			->where(array('group_id'=>$group_id))
			->getField('uid',true);
		return $user_ids;
	}
	
	
 
	 
	 /**
	 * 获取管理员权限列表
	 */
	public function getAllData(){

		$pre  = $this->tableprefix;
		$data=$this
			->field('u.id,u.username,u.truename,aga.group_id,ag.title')
			->alias('aga')
			->join("{$pre}user as u on aga.uid=u.id","RIGHT")
			->join("{$pre}auth_group as ag on aga.group_id=ag.id","LEFT")
			->select();
		// 获取第一条数据
		$first=$data[0];
		$first['title']=array();
		$user_data[$first['id']]=$first;
		// 组合数组
		foreach ($data as $k => $v) {
			foreach ($user_data as $m => $n) {
				$uids=array_map(function($a){return $a['id'];}, $user_data);
				if (!in_array($v['id'], $uids)) {
					$v['title']=array();
					$user_data[$v['id']]=$v;
				}
			}
		}
		// 组合管理员title数组
		foreach ($user_data as $k => $v) {
			foreach ($data as $m => $n) {
				if ($n['id']==$k) {
					$user_data[$k]['title'][]=$n['title'];
				}
			}
			$user_data[$k]['title']=implode('、', $user_data[$k]['title']);
		}
		// 管理组title数组用顿号连接
		return $user_data;

	}
	
	
	

	
 
 
	
}
