<?php

//1.声明命名空间
namespace Course\Model;

//2.引入父类模型
use Think\Model;

//3.声明模型 继承父类
class CourseFilesModel extends Model{
	 
	 //saveData 数据的保存
	 public function saveData($post,$file,$cid){
	 	//判断是否有文件需要处理
	 	if(!$file['error']){
	 		//定义配置
	 		
	 		$config=array(
	 			      'rootPath'  => WORKING_PATH . UPLOAD_ROOT_PATH, //保存根路径 服务器
	 			      'savePath'  =>'Course/'.$cid.'/',
	 			      'saveName' 	=> array('file_save_name','__FILE__'),
	 		);
	 		//处理上传
	 		$upload=new \Think\Upload($config);
	 		//开始上传
	 		$info = $upload->upload($file);
	 		//dump($info);die;
	 		//判断上传是否成功
	 		if(!$info){
	 			$this->error($upload->getErrorMsg());//上传失败
	 		}else{
	 			//上传成功 补全信息 获取上传文件信息 多文件
	 			$fid='';
	 			foreach($info as $file){
		 		   $post['title']=$file['name'];
		 		   $post['uri']  = UPLOAD_ROOT_PATH.$file['savepath'].$file['savename'];
		 		   $post['size'] =$file['size'];
		 		   $post['type'] =$file['type'];
		 		   $post['created_time']=time();
		 		   $fid = $this->add($post) .','.$fid;
  		  		}
	 			
	 		}
	 	} 
	 	return $fid;
	 }
	 
	 
}