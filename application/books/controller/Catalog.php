<?php
/**
 * Created by PhpStorm.
 * User: end
 * Date: 2018/5/17
 * Time: 11:22
 * explain：书籍目录
 */

namespace app\books\controller;
use think\Controller;
use think\Model;
use think\Request;
use think\Db;
use think\view;
use QL\QueryList;
use think\loader;

class Catalog extends Base
{
    /*查出书籍目录*/
    public function index(){


        $books_id = input('param.books_id');

        $catalog = model("Catalog");
        $books = $catalog->getBook($books_id);

        $match = $catalog->getCatalog($books_id);

        $chapter_num = count($match);

        $this->view->books_id = $books_id;
        $this->view->chapter_num = $chapter_num;
        $this->view->books_name = $books['books_name'];
        $this->view->books_author = $books['books_author'];
        $this->view->data = $match;
        $mobile = session('mobile');
        if($mobile){
            return $this->fetch('mobile/catalog');
        }else{
            return $this->fetch('template/catalog');
        }
    }





}