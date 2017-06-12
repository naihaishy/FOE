<?php

namespace Common\Controller;
use Think\Controller;

class PagesController extends Controller{


    /**  
     * 分页
     * @access public
     * @param  
     * @return
     */
    public function getShowPage($model, $where='', $perpage=6 ){

        $count = $model->where($where)->count();

        $page = new \Think\Page($count, $perpage); //每页显示的个数
        
        $page->rollPage   = 6; //分页个数
        $page->lastSuffix = false; 
        $page->setConfig('header',' 条记录');
        $page->setConfig('prev','上一页');
        $page->setConfig('next','下一页');
        $page->setConfig('first','首页');
        $page->setConfig('last','末页');
        $page->setConfig('theme','%TOTAL_ROW%  %HEADER%  %NOW_PAGE%/%TOTAL_PAGE% 页 %FIRST%  %UP_PAGE%   %LINK_PAGE%  %DOWN_PAGE%  %END%');
        //$show = $page->show();
        return $page;
    }

}