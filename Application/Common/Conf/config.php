<?php
return array(
    //'配置项'=>'配置值'
    
    /* 模板常量 */
        'TMPL_PARSE_STRING' => array(
        '__ADMIN__' => __ROOT__.'/Public/Admin',// 站点Admin目录
        '__HOME__' => __ROOT__.'/Public/Home',// 站点Home目录
        ),
        
        /*应用常量配置 */
        'ALLOW_SEND_EMAIL_AFTER_REG' => false,//执行添加信息发送邮箱至注册邮箱

        'LOAD_EXT_CONFIG'       =>  'setting,oauth',         //加载网站设置文件
        
        /*用户角色id配置*/
        'ADMIN_ROLE_ID'=>'1',
        'TEACHER_ROLE_ID'=>'2',
        'STUDENT_ROLE_ID'=>'3',
        
    /* 数据库设置 */
    'DB_TYPE'               =>  ' ',     // 数据库类型
    'DB_HOST'               =>  ' ', // 服务器地址
    'DB_NAME'               =>  ' ',          // 数据库名
    'DB_USER'               =>  ' ',      // 用户名
    'DB_PWD'                =>  ' ',          // 密码
    'DB_PORT'               =>  ' ',        // 端口
    'DB_PREFIX'             =>  ' ',    // 数据库表前缀
    'SHOW_PAGE_TRACE'       => true, // 显示页面Trace信息

    //sessinDb 配置
    'SESSION_TYPE'      =>  'mysqli',
    'SESSION_TABLE'     =>  'tp_session',
    'SESSION_EXPIRE'    =>  1200,
            
 
        
 
        
        //邮件配置
        'THINK_EMAIL' => array(
            'SMTP_HOST'   => ' ', //SMTP服务器
            'SMTP_PORT'   => ' ', //SMTP服务器端口 ssl加密
            'SMTP_USER'   => ' ', //SMTP服务器用户名
            'SMTP_PASS'   => ' ', //SMTP服务器密码
            'FROM_EMAIL'  => ' ', //发件人EMAIL
            'FROM_NAME'   => ' ', //发件人名称
            'REPLY_EMAIL' => '', //回复EMAIL（留空则为发件人EMAIL）
            'REPLY_NAME'  => '', //回复名称（留空则为发件人名称）
        ),
        
        //阿里短信验证码配置 
        'ALISMS_CONFIG' =>array(
                                
                                
            'ALISMS_APP_KEY' => ' ',
            'ALISMS_APP_SECRET' => ' ',
            'ALISMS_APP_REQUEST_HOST' => ' ',
            'ALISMS_APP_REQUEST_URI' =>'',
            'ALISMS_APP_REQUEST_METHOD' => ' ',                               
                                
        ),
        
        //OJ系统配置
        '__OJ_DATA__' => '/home/judge/data',
 
 



        
        
        
);