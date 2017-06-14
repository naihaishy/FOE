<?php
//1.定义命名空间
namespace Admin\Controller;
//2.引入父类控制器
use Think\Controller;

//3.定义控制器并且继承父类
class TestController extends Controller{
    //测试
    public function test(){
        //echo 'Hello World';
        //定义变量
        $var =date('Y-m-d H:i:s',time());
        //变量分配
        $this->assign('var',$var);
        //
        $this->display();
    }
    public function test1(){
        //echo U('index');
        $this->display('test');
    }
    public function test2(){
        //echo U('Index/index');
        $this->display('Demo/test');
    }
    public function test3(){
        echo U('Index/index',array('id'=>'100'));
    }
    public function test4(){
        //成功挑战
        $this -> success('操作成功',U('test'),10);
    }
    public function test5(){
        //模板常量的展示
        $this -> display();
    }
    public function test6(){
        //一维数组
        $array=array('西游记','aaa','bbb');
        //二维数组
        $array2=array(
                        array('a','b','c'),
                        array('松江南','含糊','解决'),
                        array('a发发发','读锁定b','大酒店c')
        );
        //变量分配
        $this->assign('array',$array);
        $this->assign('array2',$array2);
        //展示模板
        $this->display();
    }
    
    public function test7(){
        $stu =new Student();
        $stu->id=1;
        $stu->name="小明";
        //变量分配
        $this->assign('stu',$stu);
        //展示模板
        $this->display();
        
    }
    
    public function test8(){
        //展示模板
        $this->display();
        
    }
    
    public function test9(){
        //定义变量
        $time=time();
        $str='ashdfklsasjkf';
        //变量分配
        $this->assign('time',$time);
        $this->assign('str',$str);
        //展示模板
        $this->display();
        
    }
    public function test10(){
        //一维数组
        $array=array('西游记','aaa','bbb');
        //二维数组
        $array2=array(
                        array('a','b','c'),
                        array('松江南','含糊','解决'),
                        array('a发发发','读锁定b','大酒店c')
        );
        //变量分配
        $this->assign('array',$array);
        $this->assign('array2',$array2);
        //展示模板
        $this->display();
    }
    public function test11(){
        $day=date('N',time());
        //变量分配
        $this->assign('day',$day); 
        //展示模板
        $this->display();
    }
    
    //sql调试
    public function test12(){
        //实例化模型
        $model= M('Dept');
        $model->select();
        echo $model->getLastSql();
        echo $model->_sql();
    }
    //性能测试
    public function test13(){
        //定义开始标记
        G('start');
        
        
        //结束标记 
        G('end');
        G('start','end',4);
    }
    
    //AR模式 add
    public function test14(){
        //实例化模型 第一个映射 类映射到表 (类关联表)
        $model=M('Dept');
        //第二个映射 属性映射到字段
        $model->name='xiaomingss';
        $model->pid='0';
        //第三个映射 实例映射到记录
        $result=$model->add(); //返回值表示新增记录的主键id 
        dump($result);
    }
    
    //AR模式 修改操作
    public function test15(){
        //实例化模型 第一个映射 类映射到表 (类关联表)
        $model=M('Dept');
        //第二个映射 属性映射到字段
        $model->id='8';//主键信息
        $model->name='xioahong';
        $model->pid='2';
        //第三个映射 实例映射到记录
        $result=$model->save();//返回值表示影响的行数 
        dump($result);
    }
    
    //AR模式 删除操作
    public function test16(){
        //实例化模型
        $model=M('Dept');
        //指定主键信息
        $model->id='2,12';
        //执行删除操作
        $result=$model->delete();
        dump($result);
    }
    
    //where 
    public function test17(){
        //实例化模型
        $model=M('Dept');
        //执行操作
        $model->where('id > 8');
        $res=$model->select();
        //$model->where('id > 8')->select(); 
        dump($res);
    }
    //limit
    public function test18(){
        //实例化模型
        $model=M('Dept');
        //执行操作
        //$model->limit(7);
        $model->limit(3,6);
        $res=$model->select();
        dump($res);
    }
    //field
    public function test19(){
        //实例化模型
        $model=M('Dept');
        //执行操作
        $model->field('name');
        $res=$model->select();
        dump($res);
    }
    //order
    public function test20(){
        //实例化模型
        $model=M('Dept');
        //执行操作
        $model->order('id desc');
        $res=$model->select();
        dump($res);
    }
    
    //group
    public function test21(){
        //实例化模型
        $model=M('Dept');
        //执行操作
        $model->field('name,count(*) as count');
        $model->group('name');
        $res=$model->select();
        dump($res);
    }
    //连贯操作
    public function test22(){
        //实例化模型
        $model=M('Dept');
        //执行操作
        $res=$model->field('name,count(*) as count')->group('name')->select();
        dump($res);
    }
    
    //统计查询
    public function test23(){
        //实例化模型
        $model=M('Dept');
        //执行操作
        //$res=$model->count();
        //$res=$model->max('id');
        $res=$model->sum('id');
        dump($res);
    }
    
    //fecthSql
    public function test24(){
        //实例化模型
        $model=M('Dept');
        //执行操作
        $res=$model->fetchSql()->field('nameasas,s,as,,a..as..sacoassasunt(*) as count')->group('name')->select();
        //并不执行sql语句 返回值 SQL语句
        dump($res);
        
    }
    //session
    public function test25(){
        //1.设置
        session('name','abc');
        dump($_SESSION);    
    }
    //cookie
    public function test26(){
        //1.设置
        cookie('name','abc');
        //dump($_COOKIE);   
    }
    //加载文件 --函数库形式加载
    public function test27(){
        sayhello();
    }
    //常规验证码
    public function test28(){
        //实例化验证码
        $config=array(
              'fontSize'  =>  25,              // 验证码字体大小(px)
        'useCurve'  =>  true,            // 是否画混淆曲线
        'useNoise'  =>  true,            // 是否添加杂点  
        'length'    =>  4,               // 验证码位数
        'fontttf'   =>  '4.ttf',              // 验证码字体，不设置随机获取
        );
        $verify=new \Think\Verify($config);
        //输出验证码
        $verify->entry();
    }
    //中文验证码
    public function test29(){
        //实例化验证码
        $config=array(
                'useZh'     =>  true,           // 使用中文验证码 
              'fontSize'  =>  25,              // 验证码字体大小(px)
        'useCurve'  =>  true,            // 是否画混淆曲线
        'useNoise'  =>  true,            // 是否添加杂点  
        'length'    =>  4,               // 验证码位数
        'fontttf'   =>  'msyh.ttc',              // 验证码字体，不设置随机获取
        );
        $verify=new \Think\Verify($config);
        //输出验证码
        $verify->entry();
    }
    
    
    //关联表查询  原生SQL语句 
    public function test30(){
        $model=M();//执行原生sql语句可以不要关联表
        $sql="select t1.*,t2.name as deptname from tp_user as t1,tp_dept as t2 where t1.dept_id=t2.id ";
        $result=$model->query($sql);
        dump($result);
    }
    
    //关联表查询  table
    public function test31(){
        $model=M('User');//在使用table方法之后模型会自动关联上table方法中指定的数据表。
        //连贯操作
        $result = $model->field('t1.*,t2.name as deptname')->table('tp_user as t1,tp_dept as t2')->where('t1.dept_id=t2.id')->select();
        dump($result);
    }
    
    //关联自己 join
    public function test32(){
        //实例化模型
        $model=M('Dept');
        //sql：select t1.*,t2.name as deptname from tp_dept as t1 left join tp_dept as t2 on t1.pid = t2.id;
        $result = $model->field('t1.*,t2.name as deptname')
                                        ->alias('t1')
                                        ->join('left join tp_dept as t2 on t1.pid=t2.id')
                                        ->select();
        dump($result);                              
    }
    
    //charts
    public function charts(){
        $model=M();
        //select t2.name as deptname,count(*) as count from sp_user as t1,sp_dept as t2 where t1.dept_id = t2.id group by deptname;
        $data =$model ->field('t2.name as deptname,count(*) as count')
                                        ->table('tp_user as t1,tp_dept as t2')
                                        ->where('t1.dept_id = t2.id')
                                        ->group('deptname')
                                        ->select();
        
        
        //如果PHP版本是5.6之后的版本 可以做直接将data二位数数组assign   5.6以下 使用字符串拼接
        
        
        $str='[';
        //循环遍历字符串
        foreach($data  as $key=>$value){
            $str .="['". $value['deptname']."',". $value['count'] . "],";
        }
        
        //去除最后的逗号 
        $str=rtrim($str,',');
        //最后加上]
        $str .=']';
        //变量分配
        $this->assign('data',$str);
        //展示模板
        $this->display();   
    }
    //ip地址信息
    public function test33(){
        $ip = get_client_ip();
        echo $ip;
    }
    //ip的物理地址
    public function test34(){
        //实例化对象
        $ip =new \Org\Net\IpLocation('qqwry.dat');// 实例化类 参数表示IP地址库文件
        //$ss=I('get.ip');
        //查询
        $result = $ip -> getlocation('192.168.217.1'); // 获取某个IP地址所在的位置
        dump($result);
    } 
    
    //使用phpmail发送邮件
    public function testmail(){
        $subject='hello';
        $body='worllaslasaaaasl';
        $result = think_send_mail('448435279@qq.com','naihai',$subject,$body);
        if($result){
            $this->success('发送成功');
        }else{
            $this->error('发送失败');
        }
    }
    //datetime
    public function test36(){
        $data=date('Y-m-d H:i:s',time());
        echo $data;
        
        //随机令牌生成
            $passoa = new OAuthProvider(); 
            $password = $passoa->generateToken(5);
            
            echo $password;
    }
    
    
    public function test37(){
        $length = 2;//6号数字验证码
        $verify_code = generate_numcode();
        dump($verify_code);
    }
    
    public function test38(){
         
        $verify_code =  "https://" . $_SERVER['HTTP_HOST'] . "/index.php/Admin/Public/activation";
        dump($verify_code);
    }
    
    public function test999(){
         
        $a = md5(mt_rand(rand(1,9),rand(88,999)));
        dump($a);
    }
    public function test39(){
         datatables();
         
    }
    
    public function test40(){
         
        dump(json_encode('程序设计'));
    }
    
    public function test41(){
         
        $this->display('datepicker');
    }
    
    public function test42(){
         $data ='09/09/2017';
         
         $da=strtotime($data);
         dump($da);
         //dump($data);
    }
    
    public  function formatDate($date){
         if (strpos($date,'/') !== false){
             $date = str_replace('/', '-', $date);
             $date = date('Y-m-d', strtotime($date));
         }else{
            $date = date('d-m-Y', strtotime($date));
            $date = str_replace('-', '/', $date);
         }
    return $date;
 }
 
     public function test43(){
        if(IS_POST){
            $post=I('post.');//接收数据
             $model=D('Test');
            //保存数据
            $result = $model->addData($post,$_FILES['file']);
            //判断结果
            if($result){
                //成功
                $this->success('成功','',3);
            }else{
                //失败
                $this->error('失败');
            }
        }else{
            $this->display('imagecrop');
        }
           
        }
        
        public function test44(){
            $data = array(
                    array(NULL, 2010, 2011, 2012),
                    array('Q1',   12,   15,   21),
                    array('Q2',   56,   73,   86),
                    array('Q3',   52,   61,   69),
                    array('Q4',   30,   32,    0),
                   );
                 
        create_xls($data,'a');
        }
        
        public function test45(){
            dump(strtotime('2017-09-10')) ;
            
        }
        
        public function test46(){
            $str = "&lt;p&gt;aasasaass((sassassaassas))asssaasasas&lt;/p&gt;";
            $b = preg_replace("/\(\(.+\)\)+/",'((answer))',  $str);
            echo $b;
        }
        
        
        
        public function test47(){
            
            
            $a = array('id'=>'12');
            $b = json_encode($a);
            dump($b);
        }
        
        
        
        public function login_oj(){
            $post=array(
                'user_id'=>'admin',
                'password'=>'123456',
                );
            $cookie =  WORKING_PATH . UPLOAD_ROOT_PATH .'cookie.txt';
            $url ="http://192.168.217.8/html/JudgeOnline/login.php";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);//模拟发送post请求
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie); //设置Cookie信息保存在指定的文件中
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            
          curl_exec($ch);
            
            curl_close($ch);
        }
        
        
     
        public function add_problem() { 
            
            $cookie =  WORKING_PATH . UPLOAD_ROOT_PATH .'cookie.txt';
            $post=array(
                'title' =>'aaa',
                'time_limit'=>'5',
                'memory_limit'=>'128',
                'description'=>'666666666666666666666',
                'input'=>'12',
                'output'=>'1',
                'sample_input'=>'1',
                'sample_output'=>'12',
                'test_input'=>'1',
                'test_output'=>'2',
                'hint'=>'2',
                'source'=>'assa',
                'spj'=>'a',
            );  
 
            
            $url = "http://192.168.217.8/html/JudgeOnline/admin/problem_add.php";
            
            $ch = curl_init();
            /*$header =array(
                'HTTP_ACCEPT_LANGUAGE'=>'zh-CN',
                ); */
                curl_setopt($ch, CURLOPT_URL,$url);
                //curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie); //读取cookie 
                curl_setopt($ch, CURLOPT_POST, 1);//模拟发送post请求
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
 
            
            $res =  curl_exec($ch); //执行cURL抓取页面内容 
            curl_close($ch); 
            
            dump($res);
            
        } 
        
        
        public function test48(){
            
         
            $url  = 'http://ip.taobao.com/service/getIpInfo.php?ip=222.20.46.88';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          $res =  curl_exec($ch); //执行cURL抓取页面内容 
          curl_close($ch); 
            dump($res) ;
        }


        public function test49(){

            $link = "http://" . $_SERVER['HTTP_HOST'] . "/index.php/Home/Teacher/activation?accessToken=asassaas";
            
            $send_to = '448435279@qq.com';
            $from_name = sitesname();
            $subject = "帐户激活邮件";
            $body     = "恭喜您在我站".sitesname()."注册成功! 点击下面的链接立即激活帐户(或将网址复制到浏览器中打开)\r\n <a href='".$link."' target='_blank'>".$link."</a>";
            
            $result = think_send_mail($send_to, $from_name,$subject, $body);
            dump($result);die;
        }

        public function test50(){
            $aaa =sitesname();
             dump($aaa);die;
        }
 
    
}


//如何访问

//      http://192.168.217.8/work/tp3.2/index.php?m=Home&c=User&a=test




