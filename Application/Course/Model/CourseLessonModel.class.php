<?php
namespace Course\Model;
use Common\Model\BaseModel;

class CourseLessonModel extends BaseModel{
	
	
	//自动验证规则
	protected $_validate = array(
		array('title','require','课时标题必须！'),
	);

	public function addData($post,$file,$cid){
		//数据分为 文件+字符
		//判断是否有文件需要处理
		if($file['error']=='0' ){
			//进行文件处理
			//定义配置
	 		$config=array(
	 			      'rootPath'  => WORKING_PATH . UPLOAD_ROOT_PATH, //保存根路径 服务器
	 			      'savePath'  =>'Course/'.$cid.'/',
	 			      'saveName' 	=> array('file_save_name','__FILE__'),
	 		);
	 		//处理上传
	 		$upload=new \Think\Upload($config);//实例化上传类
	 		//开始上传
	 		$info=$upload->uploadOne($file);
	 		//判断上传是否成功
	 		if($info){
	 			//上传成功 补全信息
	 			$post['media_uri'] = UPLOAD_ROOT_PATH.$info['savepath'].$info['savename'];//文件的路径
	 			$post['media_name']=$info['name'];//文件名
	 		}else{
	 			//上传失败
	 			$this->error($upload->getErrorMsg());
	 		}
		}
		
		//补全信息
		$post['course_id']	 =$cid;
		$post['created_time']=time();
		$post['updated_time']=time();
	 	//添加操作
	 	return $this->add($post);
	}
	
	
}
