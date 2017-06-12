<?php

namespace Live\Model;
use Think\Model;

class LiveModel extends Model{
    
    
    /**  
        * 创建直播
        * @access public
        * @param int  
        * @return  int 
        */
    public function addData($post, $file, $room_id){
        //判断是否有文件需要处理
        if($file['error']=='0' ){
            //进行文件处理
            //定义配置
            $config=array(
                      'rootPath'  => WORKING_PATH . UPLOAD_ROOT_PATH, //保存根路径 服务器
                      'savePath'  =>'Live/'.$room_id.'/',
                      'saveName'    => array('file_save_name','__FILE__'),
            );
            //处理上传
            $upload=new \Think\Upload($config);
            $info=$upload->uploadOne($file);
            if($info){
                //上传成功 补全信息
                $post['has_poster']  = 1 ;
                $post['poster_name'] = $info['name'];//文件名
                $post['poster_uri']  = UPLOAD_ROOT_PATH . $info['savepath'] . $info['savename'];//文件的路径
                
            }else{
                //上传失败
                $this->error($upload->getErrorMsg());
            }
        }
        
        
        
        //添加操作
        return $this->add($post);
    }
    
    
    /**  
        * 更新直播
        * @access public
        * @param int  
        * @return  int 
        */
    public function editData($post, $file, $room_id){
        //判断是否有文件需要处理
        if($file['error']=='0' ){
            //进行文件处理
            //定义配置
            $config=array(
                      'rootPath'  => WORKING_PATH . UPLOAD_ROOT_PATH, //保存根路径 服务器
                      'savePath'  =>'Live/'.$room_id.'/',
                      'saveName'    => array('file_save_name','__FILE__'),
            );
            //处理上传
            $upload=new \Think\Upload($config);
            $info=$upload->uploadOne($file);
            if($info){
                //上传成功 补全信息
                $post['has_poster']  = 1 ;
                $post['poster_name'] = $info['name'];//文件名
                $post['poster_uri']  = UPLOAD_ROOT_PATH . $info['savepath'] . $info['savename'];//文件的路径
                
            }else{
                //上传失败
                $this->error($upload->getErrorMsg());
            }
        }
        
        
        
        //添加操作
        return $this->save($post);
    }


    /**  
     * 更新直播
     * @access public
     * @param int  
     * @return  int 
     */
    public function addVod($file, $room_id, $live_id){
        //判断是否有文件需要处理
        if(!$file['error']){
            $config=array(
                      'rootPath'  => WORKING_PATH . UPLOAD_ROOT_PATH, //保存根路径 服务器
                      'savePath'  =>'Live/'.$room_id.'/',
                      'saveName'    => array('file_save_name','__FILE__'),
            );
            $upload=new \Think\Upload($config);
            $info = $upload->upload($file);
            if(!$info){
                $this->error($upload->getErrorMsg());//上传失败
            }else{
                //上传成功 补全信息 获取上传文件信息 多文件

                $uris = $this->find($live_id)['vod_uri'];
                foreach($info as $file){
                   $uri  = UPLOAD_ROOT_PATH.$file['savepath'].$file['savename'];
                   if(empty($uris)) $uris = $uri;
                   $uris = $uris.','.$uri;
                }
                
            }
        } 
        return $this->where(array('id'=>$live_id) )->setField('vod_uri', $uris);
     }


    
    
    
    
}