<?php
/**
 * 安装向导
 */
header('Content-type:text/html;charset=utf-8');


// 检测是否安装过
if (file_exists('./install.lock')) {
    echo '你已经安装过该系统,如果需要重新安装请先删除./Public/Install/install.lock 文件';
    die;
}

// 安装协议

if(!isset($_GET['s']) || $_GET['s']=='agree'){
    require './agree.html';
}
// 环境检测
if($_GET['s']=='check'){
    require './check.html';
}
// 创建数据库页面
if($_GET['s']=='config'){
    require './config.html';
}

// 配置 最后一步

if($_GET['s']=='last'){

    if($_SERVER['REQUEST_METHOD']=='POST'){
        $post=$_POST;
        // 连接数据库
        $link   =   new mysqli("{$post['DB_HOST']}:{$post['DB_PORT']}",$post['DB_USER'],$post['DB_PWD']);
        // 获取错误信息
        $error  =   $link->connect_error;
        if (!is_null($error)) {
            $error=addslashes($error);// 转义防止和alert中的引号冲突
            die("<script>alert('数据库链接失败:$error');history.go(-1)</script>");
        }
        // 设置字符集
        $link->set_charset("utf8");
        $link->server_info > 5.0 or die("<script>alert('请将您的mysql升级到5.0以上');history.go(-1);</script>");
        // 创建数据库并选中
        if(!$link->select_db($post['DB_NAME'])){
            $create_sql =   'CREATE DATABASE IF NOT EXISTS '.$post['DB_NAME'].' DEFAULT CHARACTER SET utf8;';
            $link->query($create_sql) or die('创建数据库失败');
            $link->select_db($post['DB_NAME']);
        }
        // 导入sql数据并创建表
        $sql_str    =   file_get_contents('./tp3.sql');
        $sql_array  =   preg_split("/;[\r\n]+/", str_replace('tp_',$post['DB_PREFIX'], $sql_str));
        foreach ($sql_array as $k => $v) {
            if (!empty($v)) {
                $link->query($v);
            }
        }
        $link->close();
        $db_str=<<<php
<?php
return array(

//*************************************数据库设置*************************************
    'DB_TYPE'               =>  'mysqli',                 // 数据库类型
    'DB_HOST'               =>  '{$post['DB_HOST']}',     // 服务器地址
    'DB_NAME'               =>  '{$post['DB_NAME']}',     // 数据库名
    'DB_USER'               =>  '{$post['DB_USER']}',     // 用户名
    'DB_PWD'                =>  '{$post['DB_PWD']}',      // 密码
    'DB_PORT'               =>  '{$post['DB_PORT']}',     // 端口
    'DB_PREFIX'             =>  '{$post['DB_PREFIX']}',   // 数据库表前缀
);
php;
        // 创建数据库链接配置文件
        file_put_contents('../../Application/Common/Conf/aaa.php', $db_str);
        @touch('./install.lock');
        require './success.html';
    }

}
