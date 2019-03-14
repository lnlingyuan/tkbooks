<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2018/7/31
 * Time: 14:50
 */
namespace app\admin\controller;

use think\Controller;
use think\Model;
use think\Db;
use think\View;
use think\Validate;

Class Type extends Base
{
    public function index(){

        $address = array('首页','分类管理','小说分类');


        $data = Db::table('books_type')->select();

        foreach ($data as &$val){
            $val['num'] = Db::table('books_cou')->where('books_type',$val['type_id'])->count();
        }


        $this->view->data = $data;
        $this->view->address = $address;
        return $this->fetch('template/type_list');
    }

    public function add(){

        if(request()->isPost()){
            $data['type_name'] = input('post.type_name');
            $data['type_sort'] = input('post.type_sort');
            if(empty($data['type_name']) || empty($data['type_sort'])){
                $this->error('必填项不能为空');
            }else{
                $res = Db::table('books_type')->insert($data);
                if(!empty($res)){
                    $this->success('新增成功');
                }else{
                    $this->error('新增失败');
                }
            }

        }

        $address = array('分类管理','小说分类','添加分类');
        $this->view->address = $address;
        return $this->fetch('template/type_add');
    }

    public function edit(){
        $type_id = input('param.type_id');
        $type_name = input('param.type_name');

        if(empty($type_name)){
            $this->error('分类名不能为空');
        }

        $has = Db::table('books_type')->where('type_name',$type_name)->where('type_id','neq',$type_id)->find();
        if(empty($has)){
             Db::table('books_type')->where('type_id',$type_id)->update(['type_name'=>$type_name]);
            $this->success('分类名修改成功');
        }else{
            $this->error('存在同名分类');
        }

    }

    public function delete(){
        $type_id = input('param.type_id');
        $res = Db::table('books_type')->where('type_id',$type_id)->delete();
        if(!empty($res)){
            return $this->success('删除成功');
        }else{
            return $this->error('删除失败');
        }
    }
}