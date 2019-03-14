<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2018/5/30
 * Time: 17:43
 */

namespace app\books\controller;


use think\Controller;
use think\Model;
use think\Request;
use think\Db;
use think\view;


class Shelf extends Base
{

    public function index(){
        $user_id = session('user_id');

        if(empty($user_id)){
           return alert_error('请先登陆');
        }
        $data = Db::table('books_shelf')->alias('s')->field('s.user_id,s.books_id,c.books_name,c.books_author,c.books_type,c.books_img,h.history_name,h.history_url')->join('books_cou c','c.books_id=s.books_id','INNER')->join('books_history h','h.books_id=s.books_id','LEFT')->where('s.user_id',$user_id)->where('h.user_id',$user_id)->paginate(9);

        //所有小说类型
        $type = Db::table('books_type')->select();
        $types= array();
        foreach ($type as $value){
            $types[$value['type_id']] = $value['type_name'];
        }

        $one = $data->isEmpty();
        $this->view->data = $data;
        $this->view->has = $data->isEmpty();
        $this->view->types = $types;
        $mobile = session('mobile');
        if($mobile){
            return $this->fetch('mobile/shelf');
        }else{
            return $this->fetch('template/shelf');
        }

    }



    //删除书架书籍
    public function deleteBooks(){
        $books_id = input("param.books_id");
        $user_id =  session('user_id');
        if(!empty($books_id) && !empty($user_id)){
            DB::table('books_shelf')->where('books_id',$books_id)->where('user_id',$user_id)->delete();
            return $this->success('删除成功！','shelf/index');
        }else{
            return $this->error('删除失败！');
        }


    }


    public function nextBooks(){

        $user_id = session('user_id');

        if(empty($user_id)){
            return alert_error('请先登陆');
        }

        $data = Db::table('books_shelf')->alias('s')->field('s.user_id,s.books_id,c.books_name,c.books_author,c.books_type,c.books_img,h.history_name,h.history_url')->join('books_cou c','c.books_id=s.books_id','INNER')->join('books_history h','h.books_id=s.books_id','LEFT')->where('s.user_id',$user_id)->paginate(9);


        return json($data,200);

    }

    /**
     * 手机版书架
     * 如果已经登陆，则跳到书架，否则跳转到登陆页面
     */
    public function mobileShelf(){

        $user_id = session('user_id');

        if(empty($user_id)){
           return ajaxJson('100','请先登陆','/login/index');
        }else{
            return  ajaxJson('200','已登陆','/shelf/index');
        }


    }


    /**
     * 手机版最近阅读
     */
    public function mobileHostory(){

        $user_id = session('user_id');

        if(empty($user_id)){
            return alert_error('请先登陆');
        }

        $data = Db::table('books_history')->alias('h')->field('h.user_id,h.books_id,h.history_url,c.books_name,c.books_author,c.books_type,c.books_img,h.history_name,h.history_url')->join('books_cou c','c.books_id=h.books_id','INNER')->where('h.user_id',$user_id)->order('h.history_time desc')->select();

        foreach ($data as &$val){
            $val['history_url'] = base64_encode($val['history_url']); //加密
        }


        //所有小说类型
        $type = Db::table('books_type')->select();
        $types= array();
        foreach ($type as $value){
            $types[$value['type_id']] = $value['type_name'];
        }

        $this->view->data = $data;
        $this->view->types = $types;

        return $this->fetch('mobile/hostory');
    }


}