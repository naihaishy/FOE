<?php

//1.声明命名空间
namespace Course\Model;

//2.引入父类模型
use Common\Model\BaseModel;

//3.声明模型 继承父类
class CourseModel extends BaseModel{
    
    
    //自动验证规则
    protected $_validate = array(
        array('title','require','课程名必须！'), //默认情况下用正则进行验证
        array('title','','该课程已经存在!',0,'unique',1), // 在新增的时候验证name字段是否唯一
    );
    
     
     
     
     
    /**
     * 获取课程分类 名称+ID
     * @access public
     * @param int $id 课程id
     * @return array 分类名+ID
     */
    public function getCourseCategory($id=''){
      $pre = $this->tableprefix;
      if(empty($id)){
          //获取全部分类
          $result = M('CourseCategory')->field('name,id')->select();
      }else{
          $result = M('CourseCategory')->alias('t1')
                                      ->field('t1.name,t1.id')
                                      ->join("left join {$pre}course as t2 on t2.category_id = t1.id")
                                      ->where('t2.id='.$id)
                                      ->select();
      }
      return $result;
    }
      
      
      
     //上传课程图片
     public function upload($file,$cid){
        //判断是否有文件需要处理
        if(!$file['error']){
            //定义配置
            $config=array(
                      'rootPath'  => WORKING_PATH . UPLOAD_ROOT_PATH, //保存根路径 服务器
                      'savePath'  =>'Course/'.$cid.'/',
                      'saveName'    => array('file_save_name','__FILE__'),
            );
            //处理上传
            $upload=new \Think\Upload($config);
            //开始上传
            $info = $upload->uploadOne($file);
            //dump($info);die;
            //判断上传是否成功
            if(!$info){
                $this->error($upload->getErrorMsg());//上传失败
            }else{
                 //上传成功 补全信息
                $post['picture_path']=UPLOAD_ROOT_PATH.$info['savepath'].$info['savename'];
                $post['picture_name']=$info['name'];
                $post['has_picture']=1;
            }
        }
        
        //添加操作
        return $this->where('id='.$cid)->save($post);
         
     }
      
      
      
      
     
     
}