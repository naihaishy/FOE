<?php
namespace Admin\Controller;
use Think\Controller;
/**  
 * 后台权限管理 
 */
class AuthorityController extends CommonController{
    
    
    
    /**  
     * 权限规则列表
     * @access public
     * @param   
     * @return   
     */
    public function index(){
        $data=D('AuthRule')->getTreeData('tree','id','title');
        $assign=array(
            'data'=>$data
            );
        $this->assign($assign);
        $this->display();
    }

    /**  
     * 添加权限规则
     * @access public
     * @param   
     * @return   
     */
    public function add(){
        $data=I('post.');
        unset($data['id']);
        $model=D('AuthRule');
        if(!$model->create()){
            $this->error($model->getError(),U('Admin/Authority/index'),1);exit;
        }else{
            $result=$model->addData($data);
            if($result){
                $this->success('添加成功',U('Admin/Authority/index'),1);
            }else{
                $this->error('添加失败',U('Admin/Authority/index'),1);
            }
        }
        
    }

    /**  
     * 编辑权限规则
     * @access public
     * @param   
     * @return   
     */
    public function edit(){
        $data=I('post.');
        $map=array('id'=>$data['id']);
        $result=D('AuthRule')->editData($map,$data);
        if($result){
            $this->success('修改成功',U('Admin/Authority/index'),1);
        }else{
            $this->error('修改失败',U('Admin/Authority/index'),1);
        }
    }
    

    /**  
     * 删除权限
     * @access public
     * @param   
     * @return   
     */
    public function delete(){
        $id=I('get.id');
        $map=array('id'=>$id);
        $result=D('AuthRule')->deleteData($map);
        if($result){
            $this->success('删除成功',U('Admin/Authority/index'),1);
        }else{
            $this->error('删除失败',U('Admin/Authority/index'),1);
        }
    }
    
    
    /*-----------用户组管理-----------*/

    /**  
     * 用户组
     * @access public
     * @param   
     * @return   
     */
    public function group(){
        $data=D('AuthGroup')->getTreeData('tree','id','title');
        $assign=array('data'=>$data);
        $this->assign($assign);
        $this->display();
    }

    /**  
     * 添加用户组
     * @access public
     * @param   
     * @return   
     */
    public function add_group(){
        $data=I('post.');
        unset($data['id']);
        $model = D('AuthGroup');
        if(!$model->create()){
            $this->error($model->getError(),U('Admin/Authority/group'),1);exit;
        }else{
            $result=$model->addData($data);
            if($result){
                $this->success('添加成功',U('Admin/Authority/group'),1);
            }else{
                $this->error('添加失败',U('Admin/Authority/group'),1);
            }
        }

        
         
    }

    /**  
     * 修改用户组
     * @access public
     * @param   
     * @return   
     */
    public function edit_group(){
        $data=I('post.');
        $map=array('id'=>$data['id']);
        $result=D('AuthGroup')->editData($map,$data);
        if($result){
            $this->success('修改成功',U('Admin/Authority/group'),1);
        }else{
            $this->error('修改失败',U('Admin/Authority/group'),1);
        }
    }
    
    /**  
     * 删除用户组
     * @access public
     * @param   
     * @return   
     */
    public function del_group(){
        $id=I('get.id');
        $map=array('id'=>$id);
        $result=D('AuthGroup')->deleteData($map);
        if($result){
            $this->success('删除成功',U('Admin/Authority/group'),1);
        }else{
            $this->error('删除失败',U('Admin/Authority/group'),1);
        }
    }
    
    /*-------------权限-------------*/

    /**  
     * 分配权限
     * @access public
     * @param   
     * @return   
     */
    public function auth_assign(){
        if(IS_POST){
            $data=I('post.');
            $map=array('id'=>$data['id'] );
            $data['rules']=implode(',', $data['rule_ids']);
            $result=D('AuthGroup')->editData($map,$data);
            if ($result){
              $this->success('操作成功',U('Admin/Authority/group'));
            }else{
              $this->error('操作失败');
            }
        }else{
            $id=I('get.id');
          // 获取用户组数据
          $map=array('id'=>$id);
          $group_data=D('AuthGroup')->where($map)->find();
          $group_data['rules']=explode(',', $group_data['rules']);
          // 获取规则数据
          $rule_data=D('AuthRule')->getTreeData('level','id','title');
          $assign=array(
              'group_data'=>$group_data,
              'rule_data'=>$rule_data
              );
          $this->assign($assign);
          //dump($group_data);die;
          $this->display('assign');
            }
        
    }

    /*------------管理员列表------------*/

    /**  
     * 管理员列表
     * @access public
     * @param   
     * @return   
     */
    public function admin(){
        $data = D('AuthGroupAccess')->getAllData();
        $auth_group_data=D('AuthGroup')->select();
        //dump($auth_group_data);die;
        $assign=array(
            'data'=>$data,
            'auth_group_data'=>$auth_group_data
            );
        $this->assign($assign);
        $this->display();
    }
    
    /**  
     * 编辑管理员
     * @access public
     * @param   
     * @return   
     */
    public function edit_admin(){
        if(IS_POST){
            $data   =   I('post.');
            $map    =   array('uid'=>$data['uid']);
            $result =   D('AuthGroupAccess')->editData($map,$data);
            //dump($data);die;
            if($result){
                $this->success('修改成功',U('Admin/Authority/admin'),1);

                //消息机制
                $source['authority']    =   M('AuthGroup')->find($data['group_id'])['title'];
                A('Common/Messages')->send('authority' , 'change', $source, $data['uid'], 3);//消息机制
            }else{
                $this->error('修改失败',U('Admin/Authority/admin'),1);
            }
        }
        
    }
    
    /**  
     * 添加管理员
     * @access public
     * @param   
     * @return   
     */
    public function add_admin(){
        if(IS_POST){
            $data=I('post.');
            $model=D('AuthGroupAccess'); 
            if (!$model->create()){
                $this->error($model->getError(),U('Admin/Authority/admin'),1);
            }else{
                $result = $model->addData($data);
                if($result){
                    $this->success('添加成功',U('Admin/Authority/admin'),1);
                }else{
                    $this->error('添加失败',U('Admin/Authority/admin'),1);
                }
            }
            
        }
    }



    
}