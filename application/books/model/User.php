<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2018/6/1
 * Time: 18:32
 */

namespace app\books\model;


use think\Model;
use think\Db;
use think\Request;

class User extends Model
{
    protected $id = 'user_id';
    protected $table = 'books_user';

    public function info($user_id){
        $data = Db::table('books_user')->where('user_id',$user_id)->find();
        return $data;
    }

    public function history($user_id){
        $data = Db::table('books_history')->alias('h')->field('c.books_id,c.books_name,c.books_img')->join('books_cou c','c.books_id=h.books_id')->where('h.user_id',$user_id)->order('history_time desc')->limit(4)->select();

        return $data;
    }

}