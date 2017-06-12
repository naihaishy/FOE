<?php
namespace Files\Controller;
use Think\Controller;

/**
*  文件上传 通用处理类
*   
*/
class UploadController extends Controller {
    
 
   /**  
    * 通用文件上传  单个文件上传
    * @access public 
    * @param  
    * @return  file_id 上传文件的id  error不返回
    */
   public function common($file, $domain='', $type=''){
        $filetype = '';
        if($type=='image') $filetype =  array('jpg', 'gif', 'png', 'jpeg', 'bmp');
        
        $result =   D('Files/Files')->upload($file , $domain, $filetype);

        if(is_numeric($result)) return $result;
   }
   
   

   
   
   
}