<?php
namespace Admin\Controller;
use Think\Controller;
class NewsController extends CommonController{

    /**  
     * 新闻列表
     * @access public
     * @param  
     * @return
     */
    public function index(){

        $model = M('News');
        $page = A('Common/Pages')->getShowPage($model);
        $show = $page->show();
        $news   = M('News')->alias('t1')->field('t1.*,t2.username as author_name,t3.name as category_name')
                                        ->join('left join tp_user as t2 on t1.author=t2.id')
                                        ->join('left join tp_news_category as t3 on t1.category_id =t3.id')
                                        ->limit($page->firstRow,$page->listRows)
                                        ->select();
        $assign =   array(
                'news'  =>  $news,
                'show'  =>  $show,
            );  

        $this->assign($assign);
        $this->display();
    }


    /**  
     * 添加新闻
     * @access public
     * @param  
     * @return
     */
    public function add(){
        if(IS_POST){

            //自动验证提交数据
            $rules = array(
                 array('title','require','标题必须有！'),
                 array('category_id','require','所属分类必须确定！'),
                 array('content','require','内容不得为空!'),
            );
            $model = M('News');
            if(!$model->validate($rules)->create())  $this->error($model->getError(),'',1);


            $post = I('post.');
            $post['author'] = session('uid');//管理员
            $post['post_time'] = time();
            $post['updated_time'] = time();
            $post['content'] = htmlspecialchars_decode($post['content']);
            $result = M('News')->add($post);
            $result ? $this->success('添加成功'): $this->error('添加失败');

        }else{
            $category = M('NewsCategory')->select();
            $this->assign('category', $category);
            $this->display();
        }
    }


    /**  
     * 修改新闻
     * @access public
     * @param  
     * @return
     */
    public function edit($id){
        if(empty($id) || !is_numeric($id) || !M('News')->find($id)) $this->error('不存在此新闻'); 
        if(IS_POST){
            //自动验证提交数据
            $rules = array(
                 array('title','require','标题必须有！'),
                 array('category_id','require','所属分类必须确定！'),
                 array('content','require','内容不得为空!'),
            );
            $model = M('News');
            if(!$model->validate($rules)->create())  $this->error($model->getError(),'',1);

            $post = I('post.');
            $post['updated_time'] = time();
            $post['content'] = htmlspecialchars_decode($post['content']);

            $result = M('News')->where('id='.$id)->save($post);
            $result ===false ?  $this->error('保存失败'):$this->success('保存成功');
            
        }else{
            $news = M('News')->find($id);
            $category = M('NewsCategory')->select();
            $this->assign('news', $news);
            $this->assign('category', $category);
            $this->display();
        }
    }



    /**  
     * 删除新闻
     * @access public
     * @param  
     * @return
     */
    public function delete($id){
        if(empty($id) || !is_numeric($id) || !M('News')->find($id)) $this->error('不存在此新闻'); 
        $result = M('News')->where('id='.$id)->delete();
        $result ===false ?  $this->error('删除失败'):$this->success('删除成功');
    }
    
    
    
        
    /**  
     * 查看新闻
     * @access public
     * @param  
     * @return
     */
    
    public function view($id, $map=''){
        if(empty($id) || !is_numeric($id) || !M('News')->find($id)) $this->error('不存在此新闻');

        if(empty($map))   $news = M('News')->find($id);
        else    $news = M('News')->where($map)->find($id);
        $news['tags'] = explode(',', $news['tags']);
        //dump($manual);die;
        $this->assign('news', $news);
        $this->display();
        
        
    }




    /**  
     * 新闻分类列表
     * @access public
     * @param  
     * @return
     */
    public function category(){
        $this->checkCategoryCount();
        $category   =   M('NewsCategory')->select();
        $this->assign('category', $category);
        $this->display();
    }

    /**  
     * 添加新闻分类
     * @access public
     * @param  
     * @return
     */
    public function addCategory(){
        if(IS_POST){
            $post = I('post.');
            //自动验证提交数据
            $rules = array(
                 array('name','require','标题必须有！'),
            );
            $model = M('NewsCategory');
            if(!$model->validate($rules)->create())  $this->error($model->getError(),'',1);

            $result =   M('NewsCategory')->add($post);
            $result ? $this->success('添加成功','',1) : $this->error('添加失败','',2);
        }
    }
    /**  
     * 编辑新闻分类
     * @access public
     * @param  
     * @return
     */
    public function editCategory(){
        
        if(IS_POST){
            $post = I('post.');
            //自动验证提交数据
            $rules = array(
                 array('name','require','标题必须有！'),
            );
            $model = M('NewsCategory');
            if(!$model->validate($rules)->create())  $this->error($model->getError(),'',1);
            $result =   M('NewsCategory')->save($post);
            $result ? $this->success('更新成功','',1) : $this->error('更新失败','',2);
        }
        
    }
    

    /**  
     * 删除新闻分类
     * @access public
     * @param  
     * @return
     */
    public function delCategory($id){
        $result =   M('NewsCategory')->delete($id);
        $result ? $this->success('删除成功','',1) : $this->error('删除失败','',2);
    }


    /**  
     * 检查新闻分类总数
     * @access private
     * @param  
     * @return
     */
    private function checkCategoryCount(){
        $count  =   M('News')->alias('t1')
                            ->field('t2.id, count(*) as count ')
                            ->join('left join tp_news_category as t2 on t1.category_id=t2.id')
                            ->group('t2.id asc')
                            ->select();
        //M('Manual')
        foreach($count as $val){
            M('NewsCategory')->where('id='.$val['id'])->setField('count', $val['count']);
        }
        //return $count;
    }


}