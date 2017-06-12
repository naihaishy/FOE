<?php

namespace Course\Controller;
use Think\Controller;


class OjController extends Controller{
    
    

    public function index(){
        $this->display();
        
    }
    
    /**  
    *  处理oj题目
    * 
    * @access public
    * @param int   qid 问题id
    * @return  int sid  
    */
    public function ojSubmitAns($qid, $source){
        
        $pid = $this->getOjPid($qid);
        if(empty($pid)) exit;
        
        $source = htmlspecialchars_decode($source);
        //solution
        $data = array(
            'problem_id' => $pid,
            'user_id'       => session('uid'),
            'in_date'       => date('Y-m-d H:i:s', time()),
            'ip'                => get_client_ip(),
        );
        $map=array('problem_id'=>$pid, 'user_id'=>session('uid'), );
        $has_exists = D('OjSolution')->where($map)->find();
        if($has_exists){
            //更新记录
            $sid = $has_exists['solution_id'];
            D('OjSourceCode')->where('solution_id='.$sid)->setField('source', $source);
            D('OjSolution')->where($map)->setField('result',0);
            
        }else{
            //添加记录
            $sid = D('OjSolution')->add($data);
            $arr = array('solution_id'=>$sid, 'source'=>$source );
            D('OjSourceCode')->add($arr);
        }
        return $sid;
    }
    
    /**  
    * ajax 处理oj
    * 
    * @access public
    * @param paper_id 
    * @return  
    */
    public function ojAjaxTest(){
        if(IS_POST){
            $post = I('post.');
            $pid = $post['pid'];
            $source = htmlspecialchars_decode($post['source']);
            
            //solution
            $data['problem_id'] = $pid;
            $data['user_id'] = session('uid');
            $data['in_date'] = date('Y-m-d H:i:s', time() );
            $map=array('problem_id'=>$pid, 'user_id'=>session('uid'), );
            $has_exists = D('OjSolution')->where($map)->find();
            if($has_exists){
                //更新记录
                
                $sid = $has_exists['solution_id'];
                D('OjSourceCode')->where('solution_id='.$sid)->setField('source', $source);
                D('OjSolution')->where($map)->setField('result',0);

            }else{
                $sid = D('OjSolution')->add($data);
                $arr = array('solution_id'=>$sid, 'source'=>$source );
                D('OjSourceCode')->add($arr);
            }
            
            //sleep(2);
            //轮询
            $tmp_result =  M('OjSolution')->where($map)->getField('result');

            while ( $tmp_result < 4) {
                $tmp_result =  M('OjSolution')->where($map)->getField('result');
                sleep(1);
            }

            $result = D('OjSolution')->find($sid);

            if($result['result'] == 11){
                //Compile Error 
                $error = D('OjCompileinfo')->find($sid);

            }elseif($result['result'] == 4){
                $error = '';
            }else{
                $error = D('OjRuntimeinfo')->find($sid);
            }
            
            $error['error'] = htmlspecialchars_decode( str_replace("\n\r","\n",$error['error']) );
            
            $return = array(
                'result'    =>  $this->compileinfo($result['result']),
                'error'     =>  $error['error'] ,
                'time'      =>  $result['time'] ,
                'memory'    =>  $result['memory'],
            );
            
            $this->ajaxReturn($return);
            
        }
    }

    public function getProblem(){
        if(IS_POST){
            $pid = I('post.pid');
            $problem = M('OjProblem')->find($pid);
            $this->ajaxReturn($problem);
        }
    }
    

    
    /**  
    * oj 计算结果 正确或者错误
    * @access public
    * @param  int sid
    * @return boolen
    */
    public function checkResult($sid){
        $arr = D('OjSolution')->field('result')->find($sid);
        if($arr['result']==4){
            return true;
        }else{
            return false;
        }
    }
    
    
    /**  
    * oj 编译结果
    * 来自 judged_client.cc 宏定义
    * @access private
    * @param  int result 编译结果   0--13
    * @return   中文说明
    */
    private function compileinfo($result){
        switch($result){
            case 0:
                return '';
            case 1:
                return '';
            case 2:
                return 'Compiling ';
            case 3:
                return 'Running';
            case 4:
                return '通过';
            case 5:
                return '格式错误(你的程序运行的结果是正确的，但是格式和正确结果有点不一样)';
            case 6:
                return '答案错误';
            case 7:
                return '时间超出题目限制';
            case 8:
                return '内存超出题目限制';
            case 9:
                return '输出内存过多(可能存在无限循环)';
            case 10:
                return '运行时错误';
            case 11:
                return '编译错误';
            case 12:
                return '';
            case 13:
                return '';
        }
    }
    

    /**  
    * 删除OJ题目 
    * @access public
    * @param  int  
    * @return    
    */
    public function delOjProblem($pid){

        if(empty($pid) || !is_numeric($pid) || !M('OjProblem')->find($pid)) $this->error('不存在该题目');
        if(!is_teacher() && !is_admin()) $this->error('对不起你没有权限');
        
        D('OjProblem')->where(array('problem_id'=>$pid))->delete();//删除OJ题目记录
        $this->delSampleData($pid);//删除测试数据

        $solutions = M('OjSolution')->where(array('problem_id'=>$pid))->select(); //所有提交
        foreach ($solutions as $key => $value) {
            M('OjSourceCode')->where(array('solution_id'=>$value['solution_id']))->delete();//删除提交的oj 源代码
            M('OjCompileinfo')->where(array('solution_id'=>$value['solution_id']))->delete();//删除编译错误信息
            M('OjRuntimeinfo')->where(array('solution_id'=>$value['solution_id']))->delete();//删除运行错误信息
        }


    }
    
    
    
    
    /**  
    * 生成测试数据 
    * @access public
    * @param  
    * @return    
    */
    public function createSampleData($pid, $sample_in, $sample_out){
        $basedir = "/home/judge/data/$pid";
        if(!opendir($basedir) ){
            mkdir ( $basedir );
        }
        if(strlen($sample_out ) && !strlen($sample_in) ) $sample_in ="0";
        if(strlen($sample_in) )  mkdata($pid,"sample.in",   $sample_in );
        if(strlen($sample_out) ) mkdata($pid,"sample.out",  $sample_out  );
        closedir($basedir);
    }
    
    
    /**  
    * 更新测试数据 
    * @access public
    * @param  
    * @return    
    */
    public function updateSampleData($pid, $sample_in, $sample_out){
        $basedir = "/home/judge/data/$pid";
        if($sample_input && file_exists($basedir."/sample.in")){
            $fp=fopen($basedir."/sample.in","w");
            fputs($fp,preg_replace("(\r\n)","\n",$sample_input));
            fclose($fp);
            
            $fp=fopen($basedir."/sample.out","w");
            fputs($fp,preg_replace("(\r\n)","\n",$sample_output));
            fclose($fp);
        }else{
            $this->createSampleData($pid, $sample_in, $sample_out);
        }
    }
    
    /**  
    * 删除测试数据 
    * @access public
    * @param  
    * @return    
    */
    public function delSampleData($pid){

        $basedir = "/home/judge/data/$pid";
        unlink( $basedir."/sample.in" );
        unlink( $basedir."/sample.out" );
        rmdir( $basedir );
    }
    
    
    /**  
    * 获取oj pid
    * @access public
    * @param  int qid 题目id
    * @return  pid
    */
    public function getOjPid($qid){
        $question = D('CourseQuestion')->field('metas')->find($qid);
        $arr = json_decode($question['metas'], true);
        if($arr['oj'] == true){
            return $arr['ojpid'];
        }else{
            return '';
        }
          
    }
    
 
    public function getOjResult($sid){
        
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
 
 
}