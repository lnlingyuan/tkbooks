<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2018/6/8
 * Time: 18:19
 */

namespace app\books\model;


use think\Model;
use think\Db;

class Bibliotheca extends Model
{
    public function getAll($type_id=0,$books_number=0,$books_time=0,$books_status=-1){
        $where = array();
        if(!empty($type_id)){
            $where[]='books_type='.$type_id;
        }

        if($books_status>=0 && $books_status != null){
            $where[]='books_status='.$books_status;
        }


        if(!empty($books_time)){
            $where[] = "DATEDIFF(books_time,NOW())<=0 AND DATEDIFF(books_time,NOW())>-".$books_time;
        }

        $condition = implode(' and ',$where);

//        echo Db::table('books_cou')->field('a.books_id,books_name,books_time,books_type,books_img,books_author,chapter_name')->fetchSql()->alias('a')->join('books_chapter c','a.books_id=c.books_id')->where($condition)->limit(18)->select();


        if(!empty($condition)){
            $data =Db::table('books_cou')->field('a.books_id,books_name,books_time,books_status,books_type,books_img,books_author,chapter_name,books_synopsis')->alias('a')->join('books_chapter c','a.books_id=c.books_id')->where($condition)->order('books_id asc')->paginate(18);

        }else{
            $data = Db::table('books_cou')->field('a.books_id,books_name,books_time,books_status,books_type,books_img,books_author,chapter_name,books_synopsis')->alias('a')->join('books_chapter c','a.books_id=c.books_id')->order('books_id asc')->paginate(18);
        }

        return $data;
    }
}