<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2018/6/7
 * Time: 11:31
 */

namespace app\books\controller;
use think\Controller;
use think\Request;
use think\Db;
use think\view;


class Apotheosize extends Base
{
    public function index(){
        return $this->fetch('template/apotheosize');
    }
}