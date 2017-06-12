<?php
namespace Files\Model;
use Think\Model;

class FilesModel extends Model{


	/**  
	* 文件上传处理 单个
	* @access public
	* @param  
	* @return  
	*/
	public function upload($file, $domain, $filetype){
		//判断是否有文件需要处理
		if(!$file['error']){
			//进行文件处理
			//定义配置
	 		$config=array(
	 			      'rootPath'  	=> WORKING_PATH . UPLOAD_ROOT_PATH, //保存根路径 服务器
	 			      'savePath'  	=>'Common/'.$domain.'/',
	 			      'saveName' 	=> array('file_save_name','__FILE__'),
	 			      'exts'		=> $filetype,
	 		);
	 		//处理上传
	 		$upload =	new \Think\Upload($config);//实例化上传类
	 		$info	=	$upload->uploadOne($file);
	 		//判断上传是否成功
	 		if($info){
	 			//上传成功 补全信息
	 			$data	=	array(
	 							'uri'	=>	UPLOAD_ROOT_PATH.$info['savepath'].$info['savename'],//文件的路径
	 							'name'	=>	$info['name'],
	 							'type'	=>	$info['type'],
	 							'size'	=>	$info['size'],
	 							'domain'=>	$domain,
	 							'upload_time'	=>	time(),
	 							'upload_user'	=>	session('uid').','.session('rid'),


	 				);
	 			//添加操作
	 			return $this->add($data);//id 为数字 

	 		}else{
	 			//上传失败
	 			return $upload->getError();//返回错误信息  通过is_numeric($result)判断成功或失败
	 		}
		}
		
	 	
	}







}