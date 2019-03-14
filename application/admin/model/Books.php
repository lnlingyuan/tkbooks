<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2018/8/14
 * Time: 23:40
 */
namespace app\admin\model;

use think\Model;
use think\Db;

Class Books extends Model{

    /**
     * @return \think\Paginator
     * 查出会员列表
     */
    public function getList($res=array()){

        if(!empty($res['books_name'])){
            $map['books_name'] = ['like',"%{$res['books_name']}%"];
        }
        if(!empty($res['books_author'])){
            $map['books_author'] = ['like',"%{$res['books_author']}%"];
        }
        if(!empty($res['books_url'])){
            $map['books_url'] = ['like',"%{$res['books_url']}%"];
        }
        if(!empty($res['books_type'])){
            $map['books_type'] = $res['books_type'];
        }

        if(!empty($map)){
            $result = Db::table('books_cou')->where($map)->paginate(10,false,['query' => request()->param()]);
        }else{
            $result = Db::table('books_cou')->paginate('10');
        }

        return $result;
    }

    /**
     * @return int|string
     * 统计书籍数量
     */
    public function  getCount($res=array()){

        if(!empty($res['books_name'])){
            $map['books_name'] = ['like',"%{$res['books_name']}%"];
        }
        if(!empty($res['books_author'])){
            $map['books_author'] = ['like',"%{$res['books_author']}%"];
        }
        if(!empty($res['books_type'])){
            $map['books_type'] = $res['books_type'];
        }
        if(!empty($res['books_url'])){
            $map['books_url'] = ['like',"%{$res['books_url']}%"];
        }
        if(!empty($map)){
            $result = Db::table('books_cou')->where($map)->count();
        }else{
            $result = Db::table('books_cou')->count();
        }

        return $result;
    }



}