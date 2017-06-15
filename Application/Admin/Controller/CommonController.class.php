<?php

//1.声明命名空间
namespace Admin\Controller;
//2.引入父类控制器
use Think\Controller;
//3.定义类并扩展父类

class CommonController extends Controller{
    
    /**  
     * 验证用户登录及权限认证
     * @access public
     * @param   
     * @return   
     */
    public function _initialize(){
        $uid = session('uid');
        //判断用户是否登录
        if(empty($uid)){
            //用户没有登录
            $this->error('请先登录',U('Admin/Public/login'),2);
        }
        //检查账号是否锁定
        if($this->checklock()){
            $this->error('对不起 您的账号已经被管理员锁定.请直接联系 Naihai [abc@zhfsky.com]进行申诉',U('Public/login'),6);exit;
        }

        //权限认证
        $auth   =   new \Think\Auth();
        $rule_name  =   MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME;
        $result =   $auth->check($rule_name,$uid);
        if(!$result)   $this->error('您没有权限访问');

        //显示菜单
        $sidebar = $this->adminSidebarLeft();

        //dump($sidebar);die;
        $emailur  = $this->getEmailUnreadCount();
         
        $this->assign('sidebar',  $sidebar);
        $this->assign('emailur',  $emailur);

        
    }
    
    /**  
     * 检测账号 锁定 状态
     * @access public
     * @param   
     * @return   
     */
     private function checklock(){
        if(session('uid')){
            $uid=session('uid');
            $user_status = M('User')->field('locked')->find($uid);
            if($user_status['locked']==1){
                return true;//锁定
            }else{
                return false;
            }
        }else{
            return false;
        }
        
     }

     /**  
     * 检测账号 激活 状态
     * @access public
     * @param   
     * @return   
     */
    private function checkEn(){
        if(session('uid')){
            $id=session('uid');
            $user_status = M('User')->field('enabled')->find($uid);
            if($user_status['enabled']==1){
                return true;//激活
            }else{
                return false;
            }
        }else{
            return false;
        }

    }


    /**  
     * 管理员左侧菜单
     * @access public
     * @param  
     * @return
     */
    public function adminSidebarLeft(){

        $pre = C('DB_PREFIX');

        $rules = M('AuthGroupAccess')->alias('t1')
                                    ->join("left join {$pre}auth_group as t2 on t2.id = t1.group_id")
                                    ->where(array('t1.uid'=>session('uid')))
                                    ->getField('rules');

        $sidebar_allow  = M('AuthRule')->where(array('id'=>array('in', $rules)))->select();
        $sidebar = D('AdminNav')->where(array('pid'=>0))->order('order_number asc')->select();//一级菜单 无mca 
        foreach ($sidebar as $key => $value) {
            $sidebar[$key]['subsidebar'] = D('AdminNav')->where(array('pid'=>$value['id']))->select();
            foreach ($sidebar[$key]['subsidebar']  as $key2 => $value2) {
                $flag = 'no';
                foreach ($sidebar_allow as $key3 =>  $value3) {
                    if($value2['mca']==$value3['name']){
                        $flag ='yes';
                    }
                }
                if($flag=='no'){
                    unset($sidebar[$key]['subsidebar'][$key2]);
                }
            }

            //如果二级菜单全部不允许
            if(!$sidebar[$key]['subsidebar']){
                unset($sidebar[$key]); //删除相应与以及菜单
            }

 
        }

        return $sidebar;

       
    }



    /**  
     * 邮箱
     * @access public
     * @param  
     * @return
     */
    public function getEmailUnreadCount(){
        $map = array(
            'isread'=>0, 
            'to_uid'=>session('uid'),
            'to_rid'=>session('rid'),
        );
        return M('Email')->where($map)->count();
    }
    
    
    
}


