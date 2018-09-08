<?php
return array(
    //'配置项'=>'配置值'
    
    
    /* 模板常量 */
    'TMPL_PARSE_STRING' => array(
        '__ADMIN__' => __ROOT__.'/Public/Admin',// 站点Admin目录
        '__HOME__' => __ROOT__.'/Public/Home',// 站点Home目录
        '__THEME__' => __ROOT__.'/Public/Themes',
        '__PLUGIN__' => __ROOT__.'/Public/Plugins',
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
            
 
    
        
	//OJ系统配置
	'__OJ_DATA__' => '/home/judge/data',
 
 



        
        
        
);