<?php
namespace Admin\Model;
use Common\Model\BaseModel;
/**
 * 菜单操作model
 */
class AdminNavModel extends BaseModel{

     
    /**
     * 删除数据
     * @param   array   $map    where语句数组形式
     * @return  boolean         操作是否成功
     */
    public function deleteData($map){
        //防止直接删除父级菜单
        $count=$this
            ->where(array('pid'=>$map['id']))
            ->count();
        if($count!=0){
            return false;
        }
        $this->where(array($map))->delete();
        return true;
    }
    
    
     


}
