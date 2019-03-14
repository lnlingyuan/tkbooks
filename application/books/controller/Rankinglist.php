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


class Rankinglist extends Base
{
    public function index(){

       $list = Db::table('books_type')->select();

       foreach ($list as $key=>$val){
           $name = $val['type_name'].'榜';
           $res = Db::table('books_cou')->where('books_type', $val['type_id'])->order('books_time asc')->limit('9')->select();
           $data[] = array('id'=>$val['type_id'],'name'=>$name,'res'=>$res);

           if($key=='5'){
               break;
           }
       }

        //所有小说类型
        $types= array();
        foreach ($list as $value){
            $types[$value['type_id']] = $value['type_name'];
        }


        $this->view->data = $data;
        $this->view->types = $types;

        $mobile = session('mobile');
        if($mobile){
            return $this->fetch('mobile/rankinglist');
        }else{
            return $this->fetch('template/rankinglist');
        }
    }
}