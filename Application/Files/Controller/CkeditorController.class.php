<?php
namespace Files\Controller;
use Think\Controller;

/**
	*	Ckeditor相关文件处理类
	*	
	*/
class CkeditorController extends Controller {
	
	
	
	
	
   /**  
	* ckeditor的文件上传
	* @access  
	* @param  
	* @return  
	*/
   public function upload($type='', $domain=''){
   	$filetype = '';
   	if($type=='image') $filetype =  array('jpg', 'gif', 'png', 'jpeg', 'bmp');
   	if(IS_POST){
 		$domain = I('get.domain');
 		$callback = I('get.CKEditorFuncNum');
 		$result =	D('Files')->upload($_FILES['upload'], $domain, $filetype);
 		if($result){
 			$info	=	D('Files')->find($result);
 			$savepath	=	$info['uri'];
 			echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($callback,'".$savepath."','');</script>";  
 		}else{
 			//$error	= "上传失败"; //result返回错误
 			echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($callback,'".$result."','');</script>";
 		}

	 	}
    
   }
   
   

   
   
   
}