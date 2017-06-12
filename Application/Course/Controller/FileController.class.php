<?php
namespace Course\Controller;
use Think\Controller;
class FileController extends CommonController {
    
    //显示文件
    public function index(){
        A('Teacher/Navbar')->navbar();
        $map    =   array('user_id'=>session('uid'));

        $page = A('Common/Pages')->getShowPage(D('CourseFiles'), array('user_id'=>session('uid'), 'course_id'=>array('not in', '0') ) );
        $show = $page->show();
        $files = D('CourseFiles') ->alias('t1')
                                    ->field('t1.*,t2.title as course_title')
                                    ->join('tp_course as t2 on t1.course_id = t2.id')
                                    ->where($map)
                                    ->limit($page->firstRow,$page->listRows)
                                    ->select();
        unset($map);
        $map=array(
            'user_id'=>session('uid'),
            'course_id'=>0,
            );
        $unusefiles=D('CourseFiles')->where($map)->select();
        //dump($unusefiles);die;
        $this->assign('files',$files);
        $this->assign('unusefiles',$unusefiles);
        $this->assign('show',$show);
        $this->display();
    }
    
    //文件管理
    public function manager($type=''){
        
            $gtype=$type;
            switch($gtype){
                case 'video':
                 $type = 'video/avi,video/mp4';
                break; 
                case 'image':
                 $type = 'image/png,image/jpeg,image/gif,image/bmp';
                break; 
                case 'audio':
                 $type = 'audio/mpeg,audio/wav';
                break; 
                case 'docu':
                 $type = 'application/msword,application/vnd.ms-excel,application/vnd.ms-powerpoint,application/pdf,text/plain';
                break;
                case 'all':
                 $type = '';
                break;
                default:
                    $type = '';
                break;
            }
            
        if( empty($type) ){
            $map=array( 'user_id'=>session('uid'),);
        }else{
            $map=array( 
            'user_id'=>session('uid'),
            'type'=>array('in',$type),
            );
        }
        $files = D('CourseFiles')->where($map)->order('created_time desc')->select();
        foreach ($files as &$file){
            $file['icon']= $this->typeicon($file['type']);
        }
        //dump($files);die;
        $this->assign('files',$files);
        $this->display();
    }
    
    
    //upload ajax 进行封装 
    public function upload(){
            
            if( empty(I('get.cid')) ) {
                $course_id= 0 ;//尚未使用状态 0
            }else{
                $course_id = I('get.cid');
            }
            if(IS_POST){
                $post=I('post.');
                $post['course_id'] = $course_id;
                $post['user_id'] = session('uid');
                //dump($post);die;
                $result = D('CourseFiles')->saveData($post,$_FILES, $course_id);
                $return =array('fid'=>$result);
                $json_return =json_encode($return);
                $this->ajaxReturn($json_return);//返回新增文件的id
            }else{
                $this->display();
            }
        }
    
    
    
    //view 文件预览 
    public function view(){
        $id=I('get.id');
            $file = D('CourseFiles')->find($id);
            if(empty($file) ){
                $this->error('不存在该文件');
            }
            $type=$file['type'];
            //根据文件类型选择不同模块进行文件预览 目前仅支持  pdf  image video audio 
            switch($type){
                case 'application/pdf':
                    $this->viewPdf($file);
                break;
                case 'image/png':
                    $this->viewImgage($file);
                break;
                case 'image/jpeg':
                    $this->viewImgage($file);
                break;
                case 'text/plain':
                    $this->viewText($file);
                break;
                case 'video/mp4':
                    $this->viewVideo($file);
                break;
                case 'audio/mpeg':
                    $this->viewAudio($file);
                break;
                default:
                    $this->viewNo();
                break;
            }
            
    }
    
    
    //文件下载
        public function download(){
            $id=I('get.id');
            $data = M('CourseFiles')->find($id);
            //下载代码
            $file=WORKING_PATH . $data['uri'];
            header("Content-type: application/octet-stream");
            header('Content-Disposition: attachment; filename="' . $data['title'] . '"');
            header("Content-Length: ". filesize($file));
            readfile($file);
        }
        
        //文件删除
        public function delete($id){
            
                $file_id=$id;
                $map=array(
                    'id'=>$file_id,
                    'user_id'=>session('uid'),
                    );
                $file = D('CourseFiles')->where($map)->find();
                if(empty($file)){
                    $this->error('文件不存在');
                }
                $file_uri =  WORKING_PATH .$file['uri'];
                $bool = unlink($file_uri);//从upload删除文件 真 删除
                $result =   D('CourseFiles')->delete($file_id);
                if($result && $bool){
                    $this->success('删除成功');
                }else{
                    $this->error('删除失败');
                }
              
        }
        
        
        
        
        
        /*-----------文件预览处理类函数----------*/
        
 
        
        public function viewPdf($file){
            $this->assign('file',$file);
            $this->display('view-pdf');
        }
        
        public function viewImgage($file){
            
            $url=$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$file['uri'];
            echo "<script>window.open( ' ".$url." ')</script>";
            echo "<script>window.history.back();</script>";
        }
        
        public function viewVideo($file){
            $this->assign('file',$file);
            $this->display('view-video');
        }
        
        public function viewAudio($file){
            $this->assign('file',$file);
            $this->display('view-audio');
        }
        
        public function viewText($file){
            $content = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/'.$file['uri']);
            $content =  $this->uncode($content);
            $content=str_replace("\r\n","<br />",$content);
            $file['content'] = $content;
            $this->assign('file',$file);
            $this->display('view-text');
        }
        
        private function viewNo(){
            $this->error('对不起，暂不支持该类型文件预览,请下载至本地查看','',3);
        }
        
        
        private function uncode($text){ 
              define('UTF32_BIG_ENDIAN_BOM', chr(0x00) . chr(0x00) . chr(0xFE) . chr(0xFF));  
              define('UTF32_LITTLE_ENDIAN_BOM', chr(0xFF) . chr(0xFE) . chr(0x00) . chr(0x00));  
              define('UTF16_BIG_ENDIAN_BOM', chr(0xFE) . chr(0xFF));  
              define('UTF16_LITTLE_ENDIAN_BOM', chr(0xFF) . chr(0xFE));  
              define('UTF8_BOM', chr(0xEF) . chr(0xBB) . chr(0xBF));  
              $first2 = substr($text, 0, 2);  
              $first3 = substr($text, 0, 3);  
              $first4 = substr($text, 0, 3);  
              $encodType = "";  
              if ($first3 == UTF8_BOM)  
                  $encodType = 'UTF-8 BOM';  
              else if ($first4 == UTF32_BIG_ENDIAN_BOM)  
                  $encodType = 'UTF-32BE';  
              else if ($first4 == UTF32_LITTLE_ENDIAN_BOM)  
                  $encodType = 'UTF-32LE';  
              else if ($first2 == UTF16_BIG_ENDIAN_BOM)  
                  $encodType = 'UTF-16BE';  
              else if ($first2 == UTF16_LITTLE_ENDIAN_BOM)  
                  $encodType = 'UTF-16LE';  

              if ($encodType == '') {//即默认创建的txt文本-ANSI编码的  
                  $content = iconv("GBK", "UTF-8", $text);  
              } else if ($encodType == 'UTF-8 BOM') {//本来就是UTF-8不用转换  
                  $content = $text;  
              } else {//其他的格式都转化为UTF-8就可以了  
                  $content = iconv($encodType, "UTF-8", $text);  
              }
              return $content;
        }
        
        
        private function typeicon($type){
            switch($type){
                case 'video/mp4':
                    $icon='film';
                break;
                case 'video/avi':
                    $icon='film';
                break;
                case 'audio/mpeg':
                    $icon='music';
                break;
                case 'audio/wav':
                    $icon='music';
                break;
                case 'image/png':
                    $icon='image';
                break;
                case 'image/jpeg':
                    $icon='image';
                break;
                case 'image/bmp':
                    $icon='image';
                break;
                default:
                    $icon='file';
                break;
            }
            return $icon;
            
        }
 
    
}