<?php
namespace Live\Controller;

use Think\Controller;

class HelpController extends Controller {


    /**  
    *  
    * @access public
    * @param int  
    * @return  int 
    */
    public function index(){
        $this->redirect('Teacher/Help/category/id/1');
    }




}