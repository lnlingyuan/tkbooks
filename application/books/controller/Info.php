<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2018/5/31
 * Time: 16:28
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

class Info extends Base
{
    /*章节详情*/
    public function index(){

        //读取阅读设置，如果cookie为空，表示用户还未自定阅读设置，则采用默认规则
        $fit = cookie('fit');
        $fit = json_decode($fit,true);
        if(empty($fit)){
            $fit=array(
                'font'=>'160',
                'size'=>'1.125rem',
                'background'=>'skin-default',
                'slide'=>'Slide_tb',
                'time'=>'0',
            );
        }



        $books_id = input('param.books_id');


        $chapter_url = input('param.chapter_url');


        //取读缓存，看是否有预读章节
        $cache_name = md5($chapter_url);
        $data = cache($cache_name);


        if(empty($data)){

           // $chapter_url=  str_replace('*','/',$chapter_url).'.html';
            $chapter_url=  base64_decode($chapter_url);

            $curl = model("Curl");
            $res = $curl->getDataHttps($chapter_url);


            $href = parse_url($chapter_url);
            $rule = Db::table('books_rule')->field('info_title,info_content')->alias('r')->join('books_rule_info i','i.rule_id=r.rule_id')->where('rule_url','like',"%{$href["host"]}%")->find();
            /*取得小说地址*/
            $data = array(
                'title'=>array($rule['info_title'],'text'),
                'content'=>array($rule['info_content'],'text','<br />'),
            );
            //第三方类库
            Loader::import('QueryList', EXTEND_PATH);
            //匹配出所有章节
            $info = QueryList::Query($res,$data)->data;
            $chapter_content = mb_convert_encoding($info[0]['content'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
            $chapter_name =mb_convert_encoding($info[0]['title'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
        }else{

            $chapter_name = $data['chapter_name'];
            $chapter_content = $data['chapter_content'];
            $chapter_url = $data['chapter_url'];

        }

        //记录阅读进度,如果未登陆则不记录
        $user_id = session('user_id');
        if(!empty($user_id)){
            //先删除原有本书记录
            Db::table('books_history')->where('books_id',$books_id)->where('user_id',$user_id)->delete();
            $time = date('Y-m-s H:i:s',time());
            $data=array('books_id'=>$books_id,'user_id'=>$user_id,'history_name'=>$chapter_name,'history_url'=>$chapter_url,'history_time'=>$time);
            //新增阅读记录
            Db::table('books_history')->insert($data);
        }

        $this->view->chapter_name = $chapter_name;
        $this->view->chapter_url = $chapter_url;
        $this->view->books_id = $books_id;
        $this->view->text = $chapter_content;
        $this->view->fit = $fit;

        $mobile = session('mobile');
        if($mobile){
            return $this->fetch('mobile/info');
        }else{
            return $this->fetch('template/info');
        }

        return $this->fetch('template/info');
    }

    /**取出下一章内容
     * @return \think\response\Json
     * ajax取出下一章内容，由于效果不佳，暂时弃用
     */
    public function nextInfo(){

        $chapter_name = input('param.chapter_name');
        $books_id = input('param.books_id');


        $data = array();

        $catalog = model("Catalog");
        $books = $catalog->getBook($books_id);


        $curl = model("Curl");
        $res = $curl->getDataHttps($books['books_url']);


        /*取得小说地址*/
        $chapter_all = array(
            'text'=>array('dd>a','text'),
            'href'=>array('dd>a','href'),
        );

        //第三方类库
        Loader::import('QueryList', EXTEND_PATH);
        //匹配出所有章节
        $match = QueryList::Query($res,$chapter_all)->data;

        //去除前面重复的几个最新章节
        array_splice($match,0,9);

        foreach ($match as $key=>$val){
            //使用该函数对结果进行转码
            $text = mb_convert_encoding($val['text'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
            if($text==$chapter_name) {

                //小于count($match)说明才有下一章节
                if($key+1<count($match)){
                    $chapter_url=  'https://www.bqg5.cc'.$match[$key+1]['href'];
                    $curl = model("Curl");
                    $res = $curl->getDataHttps($chapter_url);

                    //取得小说章节
                    $name="/<h1>(.*?)<\/h1>/si";
                    preg_match($name, $res,$cname);

                    $chapter_name =mb_convert_encoding($cname[1], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');

                    //取得小说内容
                    $regex="/<div id=\"content\".*?>.*?<\/div>/ism";
                    preg_match($regex, $res, $match);

                    //记录阅读进度,如果未登陆则不记录
                    $user_id = session('user_id');
                    if(!empty($user_id)){
                        //先删除原有本书记录
                        Db::table('books_history')->where('books_id',$books_id)->where('user_id',$user_id)->delete();
                        $time = date('Y-m-s H:i:s',time());
                        $data=array('books_id'=>$books_id,'user_id'=>$user_id,'history_name'=>$chapter_name,'history_url'=>$chapter_url,'history_time'=>$time);
                        //新增阅读记录
                        Db::table('books_history')->insert($data);
                    }


                    $chapter_content =mb_convert_encoding($match[0], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
                    $data['content'] =$chapter_content;
                    $data['chapter_name'] = $chapter_name;
                    $data['code'] = '200';
                    break;
                }

            }
        }



            return json($data,200);

        }

    /**
     * @return \think\response\Json
     * ajax请求小说目录
     */
    public function ajaxCatalog(){

        $books_id = input('param.books_id');

        $catalog = model("Catalog");
        $match = $catalog->getCatalog($books_id);

        return json($match,200);

    }

    //二维数组去掉重复值
    public  function array_unique_fb($array){

        $array = array_reverse($array);
        foreach ($array as $v) {
            $v = join(",", $v); //降维,也可以用implode,将一维数组转换为用逗号连接的字符串
            $temp[] = $v;
        }

        $temp = array_unique($temp);//去掉重复的字符串,也就是重复的一维数组
        foreach ($temp as $k => $v) {
            $temp[$k] = explode(",", $v);//再将拆开的数组重新组装
        }

        return $temp;

    }


    /**
     * @return \think\response\Json
     * 预读下一章节内容
     */
    public function nextchapter(){


        $chapter_url = input('param.chapter_url');
        $books_id = input('param.books_id');

        $catalog = model("Catalog");
        $href = parse_url($chapter_url);
        $rule = $catalog->getRule($href["host"]);

        //取得目录
        $match = $catalog->getCatalog($books_id);

        $data = array();

        $cl = base64_encode($chapter_url);
        //匹配出链接相对应该的位置，即阅读到哪个章节
        $res = array_filter($match, function($t) use ($cl) { return $t[1] == $cl; });
        $chapter_key = key($res);

        if($chapter_key>0){

            $c_url = end($match[$chapter_key-1]);
            $info_url = base64_decode($c_url);

            $curl = model("Curl");
            $res = $curl->getDataHttps($info_url);

            /*取得小说地址*/
            $data = array(
                'title'=>array($rule['info_title'],'text'),
                'content'=>array($rule['info_content'],'text','<br />'),
            );
            //第三方类库
            Loader::import('QueryList', EXTEND_PATH);
            //匹配出所有章节
            $info = QueryList::Query($res,$data,'','UTF-8','GB2312')->data;

            $chapter_content = mb_convert_encoding($info[0]['content'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
            $chapter_name =mb_convert_encoding($info[0]['title'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');

            $next_chapter_url =   $c_url; //章节链接

            $cache_name = md5($next_chapter_url);

            $data['chapter_name'] = $chapter_name;
            $data['chapter_content'] = $chapter_content;
            $data['chapter_url'] = $info_url;
            cache($cache_name,$data,600);

            return $this->success('','',$next_chapter_url);
        }else{
            $next_chapter_url = 'javascript:void(0);';
            return $this->success('','',$next_chapter_url);
        }

    }

    /**
     * @return \think\response\Json
     * 预读上一章节内容
     */
    public function upperchapter(){

        $chapter_url = input('param.chapter_url');
        $books_id = input('param.books_id');

        $catalog = model("Catalog");
        $href = parse_url($chapter_url);
        $rule = $catalog->getRule($href["host"]);


        $data = array();

        //取得目录
        $match = $catalog->getCatalog($books_id);

        $cl = base64_encode($chapter_url);
        //匹配出链接相对应该的位置，即阅读到哪个章节
        $res = array_filter($match, function($t) use ($cl) { return $t[1] == $cl; });
        $chapter_key = key($res);

        $count = count($match)-1;

        if($chapter_key<$count){

            $c_url = end($match[$chapter_key+1]);
            $info_url = base64_decode($c_url);
            $curl = model("Curl");
            $res = $curl->getDataHttps($info_url);

            /*取得小说地址*/
            $data = array(
                'title'=>array($rule['info_title'],'text'),
                'content'=>array($rule['info_content'],'text','<br />'),
            );
            //第三方类库
            Loader::import('QueryList', EXTEND_PATH);
            //匹配出所有章节
            $info = QueryList::Query($res,$data,'','UTF-8','GB2312')->data;
            $chapter_content = mb_convert_encoding($info[0]['content'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
            $chapter_name =mb_convert_encoding($info[0]['title'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');

            $upper_chapter_url =   $c_url; //章节链接

            $cache_name = md5($upper_chapter_url);

            $data['chapter_name'] = $chapter_name;
            $data['chapter_content'] = $chapter_content;
            $data['chapter_url'] = $info_url;
            cache($cache_name,$data,600);
            return $this->success('','',$upper_chapter_url);
        }else{
            $upper_chapter_url = 'javascript:void(0);';
            return $this->success('','',$upper_chapter_url);
        }


    }

    /**
     * 书评区
     */
    public function forum(){

        $books_id = input('books_id');

        $this->view->books_id = $books_id;
        return $this->fetch('mobile/forum');
    }


    public function fit(){
        $fit['font'] = input('font');
        $fit['size'] = input('size');
        $fit['background'] = input('background');
         $fit['slide'] = input('slide');
         $fit['time'] = input('time');

         $fit = json_encode($fit);
         cookie('fit',$fit);

    }



}