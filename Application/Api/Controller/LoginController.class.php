<?php
namespace Api\Controller;
use Think\Controller;

class LoginController extends Controller{

    // 第三方平台登录
    public function index(){
        $type   =   I('get.type');
        import("Org.ThinkSDK.ThinkOauth");
        $sdk    =   \ThinkOauth::getInstance($type);
        redirect($sdk->getRequestCodeURL());
    }

}
