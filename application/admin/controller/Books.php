<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2018/8/1
 * Time: 16:25
 */

namespace app\admin\controller;

use think\Controller;
use think\Model;
use think\Db;
use think\View;
use QL\QueryList;
use think\loader;

Class Books extends Base{


    /**
     * @return mixed
     * 添加新书籍
     */
    public function add(){

        $books_name = input('post.books_name');
        $rule_id = input('post.rule_id');

        //过滤下传入名称，防注入
        $books_name = filter($books_name);

        if(!empty($books_name)){

            $rule = Db::table('books_rule')->where('rule_id',$rule_id)->find();

            if(!empty($rule['is_urlencode'])){
                $books_name =  urlencode($books_name);
            }

            $curl = model('Curl');
            $url=$rule['rule_url'].$books_name;
            $data = $curl->getDataHttps($url,$books_name);

            //第三方类库
            Loader::import('QueryList', EXTEND_PATH);
            //取得小说信息
            $content = array(
                'name'=>array($rule['search_name'],'text'),
                'href'=>array($rule['search_url'],'href'),
            );

            //匹配出信息
            $result = QueryList::Query($data,$content)->data;
            foreach ($result as &$val){
                $val['name'] = mb_convert_encoding( $val['name'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
            }

            $this->view->result = $result;
        }

        //规则列表
        $rule_list = Db::table('books_rule')->field('rule_id,rule_name')->where('is_search','1')->select();
        $this->view->rule_list = $rule_list;

        $address = array('首页','小说管理','添加小说');
        $this->view->address = $address;

        $this->view->books_name= urldecode($books_name);
        $this->view->rule_id= $rule_id;
        return $this->fetch('template/books_add');
    }


    /**
     * 搜索小说后入库
     */
    public function Warehousing(){
        $href = input('post.href');
        $name = input('post.name');
        $rule_id = input('post.rule_id');

        if(!empty($name)){
            $has = Db::table('books_cou')->where('books_name',$name)->find();
            if(!empty($has)){
                $this->error('书籍已存在');
            }

        }else{
            $this->error('书籍名为空，请重新搜索');
        }

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
               // array_splice($match,0,9);
                $match = $this->array_unique_fb($match);


                foreach ($match as $key=>$val){
                    //使用该函数对结果进行转码
                    $chapter[$key]['text'] = mb_convert_encoding($val[0], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
                    $chapter[$key]['href'] = $val[1];

                }

                $end_chapter = end($chapter);

              $chapter_data = ['books_id'=>$books_id,'chapter_name'=>$end_chapter['text'],'chapter_url'=>$end_chapter['href']];

              Db::table('books_chapter')->insert($chapter_data);

              if($books_id){
                  return $this->success('入库成功');
              }else{
                  return $this->error('入库失败');
              }


            }


            return $this->error('入库失败');
    }

    /**
     * @return mixed
     * 所有小说
     */
    public function booksList(){

        $res['star_time']    = input("param.star_time");
        $res['end_time']     = input("param.end_time");
        $res['books_name']   = input("param.books_name");
        $res['books_author'] = input("param.books_author");
        $res['books_type']   = input("param.books_type");
        $res['books_url']   = input("param.books_url");


        $books = model('Books');
        $data = $books->getList($res);
        $count = $books->getCount($res);


        $type = Db::table('books_type')->select();
        foreach ($type as $val){
            $types[$val['type_id']] = $val['type_name'];
        }

        $address = array('首页','小说管理','小说列表');
        $this->view->address = $address;

        $num = count($data);

        $this->view->data = $data;
        $this->view->count = $count;
        $this->view->types = $types;
        $this->view->type = $type;
        $this->view->res = $res;
        return $this->fetch('template/books_list');
    }



    /**
     * @return mixed|void
     * 编辑小说
     */
    public function edit(){

        $books_id = input('param.books_id');

        if(empty($books_id)){
            return $this->error('书籍id为空');
        }

        $data = Db::table('books_cou')->where('books_id',$books_id)->find();


        $address = array('首页','小说管理','小说编辑');
        $this->view->address = $address;

        $type = Db::table('books_type')->select();
        $this->view->type = $type;

        $this->view->data = $data;
        return $this->fetch('template/books_edit');
    }


    public function editBooks(){
        $result = input('post.');

        //进行规则验证
        $res = $this->validate(
            [
                'books_id' => $result['books_id'],
                'books_name' => $result['books_name'],
                'books_author' => $result['books_author'],
                'books_time' => $result['books_time'],
                'books_type' => $result['books_type'],
                'books_synopsis' => $result['books_synopsis'],
                'books_img' => $result['books_img'],
                'books_url' => $result['books_url'],
            ],
            [
                'books_id' => 'require',
                'books_name' => 'require',
                'books_author' => 'require',
                'books_time' => 'require|date',
                'books_type' => 'require',
                'books_synopsis' => 'require',
                'books_img' => 'require',
                'books_url' => 'require',
            ],
            [
                'books_id' => '书籍id为空',
                'books_name' => '书籍名称不能为空',
                'books_author' => '作者不能为空',
                'books_time' => '更新时间不能为空',
                'books_type' => '请选择书籍类型',
                'books_synopsis' => '书籍简介不能为空',
                'books_img' => '请上传书籍封面',
                'books_url' => '源地址不能为空',
            ]
        );

        if (true !== $res) {
            $this->error($res);
        }

        $has = Db::table('books_cou')->where('books_name',$result['books_name'])->where('books_author',$result['books_author'])->where('books_id','<>',$result['books_id'])->find();
        if(!empty($has)){
            $this->error('此作者的已存在同名作品');
        }

        //取得图片名称
        $result['books_img'] = basename($result['books_img']);
        $data = Db::table('books_cou')->strict(false)->update($result);

        if($data){
            return $this->success('小说更新成功');
        }else{
            return $this->error('小说更新失败');
        }


    }


    /**
     * @return mixed
     * 手动新增小说
     */
    public function  addBooks(){
        $address = array('首页','小说管理','小说添加');
        $this->view->address = $address;


        $type = Db::table('books_type')->select();
        $this->view->type = $type;

        return $this->fetch('template/books_add_manual');
    }

    public function appendBooks(){
        $result = input('post.');
        //进行规则验证
        $res = $this->validate(
            [
                'books_name' => $result['books_name'],
                'books_author' => $result['books_author'],
                'books_time' => $result['books_time'],
                'books_type' => $result['books_type'],
                'books_synopsis' => $result['books_synopsis'],
                'books_img' => $result['books_img'],
                'books_url' => $result['books_url'],
            ],
            [
                'books_name' => 'require',
                'books_author' => 'require',
                'books_time' => 'require|date',
                'books_type' => 'require',
                'books_synopsis' => 'require',
                'books_img' => 'require',
                'books_url' => 'require',
            ],
            [
                'books_name' => '书籍名称不能为空',
                'books_author' => '作者不能为空',
                'books_time' => '更新时间不能为空',
                'books_type' => '请选择书籍类型',
                'books_synopsis' => '书籍简介不能为空',
                'books_img' => '请上传书籍封面',
                'books_url' => '源地址不能为空',
            ]
        );

        if (true !== $res) {
            $this->error($res);
        }

        $has = Db::table('books_cou')->where('books_name',$result['books_name'])->where('books_author',$result['books_author'])->find();
        if(!empty($has)){
            $this->error('书籍已存在');
        }
        //取得图片名称
        $result['books_img'] = basename($result['books_img']);
        $data = Db::table('books_cou')->strict(false)->insert($result);

        if($data){
            return $this->success('小说添加成功');
        }else{
            return $this->error('小说添加失败');
        }


    }

    /**
     * @return \think\response\Json
     * 上传小说封面
     */
    public function upload_photo(){

        $file = $this->request->file('file');

        if(!empty($file)){
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->validate(['size'=>1048576,'ext'=>'jpg,png,gif'])->rule('uniqid')->move(ROOT_PATH . 'public/static/images/books_img');
            $error = $file->getError();
            //验证文件后缀后大小
            if(!empty($error)){
                dump($error);exit;
            }
            if($info){
                // 成功上传后 获取上传信息
                // 输出 jpg
                $info->getExtension();
                // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
                $info->getSaveName();
                // 输出 42a79759f284b767dfcb2a0197904287.jpg
                $photo = $info->getFilename();
                $path = '/static/images/books_img/'.$photo;
            }else{
                // 上传失败获取错误信息
                $file->getError();
            }
        }else{
            $photo = '';
        }
        if($photo !== ''){
            return ajaxJson('200','成功',$path);
        }else{
            return ajaxJson('100','失败');
        }
    }

    /**
     *删除小说
     */
    public function delete(){
        $books_id = input('param.books_id');

        $res = Db::table('books_cou')->where('books_id',$books_id)->delete();
        Db::table('books_chapter')->where('books_id',$books_id)->delete();
        Db::table('books_history')->where('books_id',$books_id)->delete();
        Db::table('books_shelf')->where('books_id',$books_id)->delete();

        if(!empty($res)){
            return $this->success('删除成功');
        }else{
            return $this->error('删除失败');
        }


    }
    /**
     *批量删除小说
     */
    public function delAllList(){
        $books_ids = input('post.books_ids/a');

        foreach ($books_ids as $val){
            $res = Db::table('books_cou')->where('books_id',$val)->delete();
            Db::table('books_chapter')->where('books_id',$val)->delete();
            Db::table('books_history')->where('books_id',$val)->delete();
            Db::table('books_shelf')->where('books_id',$val)->delete();
        }

        $res = Db::table('books_cou')->where('books_id','in',$books_ids)->select();

        if(empty($res)){
            return $this->success('批量删除成功');
        }else{
            return $this->error('批量删除失败');
        }


    }

    /**
     * 清空小说
     */
    public function delAllBooks(){

        $res = Db::execute('TRUNCATE table books_cou');
        $books_chapter = Db::execute('TRUNCATE table books_chapter');
        $books_history = Db::execute('TRUNCATE table books_history');
        $books_shelf = Db::execute('TRUNCATE table books_shelf');

        //删除小说图片
        $path = ROOT_PATH . 'public/static/images/books_img/';
        //调用函数，传入路径
        $this->deldir($path);

        if(empty($res)){
            return $this->success('清除成功');
        }else{
            return $this->error('清除失败');
        }

    }

    public function deldir($path){
        //如果是目录则继续
        if(is_dir($path)){
            //扫描一个文件夹内的所有文件夹和文件并返回数组
            $p = scandir($path);
            foreach($p as $val){
                //排除目录中的.和..
                if($val !="." && $val !=".."){
                    //如果是目录则递归子目录，继续操作
                    if(is_dir($path.$val)){
                        //子目录中操作删除文件夹和文件
                        deldir($path.$val.'/');
                        //目录清空后删除空文件夹
                        @rmdir($path.$val.'/');
                    }else{
                        //如果是文件直接删除
                        unlink($path.$val);
                    }
                }
            }
        }
    }
    /**
     * @return mixed
     * 添加或修改小说最新章节
     */
    public function books_chapter(){
        $address = array('小说管理','小说添加','最新章节');
        $this->view->address = $address;

        $books_id = input('param.books_id');

        if(empty($books_id)){
            $this->error('书籍id为空');
        }

        $chapter_name = input('param.chapter_name');
        $chapter_url = input('param.chapter_url');
        $action = input('param.action');
        $has = input('param.has');

        if($action=='edit'){
            if(!empty($chapter_name) || !empty($chapter_url)){
                $arr = array('books_id'=>$books_id,'chapter_name'=>$chapter_name,'chapter_url'=>$chapter_url);
                if(!empty($has)){
                    $res = Db::table('books_chapter')->where('books_id',$books_id)->update($arr);
                }else{
                    $res = Db::table('books_chapter')->insert($arr);
                }

                if($res !== false) {
                    return $this->success('更新成功');
                }else{
                    return $this->error('更新失败');
                }


            }else{
                $this->error('章节名或源地址不能为空');
            }
        }else{
            $data = Db::table('books_chapter')->where('books_id',$books_id)->find();
            $chapter_name = !empty($data) ? $data['chapter_name'] : '';
            $chapter_url = !empty($data) ? $data['chapter_url'] : '';
            $has =  !empty($data) ? '1' : '0';
        }

        $this->view->books_id = $books_id;
        $this->view->chapter_name = $chapter_name;
        $this->view->chapter_url = $chapter_url;
        $this->view->has = $has;

        return $this->fetch('template/books_chapter');
    }


    //二维数组去掉重复值
    public function array_unique_fb($array){

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

}