<?php

//1.声明命名空间
namespace Admin\Model;

//2.引入父类模型
use Think\Model;

//3.声明模型 继承父类
class DocModel extends Model{
	 
	 //saveData 数据的保存
	 public function saveData($post,$file){
	 	//判断是否有文件需要处理
	 	if(!$file['error']){
	 		//定义配置
	 		$config=array(
	 			      'rootPath'      =>  WORKING_PATH . UPLOAD_ROOT_PATH, //保存根路径
	 		);
	 		//处理上传
	 		$upload=new \Think\Upload($config);
	 		//开始上传
	 		$info=$upload->uploadOne($file);
	 		//dump($info);die;
	 		//判断上传是否成功
	 		if($info){
	 			//上传成功 补全信息
	 			$post['filepath']=UPLOAD_ROOT_PATH.$info['savepath'].$info['savename'];
	 			$post['filename']=$info['name'];
	 			$post['hasfile']=1;
	 		}else{
	 			//上传失败
	 			$this->error($upload->getErrorMsg());
	 		}
	 	} 
	 	
	 	$post['addtime']=time();
	 	//添加操作
	 	return $this->add($post);
	 	
	 	
	 }
	 
	 
}