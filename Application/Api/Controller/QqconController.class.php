<?php
namespace Api\Controller;
use Think\Controller;
class QqconController extends Controller{
    
    
    public function login(){
        qqlogin();
    }

    
    public function callback(){
        
        $call_data  = qqcallback();
        $acs        = $call_data['oauth']['access_token'];
        $oid        = $call_data['oauth']['openid'];
        $user_info  = $call_data['userinfo'];
        //dump($call_data);die;
        
        //写入数据库
        $oauth['access_token']  =    $call_data['oauth']['access_token'];
        $oauth['openid']        =    $call_data['oauth']['openid'];
        
        $data['user_name']      =   $user_info['nickname'];
        $data['sex']            =   $user_info['gender'];
        $data['last_login_time'] = time();
        $data['last_login_ip']   = get_client_ip();
        $data['session_id'] = session_id();
        
        $map = array('openid'=> $call_data['oauth']['openid']);
        
        $check = M('Oauth')->where($map)->find();
        
        if($check ){
            //已经存在oauth记录 更新Stu表数据
            $condition = array('oauth_id'=>$check['id'] );
            $result = M('Stu')->where($condition)->save($data);
            $query  = M('Stu')->field('id')->where($condition)->find();
            $id     = $query['id'];
            //dump($result);die;
        }else{
            //创建oauth记录
            $oauth_id =  M('Oauth')->add($oauth);
              //将该用户存入Stu表中 
              //补全信息
             $data['user_registered']=date('Y-m-d H:i:s',time());
             $data['account_from']='qq';
             $data['oauth_id']=$oauth_id;
             $result = M('Stu')->add($data);//返回主键id
             $id=$result;
             //dump($result);die;
        }
        
        if($result){
            //成功
            $stuinfo = M('Stu')->find($id);
            $avatar = $stuinfo['avatar'];
            session('uid', $id);
            session('rid', 3);
            session('uname', $user_info['nickname']);
            session('avatar', $avatar);
            session('last_login_time', $stuinfo['last_login_time']);
            session('last_login_ip', $stuinfo['last_login_ip']);

            //消息机制
            A('Common/Messages')->send('account', 'login', '', session('uid'), session('rid') );//消息机制
 
 
            

            echo "<script>
            window.opener.location.href = window.opener.location.href;
            window.close();
            </script>";
        }
        
    }
    
    
    
    
}

