<?php
namespace Teacher\Controller;
use Think\Controller;
class NavbarController extends Controller {
    
    private function navbarLeft(){
        $navbar_left['uname']   =   session('uname');
        $navbar_left['logo']    =   'FOE';
        $navbar_left['avatar']  =   M('Teacher')->find(session('uid'))['avatar'];
        session('avatar', $navbar_left['avatar']);
        session('logo',   $navbar_left['logo']);

        $this->assign('navleft',$navbar_left);
    }
        
    private function navbarTop(){
    
        $navbar_top['uname']=session('uname');
        $this->assign('navtop',$navbar_top);
    }
    
    public function navbar(){
        $this->navbarLeft();
        $this->navbarTop();
    }
        
}