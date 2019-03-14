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

Class User extends Base{


    /**
     * @return mixed
     * 会员列表
     */
    public function index(){
        $address = array('角色管理','网站用户','会员列表');


        $res['user_name'] = input('param.user_name');
        $res['user_email'] = input('param.user_email');

        $user = model('user');
        $data = $user->getList($res);
        $num = $user->getNum($res);

        $this->view->res = $res;
        $this->view->num = $num;
        $this->view->data = $data;
        $this->view->address = $address;
        return $this->fetch('template/user_list');
    }

    /**
     * @return mixed
     * 新增会员
     */
    public function add(){

        $address = array('角色管理','网站用户','会员添加');
        $this->view->address = $address;
        return $this->fetch('template/user_add');
    }

    public function addUser(){

        $user_name = input('param.user_name');
        $user_email = input('param.user_email');
        $user_password = input('param.user_password');
        $user_img = input('param.user_img');
        $is_disable = input('param.is_disable');


        //进行规则验证
        $result = $this->validate(
            [
                'username' => $user_name,
                'email' => $user_email,
                'user_password' => $user_password,
            ],
            [
                'username' => 'require|max:25',
                'email' => 'require|email',
                'user_password' => 'require|min:5',
            ],
            [
                'username' => '用户名不能为空',
                'email' => '邮箱格式不正确',
                'user_password' => '密码不能少于5位',
            ]
        );

        if (true !== $result) {
            $this->error($result);
        }

        $has = Db::table('books_user')->where('user_name',$user_name)->whereOr('user_email',$user_email)->find();

        if(!empty($has)){
            $this->error('用户名或邮箱已存在');
        }

        $pwd = md5($user_password);
        $time = date('Y-m-d H:i:s',time());
        $user_img = empty($user_img) ? '/static/images/timg.jpg' : $user_img;
        $data = array('user_name'=>$user_name,'user_email'=>$user_email,'user_password'=>$pwd,'add_time'=>$time,'is_disable'=>$is_disable,'user_img'=>$user_img);

        $res = Db::table('books_user')->insert($data);

        if(!empty($res)){
            return $this->success('新增成功');
        }else{
            return $this->success('新增失败');
        }


    }


    /**
     * @return mixed|string
     * 修改会员资料
     */
    public function edit(){

        $user_id =  input('param.user_id');

        if(empty($user_id)){
            return $this->error('用户id为空');
        }

        $address = array('首页','会员管理','会员编辑');

        $data = Db::table('books_user')->where('user_id',$user_id)->find();

        $this->view->data = $data;
        $this->view->address = $address;

        return $this->fetch('template/user_edit');
    }

    public function editUser(){

        $user_id = input('param.user_id');
        $user_name = input('param.user_name');
        $user_email = input('param.user_email');
        $user_img = input('param.user_img');
        $is_disable = input('param.is_disable');

        //进行规则验证
        $result = $this->validate(
            [
                'username' => $user_name,
                'email' => $user_email,
            ],
            [
                'username' => 'require|max:25',
                'email' => 'require|email',
            ],
            [
                'username' => '用户名不能为空',
                'email' => '邮箱格式不正确',
            ]
        );

        if (true !== $result) {
            $this->error($result);
        }

        $has = Db::table('books_user')->where('user_id','<>',$user_id)->where('user_name',$user_name)->whereOr('user_email',$user_email)->find();

        if(!empty($has)){
            $this->error('用户名或邮箱已存在');
        }

        $data = array('user_name'=>$user_name,'user_email'=>$user_email,'is_disable'=>$is_disable,'user_img'=>$user_img);

        $res = Db::table('books_user')->where('user_id',$user_id)->update($data);

        return $this->success('编辑成功');

    }

    /**
     * 启用或停用用户
     */
    public function ajaxIsDisable(){
       $disable = input("param.is_disable");
       $user_id = input("param.user_id");

        $data['is_disable'] = '1';
       if(!empty($disable)){
           $data['is_disable'] = '0';
       }

       $res = Db::table('books_user')->where('user_id',$user_id)->update($data);

       if($res){
           return $this->success('已停用');
       }else{
           return $this->error('停用失败');
       }

    }


    /**
     * @return \think\response\Json
     * 用户头像上头
     */
    public function upload_photo(){

        $file = $this->request->file('file');

        if(!empty($file)){
            // 移动到框架应用根目录public/static/images/portrait 目录下
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
     * 修改用户密码
     */
    public function userPwd(){
        $address = array('角色管理','网站用户','密码修改');

        $user_id = input("param.user_id");
        $user_password = input("param.user_password");
        $confirm_password = input("param.confirm_password");
        if($user_password){
            if(empty($confirm_password)){
                    return $this->error('确认密码不能为空！');
             }

            if($user_password == $confirm_password){

                if(preg_match("/^\w+$/",$user_password)){
                    $pwd = md5($user_password);
                    Db::table('books_user')->where('user_id',$user_id)->update(['user_password'=>$pwd]);
                    return $this->success('修改成功！');

                }else{
                    return $this->error('密码只能由数字、26个英文字母或者下划线组成！');
                }

            }else{
                return $this->error('密码和确认密码不一致！');
            }
        }



        $this->view->address = $address;
        $this->view->user_id = $user_id;
        $this->view->user_password = $user_password;
        $this->view->confirm_password = $confirm_password;
        return $this->fetch('template/user_password');
    }


    /**
     * 删除会员，
     * 注：将同时删除会员的阅读记录和书架记录
     */
    public function delete(){
        $user_id = input('param.user_id');

        if(empty($user_id)){
            return $this->error('用户id为空');
        }

        //删除会员表会员记录
        $res = Db::table('books_user')->delete($user_id);
        //删除会员阅读记录
        Db::table('books_history')->where('user_id',$user_id)->delete();
        //删除会员书架
        Db::table('books_shelf')->where('user_id',$user_id)->delete();

        if($res){
            return $this->success('删除成功');
        }else{
            return $this->error('删除失败');
        }


    }



    /**
     * @return mixed
     * 管理员列表
     */
    public function adminList(){
        $address = array('角色管理','后台管理员','管理员列表');
        $this->view->address = $address;


        $res['admin_name'] = input('param.admin_name');

        $user = model('user');
        $data = $user->getAdminList($res);
        $num = $user->getAdminNum($res);

        $this->view->res = $res;
        $this->view->num = $num;
        $this->view->data = $data;
        return $this->fetch('template/admin_list');
    }

    public function adminAdd(){

        if(request()->isPost()){
            $admin_name = input('param.admin_name');
            $admin_password = input('param.admin_password');
            $admin_power = input('param.admin_power/a');
            $admin_describe = input('param.admin_describe');
            $is_disable = input('param.is_disable');

            //进行规则验证
            $result = $this->validate(
                [
                    'admin_name' => $admin_name,
                    'admin_password' => $admin_password,
                    'admin_describe' => $admin_describe,
                ],
                [
                    'admin_name' => 'require|max:25',
                    'admin_describe' => 'require',
                    'admin_password' => 'require|min:5',
                ],
                [
                    'admin_name' => '用户名不能为空',
                    'admin_describe' => '描述不能为空',
                    'admin_password' => '密码不能少于5位',
                ]
            );

            if (true !== $result) {
                $this->error($result);
            }

            $has = Db::table('books_admin')->where('admin_name',$admin_name)->find();

            if(!empty($has)){
                $this->error('管理员名称已存在');
            }

            $pwd = md5($admin_password);
            $time = date('Y-m-d H:i:s',time());
            $admin_power = !empty($admin_power) ? json_encode($admin_power) : '';
            $data = array('admin_name'=>$admin_name,'admin_describe'=>$admin_describe,'admin_password'=>$pwd,'admin_power'=>$admin_power,'add_time'=>$time,'is_disable'=>$is_disable);


            $res = Db::table('books_admin')->insert($data);

            if(!empty($res)){
                return $this->success('新增成功');
            }else{
                return $this->success('新增失败');
            }
        }


        //栏目
        $meunjson = cache('meun');
        $meun = json_decode($meunjson,true);

        $address = array('角色管理','后台管理员','添加管理员');
        $this->view->address = $address;
        $this->view->meun = $meun;
        return $this->fetch('template/admin_add');
    }

    /**
     * @return mixed|string
     * 修改会员资料
     */
    public function adminedit(){

        $admin_id =  input('param.admin_id');
        $action =  input('param.action');

        if(empty($admin_id)){
            return $this->error('角色id为空');
        }

        if(request()->isPost() && $action=='edit' ){
            $admin_name = input('param.admin_name');
            $admin_password = input('param.admin_password');
            $admin_power = input('param.admin_power/a');
            $admin_describe = input('param.admin_describe');
            $is_disable = input('param.is_disable');

            //进行规则验证
            $result = $this->validate(
                [
                    'admin_name' => $admin_name,
                    'admin_password' => $admin_password,
                    'admin_describe' => $admin_describe,
                ],
                [
                    'admin_name' => 'require|max:25',
                    'admin_describe' => 'require',
                    'admin_password' => 'require|min:5',
                ],
                [
                    'admin_name' => '用户名不能为空',
                    'admin_describe' => '描述不能为空',
                    'admin_password' => '密码不能少于5位',
                ]
            );

            if (true !== $result) {
                $this->error($result);
            }

            $has = Db::table('books_admin')->where('admin_name',$admin_name)->where('admin_id','neq',$admin_id)->find();

            if(!empty($has)){
                $this->error('管理员名称已存在');
            }

            $pwd = md5($admin_password);
            $admin_power = !empty($admin_power) ? json_encode($admin_power) : '';
            $data = array('admin_name'=>$admin_name,'admin_describe'=>$admin_describe,'admin_password'=>$pwd,'admin_power'=>$admin_power,'is_disable'=>$is_disable);


            $res = Db::table('books_admin')->where('admin_id',$admin_id)->update($data);

            if(!empty($res)){
                return $this->success('编辑成功');
            }else{
                return $this->success('编辑失败');
            }
        }


        $address = array('角色管理','后台管理员','编辑管理员');

        $data = Db::table('books_admin')->where('admin_id',$admin_id)->find();
        $data['admin_power'] = !empty($data['admin_power']) ? json_decode($data['admin_power'],true) : array();
        //栏目
        $meunjson = cache('meun');
        $meun = json_decode($meunjson,true);
        $this->view->meun = $meun;

        $this->view->data = $data;
        $this->view->address = $address;

        return $this->fetch('template/admin_edit');
    }


    /**
     * 删除管理员
     */
    public function admindelete(){
        $admin_id = input('param.admin_id');


        if(empty($admin_id)){
            return $this->error('角色id为空');
        }

        $res = Db::table('books_admin')->where('admin_id',$admin_id)->delete();

        if($res){
            return $this->success('删除成功');
        }else{
            return $this->error('删除失败');
        }


    }





}