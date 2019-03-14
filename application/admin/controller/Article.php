<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2019/3/8
 * Time: 17:37
 */
namespace app\admin\controller;

use think\Controller;
use think\Model;
use think\Db;
use think\View;
use QL\QueryList;
use think\loader;

Class Article extends Controller{


    public function articleList(){
        $address = array('文章管理','站点文章','文章列表');


        $res['article_name'] = input('param.article_name');

        $article = model('article');
        $data = $article->getList($res);
        $num = $article->getNum($res);

        $this->view->res = $res;
        $this->view->num = $num;
        $this->view->data = $data;
        $this->view->address = $address;
        return $this->fetch('template/article_list');
    }

    public function articleAdd(){
        if(request()->isPost()){
            $article_name = input('param.article_name');
            $article_author = input('param.article_author');
            $article_content = input('param.article_content');
            $article_status = input('param.article_status');


            //进行规则验证
            $result = $this->validate(
                [
                    'article_name' => $article_name,
                    'article_author' => $article_author,
                    'article_content' => $article_content,
                ],
                [
                    'article_name' => 'require',
                    'article_author' => 'require',
                    'article_content' => 'require',
                ],
                [
                    'article_name' => '文章名不能为空',
                    'article_author' => '文章作者不能为空',
                    'article_content' => '文章内容不能为空',
                ]
            );

            if (true !== $result) {
                $this->error($result);
            }

            $has = Db::table('books_article')->where('article_name',$article_name)->find();

            if(!empty($has)){
                $this->error('已存在相同标题的文章');
            }

            $time = date('Y-m-d H:i:s',time());
            $data = array('article_name'=>$article_name,'article_author'=>$article_author,'article_content'=>$article_content,'article_status'=>$article_status,'add_time'=>$time);


            $res = Db::table('books_article')->insert($data);
            if(!empty($res)){
                return $this->success('新增成功');
            }else{
                return $this->success('新增失败');
            }
        }

        $address = array('文章管理','站点文章','文章添加');
        $this->view->address = $address;
        return $this->fetch('template/article_add');
    }

    public function articleEdit(){
        $article_id = input('param.article_id');
        $action = input('param.action');

        if (empty($article_id)) {
            return $this->error('id为空');
        }

        if (request()->isPost() && $action == 'edit') {

            $article_name = input('param.article_name');
            $article_author = input('param.article_author');
            $article_content = input('param.article_content');
            $article_status = input('param.article_status');


            //进行规则验证
            $result = $this->validate(
                [
                    'article_name' => $article_name,
                    'article_author' => $article_author,
                    'article_content' => $article_content,
                ],
                [
                    'article_name' => 'require',
                    'article_author' => 'require',
                    'article_content' => 'require',
                ],
                [
                    'article_name' => '文章名不能为空',
                    'article_author' => '文章作者不能为空',
                    'article_content' => '文章内容不能为空',
                ]
            );

            if (true !== $result) {
                $this->error($result);
            }

            $has = Db::table('books_article')->where('article_name', $article_name)->where('article_id', 'neq', $article_id)->find();

            if (!empty($has)) {
                $this->error('已存在相同标题的文章');
            }

            $data = array('article_name'=>$article_name,'article_author'=>$article_author,'article_content'=>$article_content,'article_status'=>$article_status);


            $res = Db::table('books_article')->where('article_id', $article_id)->update($data);

            if (!empty($res)) {
                return $this->success('编辑成功');
            } else {
                return $this->success('编辑失败');
            }
        }


        $address = array('文章管理','网站文章','文章编辑');

        $data = Db::table('books_article')->where('article_id',$article_id)->find();

        $this->view->data = $data;
        $this->view->address = $address;

        return $this->fetch('template/article_edit');
    }

    public function articleDelete(){
        $article_id = input('param.article_id');

        if(empty($article_id)){
            return $this->error('id为空');
        }

        //删除
        $res = Db::table('books_article')->where('article_id',$article_id)->delete();

        if($res){
            return $this->success('删除成功');
        }else{
            return $this->error('删除失败');
        }
    }

    public function uploadImage(){
        $file = $this->request->file('file');

        if(!empty($file)){
            // 移动到框架应用根目录public/static/images/portrait 目录下
            $info = $file->validate(['size'=>1048576,'ext'=>'jpg,png,gif'])->rule('uniqid')->move(ROOT_PATH . 'public/static/images/article');
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
                $path = '/static/images/article/'.$photo;
            }else{
                // 上传失败获取错误信息
                $file->getError();
            }
        }else{
            $photo = '';
        }
        if($photo !== ''){
            $result["code"] = '0';
            $result["msg"] = "上传成功";
            $result['data']["src"] = $path;
            $result['data']["title"] = $photo;


        }else{
            // 上传失败获取错误信息
            $result["code"] = "2";
            $result["msg"] = "上传出错";
            $result['data']["src"] ='';
        }

        return json_encode($result);
    }




}