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
use QL\QueryList;
use think\loader;

Class Rule extends Base
{
    public function index(){

        $address = array('首页','采集规则','规则管理');

        //等待添加的规则
        $result =  Db::table('books_cou')->select();
        foreach ($result as $val){
            $href = parse_url($val['books_url']);
            if(empty($href['host'])){
                continue;
            }

            $arr[] = $href['host'];
        }
        $arr = !empty($arr) ? array_unique($arr) : array();

        $res=array();
        foreach ($arr as $host){
            $has = Db::table('books_rule')->where('rule_url','like',"%{$host}%")->find();
            if(empty($has)){
                $res[] = $host;
            }
        }

        $data = Db::table('books_rule')->select();

        foreach ($data as &$val){
            $url = parse_url($val['rule_url']);
            $val['host'] = $url['host'];
        }
        $this->view->data = $data;
        $this->view->res = $res;
        $this->view->address = $address;
        return $this->fetch('template/rule_list');
    }

    public function add(){

        $data = input('post.');
        if(!empty($data)){

            $url = parse_url($data['rule_url']);

            if(empty($url['host'])){
                $this->error('搜索地址有误');
            }

            $has = Db::table('books_rule')->where('rule_url','like',"%{$url['host']}%")->find();
            if(!empty($has)){
                return $this->error('规则网址已存在');
            }

            $data['rule_id'] = Db::table('books_rule')->strict(false)->insertGetId($data);
            $res = Db::table('books_rule_info')->strict(false)->insert($data);

            if(!empty($res)){
                return $this->success('新增成功');
            }else{
                return $this->error('新增失败');
            }
        }

        $address = array('首页','采集规则','添加规则');
        $this->view->address = $address;
        return $this->fetch('template/rule_add');
    }

    public function edit(){

        $rule_id = input('param.rule_id');

        $data = Db::table('books_rule')->alias('r')->join('books_rule_info i','i.rule_id=r.rule_id')->where('r.rule_id',$rule_id)->find();


        $address = array('首页','采集规则','修改规则');
        $this->view->address = $address;
        $this->view->data = $data;
        return $this->fetch('template/rule_edit');
    }

    public function editrule(){
        $data = input('post.');
        if(!empty($data)){

            $url = parse_url($data['rule_url']);
            if(empty($url['host'])){
                $this->error('搜索地址有误');
            }

            $has = Db::table('books_rule')->where('rule_id','neq',$data['rule_id'])->where('rule_url','like',"%{$url['host']}%")->find();
            if(!empty($has)){
                return $this->error('规则网址已存在');
            }

            $rel = Db::table('books_rule')->strict(false)->where('rule_id',$data['rule_id'])->update($data);
            $res = Db::table('books_rule_info')->strict(false)->where('rule_id',$data['rule_id'])->update($data);

            if(!empty($res) || !empty($rel)){
                return $this->success('编辑成功');
            }else{
                return $this->error('编辑失败,无数据更新');
            }
        }else{
            return $this->error('数据为空');
        }
    }

    public function delete(){
        $rule_id = input('param.rule_id');

        if (!empty($rule_id)){
            $rel = Db::table('books_rule')->where('rule_id',$rule_id)->delete();
            $res = Db::table('books_rule_info')->where('rule_id',$rule_id)->delete();

            if (!empty($res) && !empty($rel)){
                return $this->success('删除成功');
            }else{
                return $this->error('删除失败');
            }

        }else{
            return $this->error('删除失败，id为空');
        }
    }


    /**
     * @return mixed
     * 采集小说
     */
    public function bookGather(){
        $address = array('首页','采集规则','小说采集');
        $this->view->address = $address;
        return $this->fetch('template/book_gather');
    }

    /**
     * 采集小说具体方法
     */
    public function gather(){

        $obj_url = input('param.obj_url');

        if(empty($obj_url)){
          return  $this->error('采集链接为空');
        }
          //循环太久，会内存用尽，默认是128M
        ini_set('memory_limit','1024M');

        //设置永不超时
        set_time_limit (0);
        

        for($i=1;$i<26;$i++){
            $url = $obj_url.$i;

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
                        }else{
                            continue;
                        }
                    }
                }
            }

        }

        return $this->success('采集完成');

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


            $has = Db::table('books_cou')->where('books_name',$name)->find();
            if(empty($has)){
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


                //下载小说封面
                $path = ROOT_PATH. 'public/static/images/books_img/';
                $imgName = $curl->downloadImg($url,$path);

                $result = ['books_name'=>$name,'books_author'=>$author,'books_synopsis'=>$synopsis,'books_time'=>$time,'books_img'=>$imgName,'books_type'=>$type_id,'books_status'=>'0','books_url'=>$href];

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


    }




}