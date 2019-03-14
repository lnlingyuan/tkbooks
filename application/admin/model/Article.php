<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2018/7/31
 * Time: 15:08
 */
namespace app\admin\model;

use think\Model;
use think\Db;

Class Article extends Model{

    /**
     * @return \think\Paginator
     * 查出seo列表
     */
    public function getList($res=array()){

        if(!empty($res['article_name'])){
            $map['article_name'] = ['like',"%{$res['article_name']}%"];
        }


        if(!empty($map)){
            $result = Db::table('books_article')->where($map)->paginate(10,false,['query' => request()->param()]);
        }else{
            $result = Db::table('books_article')->paginate('10');
        }

       return $result;
    }

    /**
     * @param array $res
     * @return int|string
     * 数据数量
     */
    public function getNum($res=array()){
        if(!empty($res['article_name'])){
            $map['article_name'] = ['like',"%{$res['article_name']}%"];
        }


        if(!empty($map)){
            $result = Db::table('books_article')->where($map)->count();
        }else{
            $result = Db::table('books_article')->count();
        }

        return $result;
    }






}