<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2018/5/30
 * Time: 10:53
 */

namespace app\books\controller;

use think\Controller;
use think\Model;
use think\Request;
use think\Db;
use think\view;
use QL\QueryList;
use think\loader;
use think\Cache;
use think\Debug;

class ZtCurl
{

    public function ztBooks(){

        //循环太久，会内存用尽，默认是128M
        ini_set('memory_limit','1024M');
        echo ini_get('memory_limit');
        die;

        set_time_limit (0);
        //引入curl方法
        $curl = model('Curl');

        //第三方类库
        Loader::import('QueryList', EXTEND_PATH);


        for($i=1;$i<20;$i++){

            $url = 'https://www.zhetian.org/sort/all/'.$i.'.html';
            $html = $curl->geturldata($url);
            $book_info = array(
                'type' => array('.n>a:even','text'),
                'name' => array('.n>a>strong','text'),
                'link' => array('.n>a:odd','href'),
                // 采集所有a标签的文本内容
                'author' => array('.a>a','text'),
            );


            $data = QueryList::Query($html,$book_info)->data;
            foreach ($data as $key=>$val){

                $type = $this->bookType($val['type']);
                $url = 'https://www.zhetian.org/'.$val['link'];

                $restul = ['books_name'=>$val['name'],'books_author'=>$val['author'],'books_type'=>$type,'books_url'=>$url];
                $books_id = Db::table('books_cou')->insertGetId($restul);
                $this->ztChapter($books_id,$url);
            }
        }

        echo "完成";die;
    }

    public function ztChapter($books_id,$url){
        //引入curl方法
        $curl = model('Curl');

        //第三方类库
        Loader::import('QueryList', EXTEND_PATH);

        $html = $curl->geturldata($url);
        $book_info = array(
            'chapter_name'=>array('.dirlist>li>a','text'),
            'chapter_url'=>array('.dirlist>li>a','href'),
        );

        $data = QueryList::Query($html,$book_info)->data;

        foreach ($data as $key=>$val){
            $url = 'https://www.zhetian.org/'.$val['chapter_url'];
            $sql = "INSERT INTO `books`.`books_chapter`(`books_id`, `chapter_name`, `chapter_url`) VALUES ('{$books_id}', '{$val['chapter_name']}', '{$url}')";
            Db::query($sql);
        }
    }


    public function bookType($type){
        $arrType = array('1'=>'玄幻','奇幻','武侠','仙侠','都市','历史','军事','游戏','竞技','科幻','灵异','同人','女生','其他');
        $data = array_search($type,$arrType);
        return !empty($data)?$data:'1';
    }




    /**
     * 更新小说
     *
     */
    public function ztBooksNew(){
        Debug::remark('begin');
        //循环太久，会内存用尽，默认是128M
        ini_set('memory_limit','1024M');


        set_time_limit (0);
        //引入curl方法
        $curl = model('Curl');

        //开启即时显示输出
        ob_end_clean();
        ob_implicit_flush(1);


        //第三方类库
        Loader::import('QueryList', EXTEND_PATH);
        for($j=1;$j<17;$j++){
            $stari = $j*29-29>0?$j*29-29:1;
            $endi = $j*29;

            echo '当前正在处理第'.$j."批数据......<br/>";
            echo '开始时间：'.date('Y-m-d H:i:s')."<br/>";

            $url =array();

            for($i=$stari;$i<$endi;$i++){

                $url[] = 'https://www.zhetian.org/sort/all/'.$i.'.html';

            }

            $all = $curl->getManyUrl($url);
            if(in_array('',$all)){
                $all = $this->getAllUrl($url);
            }
            if(empty($all)){
                $this->getAllUrl($url);
                halt($all);
            }

            foreach ($all as $ak=>$av){

                $book_info = array(
                    'type' => array('.n>a:even','text'),
                    'name' => array('.n>a>strong','text'),
                    'link' => array('.n>a:odd','href'),
                    // 采集所有a标签的文本内容
                    'author' => array('.a>a','text'),
                );
                $data = QueryList::Query($av,$book_info)->data;

                if(!empty($data)){
                    $result = array();
                    foreach ($data as $key=>$val){

                        $type = $this->bookType($val['type']);
                        $url = 'https://www.zhetian.org/'.$val['link'];
                        $hasbooks = Db::table('books_cou')->where('books_name',$val['name'])->value('books_name');
                        if(!empty($hasbooks)){
                            continue;
                        }
                        $result[] = ['books_name'=>$val['name'],'books_author'=>$val['author'],'books_type'=>$type,'books_url'=>$url];

                    }
                    $result =  $this->array_unique_fb($result);
                    Db::table('books_cou')->insertAll($result);

                }


            }
            echo '第'.$j."批数据储存完毕......<br/>";
            echo '完成时间为'.date('Y-m-d H:i:s')."<br/><br/><br/>";

        }

        Debug::remark('end');
        echo Debug::getRangeTime('begin','end').'s';

        //耗时：0.475秒
    }

    public function ztChapterNew(){

        //循环太久，会内存用尽，默认是128M
        ini_set('memory_limit','1024M');

        //设置永不超时
        set_time_limit (0);

        //第三方类库
        Loader::import('QueryList', EXTEND_PATH);



        //开启即时显示输出
        Debug::remark('begin');

        Db::table('books_cou')->where('books_time','exp',' is NULL')->chunk(40, function($books) {
            $url= array();
            foreach ($books as $book) {
                $url[] = $book['books_url'];
            }

            //引入curl方法
            $curl = model('Curl');
            $all = $curl->getManyUrl($url);


            /*  if(in_array('',$all)){
                  $all = $this->getAllUrl($url);
              } */



            $result=array();

            foreach ($all as $key=>$val){
                if(empty($val)){
                    continue;
                }

                $book_info = array(
                    'name' => array('h1','text'),
                    'status' => array('#author>i:last','text'),
                    'time' => array('#update>i:first','text'),
                    'synopsis' => array('#intro>p:first','text'),
                );

                //根据地址取得小说简介，小说状态，小说更新时间
                $data = QueryList::Query($val,$book_info)->data;
                //验证不为空
                $books_time = !empty($data[0]['time']) ? $data[0]['time'] : '';
                $synopsis = !empty($data[0]['synopsis']) ? trim(addslashes($data[0]['synopsis'])) : '';

                if(!empty($data[0]['status'])){
                    if($data[0]['status'] == '连载中'){
                        $status = '0';
                    }else{
                        $status = '1';
                    }
                }else{
                    $status = '';
                }
                $books_name = $data[0]['name'];
                $r = array_filter($books, function($t) use ($books_name) { return $t['books_name'] == $books_name; });
                $bid = array_column($r,'books_id');

                //下载小说封面
                /*    $book_img = array(
                        'image' => array('.novelinfo-r>img','src'),
                    );

                    $img = QueryList::Query($val,$book_img)->data;
                    $path = ROOT_PATH. 'public/static/images/books_img/';
                    $imgName = $curl->downloadImg($img[0]['image'],$path); */
                $imgName = '736d65f673cfd5014699c6c2e3d2b027.jpg';
                $result[] = ['books_id'=>$bid[0],'books_time'=>$books_time,'books_synopsis'=>$synopsis,'books_status'=>$status,'books_img'=>$imgName];

            }
            $cou  = \model('Cou');
            $cou->updateAll($result);

        });

        $editdata =  DB::table('books_cou')->where('books_time','exp',' is NULL')->find();
        if(!empty($editdata)){
            $this->ztChapterNew();
        }

        Debug::remark('end');
        echo Debug::getRangeTime('begin','end').'s';



    }




    /**
     * 更新小说章节
     * 由于取所有章节存数据库，占内存太大，故弃用
     */
    public function ztChapterNew_Disuse(){

        //第三方类库
        Loader::import('QueryList', EXTEND_PATH);

        //开启即时显示输出
        ob_end_clean();
        ob_implicit_flush(1);
        Debug::remark('begin');

        Db::table('books_cou')->chunk(100, function($books) {
            foreach ($books as $book) {
                echo '当前正在处理第'.$book['books_id'].'本小说《'.$book['books_name']."》的章节数据......<br/>";
                echo '开始时间：'.date('Y-m-d H:i:s')."<br/>";
                //引入curl方法
                $curl = model('Curl');
                $html = $curl->geturldata($book['books_url']);
                $book_info = array(
                    'chapter_name'=>array('.dirlist>li>a','text'),
                    'chapter_url'=>array('.dirlist>li>a','href'),
                );

                $data = QueryList::Query($html,$book_info)->data;

                foreach ($data as $key=>$val){
                    $url = 'https://www.zhetian.org/'.$val['chapter_url'];
                    $restul[] = ['books_id'=>$book['books_id'],'chapter_name'=>$val['chapter_name'],'chapter_url'=>$url];

                    //Db::query($sql);
                }
                Db::table('books_chapter')->insertAll($restul);

                echo '小说《'.$book['books_name']."》章节数据储存完毕......<br/>";
                echo '完成时间为'.date('Y-m-d H:i:s')."<br/><br/><br/>";

            }


        });

        Debug::remark('end');
        echo Debug::getRangeTime('begin','end').'s';



    }




    /**
     * @param array $url 网址数组
     * @return mixed 当并发请求返回有空时，重新请求
     */
    public function getAllUrl($url=array()){
        $curl=\model('Curl');
        $all = $curl->getManyUrl($url);
        if(in_array('',$all)){
            $this->getAllUrl($url);
        }
        return $all;

    }

    /**
     * @param $arr
     * @return array
     * 数组去重
     */
    public  function array_unique_fb($arr){
        if(!empty($arr)){
            $res = array();
            foreach ($arr as $key=>$val){
                $res[$key] = $val['books_name'];
            }
            $res = array_unique($res);
            foreach ($res as $rk=>$rv){
                $data[] = $arr[$rk];
            }
        }else{
            $data = array();
        }


        return $data;
    }



}