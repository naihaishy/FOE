<?php

namespace Api\Controller;


use Think\Controller\RestController;


class UserController extends RestController  {


    /**  
    * 注册
    * @access public 
    * @param  
    * @return 
    */
    public function register(){

        checkRequestMethod();  

    }


    /**  
    * 登录
    * @access public 
    * @param  email password
    * @return 
    */
    public function login(){

        //checkRequestMethod();

        if($this->_method=='post'){
            $post = I("post.");
            $model = M('Stu');
            $salt = $model->field('salt')->where(array('email'=>$post['email']))->find()['salt'];
            $post['password'] = md5($post['password'].$salt);
            $data = $model->where($post)->find();

            if($data){
                $returnArr = array(
                    'status' => 1,
                    'message'=>'登录成功',
                    'data' => $data,
                );
                
            }else{
                $returnArr = array(
                    'status' => 0,
                    'message'=>'用户名或密码错误',
                );
             
            }

            $this->response($returnArr, 'json');
        }
    }



}