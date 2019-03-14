<?php
/**
 * Created by PhpStorm.
 * User: end
 * Date: 2018/5/2
 * Time: 15:32
 */

namespace app\books\controller;
use think\Controller;
use think\Model;
use think\Request;
use think\Db;
use QL\QueryList;
use think\loader;
use think\view;
use think\Cache;



class Index extends Base
{

    public function index(){

        //如果未登陆，跳转到静态首页
        $user_name = session('user_name');
        if(empty($user_name)){
            $htmlpath = APP_PATH.'books/view/static/index.html';
            if(file_exists ($htmlpath)){
                return  $this->fetch('static/index');
            }
        }


        //前百本小说
        $all = Db::table('books_cou')->limit(100)->select();

        //所有小说类型
        $type = Db::table('books_type')->select();
        $types= array();
        foreach ($type as $value){
            $types[$value['type_id']] = $value['type_name'];
        }

        //热门精选
        $module = Db::table('books_module')->where('module_key','hot')->value('module_data');
        $books_ids = get_object_vars(json_decode($module));
        foreach ($books_ids as $ikey=>$ival){
            $host[$ikey] = Db::table('books_cou')->where('books_id','in',$ival)->select();

        }


        $this->view->host = $host;
        $this->view->all = $all;
        $this->view->types = $types;

        $mobile = session('mobile');
        if($mobile){
            //手机幻灯片
            $module = Db::table('books_module')->where('module_key','slide_wap')->value('module_data');
            $data = json_decode($module,true);
            $this->view->data = $data;

            return $this->fetch('mobile/index');
        }else{
            //电脑幻灯片
            $module = Db::table('books_module')->where('module_key','slide')->value('module_data');
            $books_ids = json_decode($module,true);


            $books_id = implode(',',$books_ids['books_ids']);

            //幻灯片的小说,排序按in里的原id排
            $data = Db::table('books_cou')->where('books_id','in',$books_id)->orderRaw("field(books_id,{$books_id})")->select();
            $this->view->data = $data;

            return $this->fetch('template/index');
        }

    }

    public function cover(){
        return $this->fetch('template/cover');
    }




    //更新小说信息
    public function editBooks(){

        header('Content-Type: text/html; charset=utf-8');

        //引入curl方法
        $curl = model('Curl');
        //第三方类库
        Loader::import('QueryList', EXTEND_PATH);


        //取出小说地址
       $result =  Db::table('books_cou')->select();

       foreach ($result as $val){

           $url = $val['books_url'];
           $html = $curl->getUrlData($url);
           $book_info = array(
               'status' => array('#author>i:last','text'),
               'time' => array('#update>i:first','text'),
               'synopsis' => array('#intro>p:first','text'),
           );

            //根据地址取得小说简介，小说状态，小说更新时间
           $data = QueryList::Query($html,$book_info)->data;

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

           $id = $val['books_id'];
           $sql ='UPDATE `books`.`books_cou` SET `books_time` = "'.$books_time.'", `books_status` = "'.$status.'", `books_synopsis` = "'.$synopsis.'" WHERE `books_id` = "'.$id.'"';
           echo $sql."<br/>";
           Db::query($sql);

       }


       echo "更新完成！";

    }

    //更新小说封面
    public function downloadImg(){

        //引入curl方法
        $curl = model('Curl');
        //第三方类库
        Loader::import('QueryList', EXTEND_PATH);

        $result =  Db::table('books_cou')->select();

        foreach ($result as $val){

            $url = $val['books_url'];
            $html = $curl->getUrlData($url);
            $book_info = array(
                'image' => array('.novelinfo-r>img','src'),
            );

            $data = QueryList::Query($html,$book_info)->data;
            $path = dirname(__FILE__).'/../images/';
            $imgName = $curl->downloadImg($data[0]['image'],$path);

            $id = $val['books_id'];
            $sql ='UPDATE `books`.`books_cou` SET `books_img` = "'.$imgName.'" WHERE `books_id` = "'.$id.'"';
            echo $sql."<br/>";
            Db::query($sql);

        }


    }


    /*章节内容*/
    public function info(){
        header('Content-Type: text/html; charset=utf-8');

        //引入curl方法
        $curl = model('Curl');
        $name = urlencode('修真聊天群');
        $url = 'http://www.zaidudu.net/search.php?searchkey='.$name;
        $res = $curl->getUrlData($url);
print_r($res);die;
        //第三方类库
        Loader::import('QueryList', EXTEND_PATH);

        /*取得小说地址*/
        $book_info = array(
            'href' => array('a.result-game-item-title-link','href'),
        );

        $data = QueryList::Query($res,$book_info)->data;

        $rescon = $curl->getUrlData($data[0]['href']);

        /*匹配具体章节*/
        $book_url = array(
            'name'=>array('dd>a','text'),
            'href' => array('dd>a','href'),
        );

        $data = QueryList::Query($rescon,$book_url)->data;

echo "<pre>";
print_r($data);die;

        halt($data);

    }

    //更新小说阅读地址
    public function res()
    {
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
                //读取小说章节目录
                $ret = $curl->getUrlData($rb[0]['href']);
                $book_chart = array(
                    'text' => array('#list>dl>dd>a', 'text'),
                    'href' => array('#list>dl>dd>a', 'href'),
                );
                $cha = QueryList::Query($ret, $book_chart)->data;

                $chapter = Db::table("books_chapter")->where('books_id', '1')->select();

                foreach ($chapter as $ch) {
                 /*   $name = $ch['chapter_name'];
                    $r = array_filter($cha, function ($t) use ($name) {return $t['text'] == $name;});
                    if (!empty($r)) {
                        //key从0开始
                        $r = array_values ($r);

                        $url = "http://www.xbiquge.la/xiaoshuodaquan/";
                        $read_url = dirname($url) . $r[0]['href'];
                        $sql = "UPDATE `books`.`books_chapter` SET `read_url` = '" . $read_url . "' WHERE `chapter_id` = " . $ch['chapter_id'];

                        Db::query($sql);

                    }*/
                }


            }
            echo "6"."<br>";
            }
            echo "完成";die;


        } else {
            echo "无可用小说列表";
        }

    }

    //将网址起来
    public function write(){
        header('Content-Type: text/html; charset=utf-8');

        //引入curl方法
        $curl = model('Curl');
        $url = 'http://www.xbiquge.la/xiaoshuodaquan/';
        $res = $curl->getUrlData($url);

        //第三方类库
        Loader::import('QueryList', EXTEND_PATH);

        /*取得小说地址*/
        $book_info = array(
            'text'=>array('.novellist>ul>li>a','text'),
            'href' => array('.novellist>ul>li>a','href'),
        );

        $data = QueryList::Query($res,$book_info)->data;
        // 把PHP数组转成JSON字符串
        $json_string = json_encode($data);
        // 写入文件
        file_put_contents('url.json', $json_string);

    }

    //读取网址
    public function readUrl(){
        // 从文件中读取数据到PHP变量
        $json_string = file_get_contents('url.json');
        // 把JSON字符串转成PHP数组
        $data = json_decode($json_string, true);
        // 显示出来看看
        return $data;
    }

    public function phpinfo(){
        phpinfo();
    }

    /**
     * 首页热门精选模块
     */
    public function moduleHost(){
        $data=array();

        $res = Db::table('books_cou')->where('books_type=4')->field('books_id')->limit(5)->select();
        $data['4'] =implode(',',array_column($res,'books_id'));

        $res = Db::table('books_cou')->where('books_type=5')->field('books_id')->limit(5)->select();
        $data['5'] =implode(',',array_column($res,'books_id'));

        $res = Db::table('books_cou')->where('books_type=6')->field('books_id')->limit(5)->select();
        $data['6'] =implode(',',array_column($res,'books_id'));

        $res = Db::table('books_cou')->where('books_type=7')->field('books_id')->limit(5)->select();
        $data['7'] =implode(',',array_column($res,'books_id'));

        $data = json_encode($data);


        $result = array('module_name'=>'热门精选','module_data'=>$data);
        Db::table('books_module')->insert($result);

        halt($data);
    }

    /**
     * @return mixed
     * 版权声明
     */
    public function copyright(){
        //导航栏高亮
        $cer = 'Copyright';
        $this->view->cer = $cer;

        $mobile = session('mobile');
        if($mobile){
            return $this->fetch('mobile/copyright');
        }else{
            return $this->fetch('template/copyright');
        }
    }

    /**
     * @return mixed
     * 关于我们
     */
    public function about(){
        return $this->fetch('template/about');
    }

    /**
     * @return mixed
     * 隐私条款
     */
    public function privacy(){
        return $this->fetch('template/privacy');
    }

    /**
     * @return mixed
     * 申请收录
     */
    public function employ(){
        return $this->fetch('template/employ');
    }




}
