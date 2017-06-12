<?php

function sayhello(){
    echo 'Hello world';
}

/**
 *定义站点名称
 *@return 返回站点链接
 */
function sitesname(){
    return "<a href='https://foe.zhfsky.com'>Free Online Education</a>";
}

/**
 * 系统邮件发送函数
 * @param string $to    接收邮件者邮箱
 * @param string $name  接收邮件者名称
 * @param string $subject 邮件主题 
 * @param string $body    邮件内容
 * @param string $attachment 附件列表
 * @return boolean 
 */
function think_send_mail($to, $name, $subject = '', $body = '', $attachment = null){
    $config = C('THINK_EMAIL');
    vendor('PHPMailer.class#smtp'); //从PHPMailer目录导class.smtp.php类文件
    vendor('PHPMailer.class#phpmailer'); //从PHPMailer目录导class.phpmailer.php类文件
    $mail             = new PHPMailer(); //PHPMailer对象
    $mail->CharSet    = 'UTF-8'; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
    $mail->IsSMTP();  // 设定使用SMTP服务
    $mail->SMTPDebug  = 0;                     // 关闭SMTP调试功能
                                               // 1 = errors and messages
                                               // 2 = messages only
    $mail->SMTPAuth   = true;                  // 启用 SMTP 验证功能
    $mail->SMTPSecure = 'ssl';                 // 使用安全协议
    $mail->Host       = $config['SMTP_HOST'];  // SMTP 服务器
    $mail->Port       = $config['SMTP_PORT'];  // SMTP服务器的端口号
    $mail->Username   = $config['SMTP_USER'];  // SMTP服务器用户名
    $mail->Password   = $config['SMTP_PASS'];  // SMTP服务器密码
    $mail->SetFrom($config['FROM_EMAIL'], $config['FROM_NAME']);
    $replyEmail       = $config['REPLY_EMAIL']?$config['REPLY_EMAIL']:$config['FROM_EMAIL'];
    $replyName        = $config['REPLY_NAME']?$config['REPLY_NAME']:$config['FROM_NAME'];
    $mail->AddReplyTo($replyEmail, $replyName);
    $mail->Subject    = $subject;
    $mail->MsgHTML($body);
    $mail->AddAddress($to, $name);
    if(is_array($attachment)){ // 添加附件
        foreach ($attachment as $file){
            is_file($file) && $mail->AddAttachment($file);
        }
    }
    return $mail->Send() ? true : $mail->ErrorInfo;
}

/**
    *上传文件名 命名规则 暂且只用于Course
    */
function file_save_name($filename){
    $filename=substr($filename,0,strrpos($filename,'.'));//去掉后缀名 .xxx
    return strtolower(generate_password(6)).'_'.$filename;
}


/**
 * 密码生成函数
 *  
 */

function generate_password( $length = 10 ) {
    
    // 密码字符集，可任意添加你需要的字符
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    $password = '';
    for ( $i = 0; $i < $length; $i++ ) 
    {
        // 这里提供两种字符获取方式
        // 第一种是使用 substr 截取$chars中的任意一位字符；
        // 第二种是取字符数组 $chars 的任意元素
        // $password .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        $password .= $chars[ mt_rand(0, strlen($chars) - 1) ];
    }
    return $password;
}


/**
 * 字符串生成函数
 *  
 */

function generate_rand_string( $length = 10 ) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $string = '';
    for ( $i = 0; $i < $length; $i++ ) {
        $string .= $chars[ mt_rand(0, strlen($chars) - 1) ];
    }
    return $string;
}

/**
 * 生成指定位数的数字
 *  
 */
function generate_numcode($length) {
    
    // 密码字符集，可任意添加你需要的字符
    $nums = '0123456789';

    $code = '';
    for ( $i = 0; $i < $length; $i++ ) {
        $code .= $nums[ mt_rand(0, strlen($nums) - 1) ];
    }
    return $code;
}


/**
    **返回上一页
    */
function goBack(){
        echo "<script>window.history.back(-2);</script>";
    }


/**
 *记录当前页面URI
 */
 
function record_current_uri(){
    $url = $_SERVER['REQUEST_SCHEME'].$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    $_SESSION['current_uri']=$url;
    //return $url;
}


/**
 * 阿里短信验证码发送函数
 * @param string $tel              接收短信者手机号码箱
 * @param string $verify_code  接收验证码
 * @return boolean  
 */
function sms_verf_code($tel,$verify_code){
    
     vendor('AliSMS.class#alisms'); //从AliSMS目录导class.alisms.php类文件
     $alisms =new AliSMS();
     
     if( empty($tel) || empty($verify_code) ||  !is_numeric($verify_code) || !is_numeric($tel) ){
        die('参数错误');
     }
     
    $nameString     =   array('verifynum'=>$verify_code);  //模板变量
    $SmsParamString =   json_encode($nameString);      //发送的字符串
    
    //获取阿里短信的配置
    $alisms_config  =   C('ALISMS_CONFIG');//二维数组
    $app_key        =   $alisms_config['ALISMS_APP_KEY'];        //app_key
    $app_secret     =   $alisms_config['ALISMS_APP_SECRET'];     //app_secret
    
    $request_paras = array(
            'ParamString' => $SmsParamString ,
            'RecNum'    => $tel ,
            'SignName'  =>'萘海', 
            'TemplateCode' => 'SMS_47950204'
            );
            
    $request_host =     $alisms_config['ALISMS_APP_REQUEST_HOST'];  
    $request_uri =      $alisms_config['ALISMS_APP_REQUEST_URI'];   
    $request_method = $alisms_config['ALISMS_APP_REQUEST_METHOD'];  

    $content    = $alisms->do_get($app_key, $app_secret, $request_host, $request_uri, $request_method, $request_paras);
    $result     = json_decode($content, true);
    if($result['success']==true) return true;
    else return false;
   
}

/**
 * 字符串截取，支持中文和其他编码 直接从TP库中导入
 * @static
 * @access public
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 * @return string
 */
 
function msubstr($str, $start, $length){
    import('Org.Util.String');
    $string =new String();
    $string->msubstr($str, $start, $length);
}

function subtext($text, $length)
{
    if(mb_strlen($text, 'utf8') > $length) 
    return mb_substr($text, 0, $length, 'utf8').'...';
    return $text;
}




/**
 * formatDate  将格式化时间转成unix时间戳 03/16/2017 ->unix时间戳
 * @access public
 * @param string $date 需要转换的时间
 * @return string
 */
function formatDate($date){
         if (strpos($date,'/') !== false){
             $date = str_replace('/', '-', $date);
             $date = date('Y-m-d', strtotime($date));
         }else{
            $date = date('d-m-Y', strtotime($date));
            $date = str_replace('-', '/', $date);
         }
    return $date;
}


/**
 * 数组转xls格式的excel文件
 * @param  array  $data      需要生成excel文件的数组
 * @param  string $filename  生成的excel文件名
 *      示例数据：
        $data = array(
            array(NULL, 2010, 2011, 2012),
            array('Q1',   12,   15,   21),
            array('Q2',   56,   73,   86),
            array('Q3',   52,   61,   69),
            array('Q4',   30,   32,    0),
           );
 */
function create_xls($data,$filename='simple.xls'){
    ini_set('max_execution_time', '0');
    Vendor('PHPExcel.PHPExcel');
    $filename=str_replace('.xls', '', $filename).'.xls';
    $phpexcel = new PHPExcel();
    $phpexcel->getProperties()
        ->setCreator("Naihai 赵海峰")
        ->setLastModifiedBy("Naihai 赵海峰")
        ->setTitle("Office 2007 XLSX Test Document")
        ->setSubject("Office 2007 XLSX Test Document")
        ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
        ->setKeywords("office 2007 openxml php")
        ->setCategory("Test result file");
    $phpexcel->getActiveSheet()->fromArray($data);
    $phpexcel->getActiveSheet()->setTitle('Sheet1');
    $phpexcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment;filename=$filename");
    header('Cache-Control: max-age=0');
    header('Cache-Control: max-age=1');
    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header ('Pragma: public'); // HTTP/1.0
    $objwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel5');
    $objwriter->save('php://output');
    exit;
}


//QQ login
function qqlogin(){
    vendor('QQConnect.API.qqConnectAPI'); //从 QQConnect/API/ 目录下导入 qqConnectAPI.php文件
    $qc = new QC();
    $qc->qq_login();
} 

function qqcallback(){
    vendor('QQConnect.API.qqConnectAPI'); //从 QQConnect/API/ 目录下导入 qqConnectAPI.php文件
    $qc = new QC();
    $calldata=array();
    $acs = $qc->qq_callback();//callback主要是验证 code和state,返回token信息，并写入到文件中存储，方便get_openid从文件中度  
    $oid = $qc->get_openid();//根据callback获取到的token信息得到openid,所以callback必须在openid前调用  
    
    //实例化QC对象
    $qc         = new QC($acs,$oid);
    $user_info  = $qc->get_user_info();
    $calldata['oauth']['access_token']  =   $acs;
    $calldata['oauth']['openid']        =   $oid;
    $calldata['userinfo']               =   $user_info;
    return $calldata;
} 




 //简单判断是否登录
 function is_login(){

    $uid = $_SESSION['uid'];
    $rid = $_SESSION['rid'];
    if($uid && $rid){
        return true;
    }else{
        return false;
    }
}


/**
 * 判断是否为教师 直接从TP库中导入
 * @access public
 * @return boolean 返回布尔值
 */

function is_teacher(){

    $uid = session('uid');
    $rid = session('rid');
    if($uid && $rid =='2'){
        return true;
    }else{
        return false;
    }
}

function is_student(){
    $uid = session('uid');
    $rid = session('rid');
    if($uid && $rid == '3'){
        return true;
    }else{
        return false;
    }
}

function is_admin(){
    $uid = session('uid');
    $rid = session('rid');
    if($uid && $rid =='1'){
        return true;
    }else{
        return false;
    }
}




//OJ 系统生成sapmle 文件
function mkdata($pid, $filename, $input){
    $basedir = "/home/judge/data/$pid";
    $fp = @fopen ( $basedir . "/$filename", "w" );
    if($fp){
        fputs ( $fp, preg_replace ( "(\r\n)", "\n", $input ) );
        fclose ( $fp );
    }else{
        echo "Error while opening".$basedir . "/$filename ,try [chgrp -R www-data $OJ_DATA] and [chmod -R 771 $OJ_DATA ] ";     
    }
}

//格式化各种列表中content
function format_list_content($str){
     
}



/**  
 * 计算距离过去时间差e
 * @param int  time uniux时间戳
 * @return  mix 
*/
function past_time($time){
    
}
 


/**  
 * 获取系统设置
 * @param  
 * @return  
*/
function get_option($name){
    $map = array('name'=> $name );
    return M('Setting')->where($map)->find()['value'];
}

function get_group_options($group=''){
    $map = array('group'=> $group );
    return M('Setting')->where($map)->select();
}


function pass_time($time){
    $distance = time() - $time; //秒
    if($distance < 60) return '刚刚'; //一分钟以内
    elseif ( floor($distance/60) < 60 ) return  floor($distance/60).'分钟前'; //一小时以内
    elseif ( floor($distance/3600) < 60 ) return  floor($distance/3600).'小时前'; //一天以内
    elseif ( floor($distance/86400) < 7 ) return  floor($distance/86400).'天前'; //一星期以内
    else return date('Y年m月d日 H:i:s', $time); 
}

