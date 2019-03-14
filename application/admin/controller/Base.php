<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2018/6/2
 * Time: 1:31
 */

namespace app\admin\controller;


use think\Controller;
use think\loader;
use think\Session;
use think\Db;

class Base extends Controller {
    public function _initialize(){

        $admin_name = session('admin_name');
        $admin_id = session('admin_id');
        if(!empty($admin_name)){

            $this->view->admin_name = $admin_name;
            $this->view->admin_id =$admin_id;
        }else{

            $this->redirect('login/index');
        }

        $admin = Db::table('books_admin')->where('admin_id',$admin_id)->find() ;
        $this->view->arr = $admin['admin_power'];


    }
}