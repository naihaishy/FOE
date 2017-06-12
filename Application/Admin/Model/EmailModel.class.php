<?php

//1.声明命名空间
namespace Admin\Model;
//2.引入父类
use Think\Model;
//3.定义类并且扩展父类
class EmailModel extends Model{
	
	//addData
	public function addData($post,$file){
		//数据分为 文件+字符
		//判断是否有文件需要处理
		if($file['error']=='0' ){
			//进行文件处理
			//定义配置
	 		$config=array(
	 			      'rootPath'      =>  WORKING_PATH . UPLOAD_ROOT_PATH, //保存根路径
	 		);
	 		//处理上传
	 		$upload=new \Think\Upload($config);//实例化上传类
	 		//开始上传
	 		$info=$upload->uploadOne($file);
	 		//判断上传是否成功
	 		if($info){
	 			//上传成功 补全信息
	 			$post['file'] = UPLOAD_ROOT_PATH.$info['savepath'].$info['savename'];//文件的路径
	 			$post['filename']=$info['name'];//文件名
	 			$post['hasfile']=1;//是否有文件 
	 		}else{
	 			//上传失败
	 			$this->error($upload->getErrorMsg());
	 		}
		}
		
		//无文件处理 补全信息
		$post['from_id']=session('id');//发件人的id
		$post['sendtime']=time();//发送事件
	 	//添加操作
	 	return $this->add($post);
		
		
	}
	
	
	
	
}