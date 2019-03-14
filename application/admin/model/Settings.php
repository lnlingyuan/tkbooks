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

Class Settings extends Model{

    /**
     * @return \think\Paginator
     * 查出seo列表
     */
    public function getList($res=array()){

        if(!empty($res['seo_remark'])){
            $map['seo_remark'] = ['like',"%{$res['seo_remark']}%"];
        }


        if(!empty($map)){
            $result = Db::table('books_seo')->where($map)->paginate(10,false,['query' => request()->param()]);
        }else{
            $result = Db::table('books_seo')->paginate('10');
        }

       return $result;
    }

    /**
     * @param array $res
     * @return int|string
     * 数据数量
     */
    public function getNum($res=array()){
        if(!empty($res['seo_remark'])){
            $map['seo_remark'] = ['like',"%{$res['seo_remark']}%"];
        }


        if(!empty($map)){
            $result = Db::table('books_seo')->where($map)->count();
        }else{
            $result = Db::table('books_seo')->count();
        }

        return $result;
    }






}