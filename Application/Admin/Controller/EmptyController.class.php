<?php
 
namespace Admin\Controller;
 
use Think\Controller;

 
class EmptyController extends Controller{
    
 
    public function _empty(){
         
        $this->display('Empty/404');
    }
    
 
}