<?php
/**
 * Created by PhpStorm.
 * User: end
 * Date: 2018/5/19
 * Time: 15:36
 */

namespace app\books\model;


use think\Model;
use think\Db;

class Search extends Model
{
    public function getNameBooks($book_name){

       $result = Db::table('books_cou')->where('books_name','like',"%{$book_name}%")->paginate(9);

       return $result;


    }

}