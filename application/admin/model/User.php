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

Class User extends Model{

    /**
     * @return \think\Paginator
     * 查出会员列表
     */
    public function getList($res=array()){

        if(!empty($res['user_name'])){
            $map['user_name'] = ['like',"%{$res['user_name']}%"];
        }
        if(!empty($res['user_email'])){
            $map['user_email'] = ['like',"%{$res['user_email']}%"];
        }

        if(!empty($map)){
            $result = Db::table('books_user')->where($map)->paginate(10,false,['query' => request()->param()]);
        }else{
            $result = Db::table('books_user')->paginate('10');
        }

       return $result;
    }

    /**
     * @param array $res
     * @return int|string
     * 数据数量
     */
    public function getNum($res=array()){
        if(!empty($res['user_name'])){
            $map['user_name'] = ['like',"%{$res['user_name']}%"];
        }
        if(!empty($res['user_email'])){
            $map['user_email'] = ['like',"%{$res['user_email']}%"];
        }

        if(!empty($map)){
            $result = Db::table('books_user')->where($map)->count();
        }else{
            $result = Db::table('books_user')->count();
        }

        return $result;
    }



    /**
     * @return \think\Paginator
     * 查出管理员列表
     */
    public function getAdminList($res=array()){

        if(!empty($res['admin_name'])){
            $map['admin_name'] = ['like',"%{$res['admin_name']}%"];
        }

        if(!empty($map)){
            $result = Db::table('books_admin')->where($map)->paginate(10,false,['query' => request()->param()]);
        }else{
            $result = Db::table('books_admin')->paginate('10');
        }

        return $result;
    }

    /**
     * @param array $res
     * @return int|string
     * 查出管理员数量
     */
    public function getAdminNum($res=array()){
        if(!empty($res['user_name'])){
            $map['user_name'] = ['like',"%{$res['user_name']}%"];
        }
        if(!empty($res['user_email'])){
            $map['user_email'] = ['like',"%{$res['user_email']}%"];
        }

        if(!empty($map)){
            $result = Db::table('books_admin')->where($map)->count();
        }else{
            $result = Db::table('books_admin')->count();
        }

        return $result;
    }


}