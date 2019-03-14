<?php
/**
 * Created by PhpStorm.
 * User: end
 * Date: 2018/5/2
 * Time: 15:32
 */

namespace app\admin\controller;

use think\Controller;
use think\Model;
use think\View;
use think\Db;
use think\Cache;



Class Index extends Base
{

    public function index(){

        return $this->fetch('template/index');
    }

    public function welcome(){

        $books_num = Db::table('books_cou')->count();
        $user_num = Db::table('books_user')->count();
        $rule_num = Db::table('books_rule')->count();
        $time = time();


            $version = Db::query('SELECT VERSION() AS ver');
            $config  = [
                'url'             => $_SERVER['HTTP_HOST'],
                'document_root'   => $_SERVER['DOCUMENT_ROOT'],
                'server_os'       => PHP_OS,
                'server_port'     => $_SERVER['SERVER_PORT'],
                'server_ip'       => $_SERVER['SERVER_ADDR'],
                'server_soft'     => $_SERVER['SERVER_SOFTWARE'],
                'php_version'     => PHP_VERSION,
                'mysql_version'   => $version[0]['ver'],
                'max_upload_size' => ini_get('upload_max_filesize')
            ];

        $address = array('首页','我的桌面','欢迎回来');

        $this->view->address = $address;
        $this->view->config = $config;
        $this->view->books_num = $books_num;
        $this->view->rule_num = $rule_num;
        $this->view->user_num = $user_num;
        $this->view->time = $time;

        return $this->fetch('template/welcome');
    }

    /**
     * 清除缓存
     */
    public function clear(){
        Cache::clear();
    }

    /**
     * 缓存栏目
     * 当管理员设置权限时可用
     */
    public function meun(){
        $meunjson = input('param.meunjson');
        Cache::set('meun',$meunjson);

    }


}