<?php
namespace Common\Controller;
use Think\Controller;


/**
 * 用户相关通用处理类 
 */
class UsersController extends Controller {

    

    /**  
     * 获取某一用户信息 
     * @access public
     * @param  uid rid field
     * @return
     */
    public function getUserInfo($uid, $rid, $field=''){
        
        if($rid===3){
            //学生
            $user   =  M('Stu')->field('id, username as name, sex, email, avatar')->find($uid);
        }elseif ($rid===2) {
            //教师
            $user   =  M('Teacher')->field('id, username as name, sex , email, avatar')->find($uid);
        }elseif ($rid===1) {
            //管理员
            $user   =  M('User')->field('id, username as name, sex, email, avatar')->find($uid);
        }
        return $user;
    }


    /**  
     * 获取用户在线信息 
     * @access public
     * @param  uid rid 
     * @return on off
     */
    public function getOnlineInfo($uid, $rid){

        switch ($rid) {
            case '3':
                $session_id = M('Stu')->find($uid)['session_id'];
                break;
            case '2':
                $session_id = M('Teacher')->find($uid)['session_id'];
                break;
            case '1':
                $session_id = M('User')->find($uid)['session_id'];
                break;
            default:
                # code...
                break;
        }

        $map = array('session_id'=>$session_id, 'session_expire'=>array('gt',NOW_TIME), 'session_data'=>array('neq','') );
        $res = D('Session')->where($map)->find();
        if($res) return 'on'; else return 'off';
        
    }

    /**  
     * 获取用户所有朋友及基本信息
     * @access public
     * @param  uid rid 
     * @return on off
     */
    public function getFriendsInfo($uid, $rid){

        $map = array('user_id'=>$uid, 'role_id'=>$rid, 'status'=>'friend');

        $friends    = M('Relation')->where($map)->select();

        foreach ($friends as $key => &$value) {
            $value['online'] = A('Common/Users')->getOnlineInfo($value['friend_uid'], $value['friend_rid']);
            if($value['friend_rid']==3){
                //学生
                $value['friend_info'] =  M('Stu')->field('id, username as name, sex, email, avatar')->find($value['friend_uid']);
                $value['chat']['series'] = $uid.'_'.$rid.'_'.$value['friend_uid'].'_'.$value['friend_rid'];
            }elseif ($value['friend_rid']==2) {
                //教师
                $value['friend_info'] =  M('Teacher')->field('id,username as name, sex , email, avatar')->find($value['friend_uid']);
                $value['chat']['series'] = $uid.'_'.$rid.'_'.$value['friend_uid'].'_'.$value['friend_rid'];
            }elseif ($value['friend_rid']==1) {
                //管理员
                $value['friend_info'] =  M('User')->field('id, username as name, sex, email, avatar')->find($value['friend_uid']);
                $value['chat']['series'] = $uid.'_'.$rid.'_'.$value['friend_uid'].'_'.$value['friend_rid'];
            }
        }

        return $friends;

    }


    /**  
     * 获取用户所有粉丝及基本信息
     * @access public
     * @param  uid rid 
     * @return on off
     */
    public function getFansInfo($uid, $rid){

        $map = array('friend_uid'=>$uid, 'friend_rid'=>$rid, 'status'=>'follow');
        $fans    = M('Relation')->where($map)->select();

        foreach ($fans as $key => &$value) {

            $value['online'] = A('Common/Users')->getOnlineInfo($value['user_id'], $value['role_id']);
            
            if($value['role_id']==3){
                //学生
                $value['friend_info'] =  M('Stu')->field('id, username as name, sex, email, avatar')->find($value['user_id']);
                $value['chat']['series'] = $uid.'_'.$rid.'_'.$value['user_id'].'_'.$value['role_id'];

            }elseif ($value['role_id']==2) {
                //教师
                $value['friend_info'] =  M('Teacher')->field('id,username as name, sex , email, avatar')->find($value['user_id']);
                $value['chat']['series'] = $uid.'_'.$rid.'_'.$value['user_id'].'_'.$value['role_id'];

            }elseif ($value['role_id']==1) {
                //管理员
                $value['friend_info'] =  M('User')->field('id, username as name, sex, email, avatar')->find($value['user_id']);
                $value['chat']['series'] = $uid.'_'.$rid.'_'.$value['user_id'].'_'.$value['role_id'];
            }
        }

        return $fans;

    }

    /**  
     * 获取用户所有关注及基本信息 我关注的
     * @access public
     * @param  uid rid 
     * @return on off
     */
    public function getFollowersInfo($uid, $rid){

        $map = array('user_id'=>$uid, 'role_id'=>$rid, 'status'=>'follow');

        $followers    = M('Relation')->where($map)->select();

        foreach ($followers as $key => &$value) {
            $value['online'] = A('Common/Users')->getOnlineInfo($value['friend_uid'], $value['friend_rid']);
            if($value['friend_rid']==3){
                //学生
                $value['friend_info'] =  M('Stu')->field('id, username as name, sex, email, avatar')->find($value['friend_uid']);
                $value['chat']['series'] = $uid.'_'.$rid.'_'.$value['friend_uid'].'_'.$value['friend_rid'];

            }elseif ($value['friend_rid']==2) {
                //教师
                $value['friend_info'] =  M('Teacher')->field('id,username as name, sex , email, avatar')->find($value['friend_uid']);
                $value['chat']['series'] = $uid.'_'.$rid.'_'.$value['friend_uid'].'_'.$value['friend_rid'];

            }elseif ($value['friend_rid']==1) {
                //管理员
                $value['friend_info'] =  M('User')->field('id, username as name, sex, email, avatar')->find($value['friend_uid']);
                $value['chat']['series'] = $uid.'_'.$rid.'_'.$value['friend_uid'].'_'.$value['friend_rid'];

            }
        }

        return $followers;

    }




}