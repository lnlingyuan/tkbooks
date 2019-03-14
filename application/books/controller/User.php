<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2018/5/30
 * Time: 16:54
 */

namespace app\books\controller;
use think\Controller;
use think\Model;
use think\Request;
use think\Db;
use think\view;


class User extends Base
{

    public function index(){

        $user_id = session('user_id');
        $mobile = session('mobile');

        if(empty($user_id)){
            if($mobile){
                $this->redirect('/login/index');
            }else{
                return $this->fetch('template/user');
            }
        }else{
            $user = model('User');
            $history = $user->history($user_id);

            $info =$user->info($user_id);

            $this->view->history = $history;
            $this->view->info = $info;
            $this->view->user_id = $user_id;

            if($mobile){
                return $this->fetch('mobile/user');
            }else{
                return $this->fetch('template/user');
            }
        }


    }

    public function upload_photo(){

        $file = $this->request->file('file');

        $user_id = session('user_id');
        if(empty($user_id)){
            return ajaxJson('100','请先登陆');
        }


        if(!empty($file)){
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->validate(['size'=>1048576,'ext'=>'jpg,png,gif'])->rule('uniqid')->move(ROOT_PATH . 'public/static/images/portrait');
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
                $path = '/static/images/portrait/'.$photo;
                Db::table('books_user')->where('user_id',$user_id)->update(['user_img'=>$path]);

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
     * @return mixed
     * 会员中心
     */
   public function userCenter(){
       return $this->fetch('mobile/user_center');
   }

    /**
     * @return mixed
     * 勋章
     */
    public function badge(){
       return $this->fetch('mobile/user_badge');
    }

    /**
     * @return mixed
     * 消息中心
     */
    public function message(){
        return $this->fetch('mobile/user_message');
    }


    public function login(){
        $user_id = session('user_id');
        if(!empty($user_id)){
            return $this->success('','/user/index');
        }else{
            return $this->error('不登陆,何来个人中心？','/login/index');
        }
    }


}