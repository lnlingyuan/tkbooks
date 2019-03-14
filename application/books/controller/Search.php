<?php
/**
 * Created by PhpStorm.
 * User: end
 * Date: 2018/5/19
 * Time: 15:32
 */

namespace app\books\controller;

use think\Controller;
use think\Db;
use think\Model;


class Search extends Base
{


    public function index(){

        $books_name =input("param.books_name");

        //过滤
        $books_name = filter($books_name);

        $search_arr = cookie('search_cookie');
        $search_arr[]= $books_name;
        $search_arr= array_unique($search_arr);
        cookie('search_cookie',$search_arr);


        //调用公共文件中的过滤函数
        $books_name = filter($books_name);

        $search = model('Search');
        $data = $search->getNameBooks($books_name);


        //所有小说类型
        $type = Db::table('books_type')->select();
        $types= array();
        foreach ($type as $value){
            $types[$value['type_id']] = $value['type_name'];
        }



        $this->view->data = $data;
        $this->view->types = $types;
        $this->view->search_name= $books_name;

        $mobile = session('mobile');
        if($mobile){
            return $this->fetch('mobile/search');
        }else{
            return $this->fetch('template/search');
        }
    }

    public function nextResult(){
        $books_name =input("param.books_name");

        //调用公共文件中的过滤函数
        $books_name = filter($books_name);

        $search = model('Search');
        $data = $search->getNameBooks($books_name);


        //所有小说类型
        $type = Db::table('books_type')->select();
        $types= array();
        foreach ($type as $value){
            $types[$value['type_id']] = $value['type_name'];
        }

        foreach ( $data as &$val){
            $val['books_type'] =  $types[$val['books_type']];
            if ($val['books_status']=='1'){
                $val['books_status'] = '完结';
            }else{
                $val['books_status'] = '连载';
            }
            $val['books_num'] =  $parama=mt_rand(100,999);
            $res[] = $val;
        }

        return ajaxJson('200','成功',$res);
    }


    /**
     * 清空搜索历史
     */
    public function clearSearch(){
        // 删除
        cookie('search_cookie', null);
    }



}