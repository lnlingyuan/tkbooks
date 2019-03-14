<?php
/**
 * Created by PhpStorm.
 * User: end
 * Date: 2018/5/10
 * Time: 16:31
 * explain:书籍详情页
 */

namespace app\books\controller;
use think\Controller;
use think\Model;
use think\Request;
use think\Db;
use QL\QueryList;
use think\loader;
use think\view;
use think\Debug;

class Cover extends Base
{
    public function Index(){
        $core = model('Cover');
        $books_id =input('param.books_id');


        //所有小说类型
        $type = Db::table('books_type')->select();
        $types= array();
        foreach ($type as $value){
            $types[$value['type_id']] = $value['type_name'];
        }

        $data = $core->getBook($books_id);
        if(!empty($data)){
            $data['books_status'] = !empty($data['books_status']) ? '完结' : '连载中';
            $this->view->data = $data;
        }else{
            return $this->error('无此书籍');
        }

        //如果用户已经登陆，则判断书籍是否在书架
        $user_id = session('user_id');
        $books_has = '';
        $history_url='';
        if(!empty($user_id)){
           $has =  DB::table('books_shelf')->where('user_id',$user_id)->where('books_id',$books_id)->find();
           if(!empty($has)){
               $books_has = '1';
           }

            $history_url =  DB::table('books_history')->where('user_id',$user_id)->where('books_id',$books_id)->value('history_url');
            $history_url =  base64_encode($history_url); //加密
        }

        $res = Db::table('books_cou')->order('books_time desc')->limit(100)->cache('key',300)->select();

        //畅言代码
        $changyan = Db::table('books_module')->where('module_key','changyan')->find();
        $changyan = json_decode($changyan['module_data'],true);

        //重定义seo
        $module_data =  self::seo();
        $module_data['title'] ="《".$data['books_name']."》_".$data['books_author']."_".$types[$data['books_type']]."_".$module_data['title'];
        $module_data['keywords'] =  $data['books_name'].','.$data['books_name'].'最新章节,'.$data['books_name'].'全文阅读,'.$data['books_name'].'无弹窗';
        $module_data['descript'] =  $data['books_name'].'，'. $data['books_name'].'小说阅读。'.$types[$data['books_type']].'小说'.$data['books_name'].'由作家'.$data['books_author'].'创作，'.$module_data['name'].'提供'.$data['books_name'].'最新章节及章节列表，'.$data['books_name'].'最新更新尽在'.$module_data['name'];
        $this->view->module_data = $module_data;

        $this->view->books_id = $data['books_id'];
        $this->view->books_has = $books_has;
        $this->view->res = $res;
        $this->view->types = $types;
        $this->view->changyan = $changyan;
        $this->view->history_url = $history_url;

        $mobile = session('mobile');
        if($mobile){
            return $this->fetch('mobile/cover');
        }else{
            return $this->fetch('template/cover');
        }


    }

    /**
     * @return \think\response\Json
     * 加入书架
     */
    public function addShelf(){
        $user_id = session('user_id');;
        $books_id = input("param.books_id");

        if(empty($user_id)){

            return ajaxJson('100','请先登陆','');
        }
        if(empty($books_id)){

            return ajaxJson('100','书籍id不能为空','');
        }

        $has = Db::table('books_shelf')->where('user_id',$user_id)->where('books_id',$books_id)->find();

        if(!empty($has)){
            return ajaxJson('100','书籍已在书架','');
        }else{

            $data = ['user_id'=>$user_id,'books_id'=>$books_id];
            Db::table('books_shelf')->insert($data);
            return ajaxJson('200','加入书架成功','');

        }



    }

    public function test(){
        $core = model('Cover');
        $books_id =input('param.books_id');

        Debug::remark('begin');
        //所有小说类型
        $type = Db::table('books_type')->select();
        Debug::remark('end');
        echo Debug::getRangeTime('begin','end').'s';
        $types= array();
        foreach ($type as $value){
            $types[$value['type_id']] = $value['type_name'];
        }

    }

    //获取最新章节
    public function newChapter(){
        $url = input('post.books_url');
        $books_id = input('post.books_id');

        //引入curl方法
        $curl = model('Curl');
        $all = $curl->getDataHttps($url);


        $href = parse_url($url);


        $rule = Db::table('books_rule')->field('chapter_name,chapter_url,info_title,info_content')->alias('r')->join('books_rule_info i','i.rule_id=r.rule_id')->where('rule_url','like',"%{$href["host"]}%")->find();


        /*取得小说地址*/
        $chapter_all = array(
            'text'=>array($rule['chapter_name'],'text'),
            'href'=>array($rule['chapter_url'],'href'),
        );

        //第三方类库
        Loader::import('QueryList', EXTEND_PATH);
        //匹配出所有章节
        $match = QueryList::Query($all,$chapter_all,'','UTF-8','GB2312')->data;

        //去除前面重复的几个最新章节
        $match = array_unique_fb($match);

        $end_chapter = array_shift($match);

        $chapter_url =  correct_url($url,$end_chapter[1]);



        $res = $curl->getDataHttps($chapter_url);

        /*取得小说地址*/
        $data = array(
            'title'=>array($rule['info_title'],'text'),
            'content'=>array($rule['info_content'],'text','<br />'),
        );
        //第三方类库
        Loader::import('QueryList', EXTEND_PATH);
        //匹配出所有章节
        $info = QueryList::Query($res,$data,'','UTF-8','GB2312')->data;



        if(!empty($info)){

            $data['chapter_name'] =$info[0]['title'];
            $chapter_content = strip_tags($info[0]['content']);
            $data['chapter_content'] = substr($chapter_content,0,429).'...';

            $chapter_url = base64_encode($chapter_url); //加密
            $data['chapter_url'] = url('/info/index',['books_id'=>$books_id,'chapter_url'=>$chapter_url]);


            return ajaxJson('200','',$data);
        }else{
            return ajaxJson('100');
        }








    }


}