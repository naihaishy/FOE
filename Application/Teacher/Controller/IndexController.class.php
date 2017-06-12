<?php
namespace Teacher\Controller;
use Think\Controller;
class IndexController extends CommonController {
    
    public function index(){
        A('Navbar')->navbar();
        
 
        

        $this->display();
    }
    
    
 

    /**  
     * 账号退出
     * @access public
     * @param   
     */
    public function logout(){
        A('Common/Accounts')->logout();
    }
    
    
}