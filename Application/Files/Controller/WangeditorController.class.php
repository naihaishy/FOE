<?php
namespace Files\Controller;
use Think\Controller;

/**
    *   wangEditor相关文件处理类
    *   
    */
class WangeditorController extends Controller {
    
    
    
    
    
   /**  
    * wangEditor的文件上传
    * @access  public
    * @param  
    * @return  
    */
   public function upload($type='', $domain=''){
    $filetype = '';
    if($type=='image') $filetype =  array('jpg', 'gif', 'png', 'jpeg', 'bmp');
    if(IS_POST){
        $domain = I('get.domain');
        $result =   D('Files')->upload($_FILES['upload'], $domain, $filetype);
        if(is_numeric($result)){
            $info   =   D('Files')->find($result);
            $savepath   =   $info['uri'];
            echo  $savepath;
        }else{
            //$error    = "上传失败"; //result返回错误
            echo $result;
        }

    }
    
   }
   
   

   
   
   
}