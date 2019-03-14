<?php
namespace app\books\model;

use think\Model;
use think\Db;
use think\Request;

class Cover extends Model{

    /*根据书id查询书籍信息*/
    public function getBook($books_id){
        $result = Db::table('books_cou')->where('books_id',"$books_id")->find();
        return $result;
    }

    /*根据书id查询书籍最新章节*/
    public function getNewchapter($books_id){
        $result = Db::table('books_chapter')->where('books_id',"$books_id")->find();
        return $result;
    }

    /*根据类型id查询书籍类型名*/
    public function getTypeName($type_id){
        $result = Db::table('books_type')->where('type_id',"$type_id")->find();
        return $result['type_name'];
    }


}