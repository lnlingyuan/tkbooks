<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2018/6/7
 * Time: 11:31
 */

namespace app\books\controller;
use think\Controller;
use think\Request;
use think\Db;
use think\view;


class Bibliotheca extends Base
{
    public function index(){

        $type_id = input("param.type_id");
        $books_number = input("param.books_number");
        $books_time = input("param.books_time");
        $books_status = input("param.books_status");
        $txt = input("param.txt");



        $bla = model('Bibliotheca');
        $data = $bla->getAll($type_id,$books_number,$books_time,$books_status);

        $condition_type = !empty($type_id) ? '/type_id/'.$type_id : '';
        $condition_time = !empty($books_time) ? '/books_time/'.$books_time : '';
        $condition_status = $books_status>= -1 ? '/books_status/'.$books_status : '';
        $condition_txt =  !empty($txt) ? '/txt/'.$txt : '';


        //查出所有书籍分类
        $parameter = $condition_status.$condition_time.$condition_txt;
        $type = Db::table('books_type')->select();
        $types= array();
        foreach ($type as $key=>$value){
            $types[$key]['type_id'] = $value['type_id'];
            $types[$key]['name'] = $value['type_name'];
            $types[$key]['href'] = '/bibliotheca/index/type_id/'.$value['type_id'].$parameter;
            $type[$value['type_id']] = $value['type_name'];
        }
        //全部分类链接
        $all_type_href='/bibliotheca/index'.$parameter;

        //书籍状态
        $parameter = $condition_type.$condition_time.$condition_txt;
        $status_arr = array(
            array('id'=>'-1','name'=>'全部','href'=>'/bibliotheca/index/books_status/-1'.$parameter),
            array('id'=>'0','name'=>'连载','href'=>'/bibliotheca/index/books_status/0'.$parameter),
            array('id'=>'1','name'=>'完结','href'=>'/bibliotheca/index/books_status/1'.$parameter),
        );

        //更新时间
        $parameter = $condition_type.$condition_status.$condition_txt;
        $time_arr = array(
            array('id'=>'0','name'=>'全部','href'=>'/bibliotheca/index/books_time/0'.$parameter),
            array('id'=>'3','name'=>'三日内','href'=>'/bibliotheca/index/books_time/3'.$parameter),
            array('id'=>'7','name'=>'七日内','href'=>'/bibliotheca/index/books_time/7'.$parameter),
            array('id'=>'15','name'=>'半月内','href'=>'/bibliotheca/index/books_time/15'.$parameter),
            array('id'=>'30','name'=>'一月内','href'=>'/bibliotheca/index/books_time/30'.$parameter),
        );

        //显示方式
        $parameter = $condition_type.$condition_status.$condition_time;
        $txt_arr = array(
            array('id'=>'0','href'=>'/bibliotheca/index/txt/0'.$parameter),
            array('id'=>'1','href'=>'/bibliotheca/index/txt/1'.$parameter),
        );


        $this->view->data = $data;
        $this->view->txt = $txt;
        $this->view->txt_arr = $txt_arr;
        $this->view->status_arr = $status_arr;
        $this->view->time_arr = $time_arr;
        $this->view->all_type_href = $all_type_href;
        $this->view->types = $types;
        $this->view->type = $type;
        $this->view->type_id =$type_id;
        $this->view->books_status = $books_status!=null ? $books_status : -1;
        $this->view->books_time = $books_time!=null ? $books_time : 0;


        $mobile = session('mobile');
        if($mobile){
            return $this->fetch('mobile/bibliotheca_type');
        }else{
            return $this->fetch('template/bibliotheca');
        }
    }


    public function mobileIndex(){
        $data = Db::table('books_type')->select();

        foreach ($data as &$val){
            $val['num'] = Db::table('books_cou')->where('books_type',$val['type_id'])->count();
            $img =  Db::table('books_cou')->field('books_img')->where('books_type',$val['type_id'])->order('books_id desc')->find();
            $val['img'] = $img['books_img'];
        }

        $this->view->data = $data;
        return $this->fetch('mobile/bibliotheca');
    }


}