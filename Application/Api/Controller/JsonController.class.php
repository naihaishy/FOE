<?php
namespace Api\Controller;
use Think\Controller\RestController;
class JsonController extends RestController  {


    public function live(){
        $data = M('Live')->where("status='open'")->field('title, description, poster_uri as image')->select();
        foreach ($data as $key => $value) {
            $data[$key]['image'] = "https://foe.zhfsky.com".$value['image'];
            $data[$key]['description']  =  $this->cleandata($value['description']);

        }

        $return = array('status'=>'ok','data'=>$data, 'msg'=>'success');

        $this->response($return,'json');  
    }


    public function classroom(){

        $data = M('Course')->where("status = 'published'")->field('title, description, picture_path as image')->select();
        foreach ($data as $key => $value) {
            $data[$key]['image'] = "https://foe.zhfsky.com".$value['image'];
            $data[$key]['description']  =  $this->cleandata($value['description']);
        }
        
        $return = array('status'=>'ok','data'=>$data, 'msg'=>'success');
       

        $this->response($return,'json');  
    }

    private function cleandata($str){
        return trim(strip_tags(htmlspecialchars_decode($str)));
    }



   










}