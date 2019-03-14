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
use think\Request;

Class Settings extends Base
{
    public function website(){

        $data = input("post.");
        if(!empty($data)){
            $result['module_data'] = json_encode($data);
            $res = Db::table('books_module')->where('module_key','settings')->update($result);
            if(!empty($res)){
                return $this->success('修改成功');
            }else{
                return $this->error('修改失败');
            }
        }else{
            $result = Db::table('books_module')->field('module_data')->where('module_key','settings')->find();
            $data = json_decode($result['module_data'],true);
        }


        $address = array('首页','系统设置','网站设置');
        $this->view->address = $address;
        $this->view->data = $data;
        return $this->fetch('template/settings_website');
    }

    /**
     * @return mixed|void
     * 邮件设置
     */
    public function mail(){

        $data = input("post.");
        if(!empty($data)){

            $result['module_data'] = json_encode($data);
            $res = Db::table('books_module')->where('module_key','stmp')->update($result);
            if(!empty($res)){
                return $this->success('修改成功');
            }else{
                return $this->error('修改失败');
            }
        }else{
            $result = Db::table('books_module')->field('module_data')->where('module_key','stmp')->find();
            $data = json_decode($result['module_data'],true);
        }

        $address = array('首页','系统设置','邮件设置');
        $this->view->data = $data;
        $this->view->address = $address;
        return $this->fetch('template/settings_mail');
    }


    /**
     * @return mixed|void
     * 幻灯片设置
     */
    public function slide(){

        $data = input("post.");
        if(!empty($data)){

            $result['module_data'] = json_encode($data);
            $res = Db::table('books_module')->where('module_key','slide')->update($result);
            if(!empty($res)){
                return $this->success('修改成功');
            }else{
                return $this->error('修改失败');
            }
        }else{
            $result = Db::table('books_module')->field('module_data')->where('module_key','slide')->find();
            $data = json_decode($result['module_data'],true);

            $res = Db::table('books_module')->field('module_data')->where('module_key','slide_wap')->find();
            $slide_wap = json_decode($res['module_data'],true);

        }

        foreach ($data['books_ids'] as $k=>$bs){

            $books[$k]['books_id'] = $bs;
            $arr = Db::table('books_cou')->field('books_name')->where('books_id',$bs)->find();
            $books[$k]['books_name'] = $arr['books_name'];
        }

        $address = array('首页','首页设置','幻灯片设置');
        $this->view->address = $address;
        $this->view->books = $books;
        $this->view->slide_wap = $slide_wap;
        return $this->fetch('template/settings_slide');
    }

    public function slide_wap(){

        $data = input("post.");
        if(!empty($data)){
            $result['module_data'] = json_encode($data['data']);
            $res = Db::table('books_module')->where('module_key','slide_wap')->update($result);
            if(!empty($res)){
                return $this->success('修改成功');
            }else{
                return $this->error('修改失败');
            }
        }else{
           return $this->error('数据为空');
        }

    }


    /**
     * 首页缓存
     */
    public function homeCache(){
        $address = array('首页','首页设置','首页缓存');
        $this->view->address = $address;

        $file = APP_PATH.'books/view/static/index.html';
        if(request()->isPost()){
            $home_switch = input('post.home_switch');
            if($home_switch == 'true'){
                $request = Request::instance();
                $home = $request->domain();
                $res = $this->buildHtml('index',APP_PATH.'books/view/static/',$home);
               if(empty($res)){
                   return $this->success('首页静态化成功');
               }else{
                   return $this->error($res);
               }
            }else{

                if (!unlink($file))
                {
                    return $this->error('静态化关闭失败');
                }
                else
                {
                    return $this->success('已关闭静态化');
                }

            }
        }

        if(file_exists ($file)){
            $home_switch = true;
        }else{
            $home_switch = false;
        }
        $this->view->home_switch = $home_switch;

        return $this->fetch('template/settings_home_cache');
    }

    public function editHome(){
        $file = APP_PATH.'books/view/static/index.html';
        if(!file_exists ($file)){
            return $this->error("请先开启首页静态化");
        }

        $request = Request::instance();
        $home = $request->domain();
        $res = $this->buildHtml('index',APP_PATH.'books/view/static/',$home);
        if(empty($res)){
            return $this->success('更新成功');
        }else{
            return $this->error($res);
        }

    }

    /**
     * @param string $htmlfile
     * @param string $htmlpath
     * @param string $templateFile
     * @return mixed
     * 生成首页缓存
     */
    protected function buildHtml($htmlfile = '', $htmlpath = '', $url = '')
    {
        //引入curl方法
        $curl = model('Curl');
        $content = $curl->getUrlData($url);
        $htmlpath = !empty($htmlpath) ? $htmlpath : './appTemplate/';
        $htmlfile = $htmlpath . $htmlfile . '.'.config('url_html_suffix');

        $File = new \think\template\driver\File();
        $res = $File->write($htmlfile, $content);
        return $res;
    }

    /**
     * @return mixed
     * 修改管理员密码
     */
    public function myPwd(){
        if(request()->isPost()){
            $admin_id = input('post.admin_id');
            $now_password = input('post.now_password');
            $user_password = input('post.user_password');
            $confirm_password = input('post.confirm_password');

            if(empty($now_password) || empty($now_password) || empty($confirm_password) ){
                $this->error('必填项不能为空');
            }

            if(!empty($admin_id)){
                $pwd = md5($now_password);
                $admin = Db::table('books_admin')->where('admin_id',$admin_id)->where('admin_password',$pwd)->find();
                if(!empty($admin)){
                    if($user_password == $confirm_password){
                        $res['admin_password'] = md5($user_password);
                        Db::table('books_admin')->where('admin_id',$admin_id)->update($res);
                        $this->success('密码修改成功');
                    }else{
                        $this->error('两次密码不一致');
                    }
                }else{
                    $this->error('原密码错误');
                }

            }else{
                $this->error('管理员id为空');
            }
        }




        $address = array('设置','我的设置','修改密码');
        $this->view->address = $address;
        return $this->fetch('template/admin_password');
    }


    /**
     * @return \think\response\Json
     * 上传手机版幻灯片
     */
    public function upload_img(){

        $file = $this->request->file('file');

        if(!empty($file)){
            // 移动到框架应用根目录public/static/images/portrait 目录下
            $info = $file->validate(['size'=>1048576,'ext'=>'jpg,png,gif'])->rule('uniqid')->move(ROOT_PATH . 'public/static/images/mobile_slide');
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
                $path = '/static/images/mobile_slide/'.$photo;
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
     * 功能弃用中
     */
    public function authority(){

        $address = array('设置','权限检测','检测权限');
        $this->view->address = $address;
        return $this->fetch('template/settings_authority');
    }


    /**
     * @return mixed|void
     * 畅言接入
     */
    public function changyan(){

        if(request()->isPost()){

            $res['appid'] = input('param.appid');
            $res['conf'] = input('param.conf');

            if(empty( $res['appid']) || empty( $res['conf'])){
                return $this->error('必填项不能为空');
            }
            $arr['module_data'] = json_encode($res);
            $result  = Db::table('books_module')->where('module_key','changyan')->update($arr);

            if(!empty($result)){
                return $this->success('更新成功！');
            }

        }

        $data = Db::table('books_module')->where('module_key','changyan')->find();
        $data = json_decode($data['module_data'],true);

        $address = array('设置','系统设置','畅言接入');
        $this->view->address = $address;
        $this->view->data = $data;
        return $this->fetch('template/settings_changyan');
    }

    /**
     * @return mixed
     * seo设置列表
     */
    public function seoList(){
        $address = array('设置','seo设置','seo管理');


        $res['seo_remark'] = input('param.seo_remark');

        $user = model('settings');
        $data = $user->getList($res);
        $num = $user->getNum($res);

        $this->view->res = $res;
        $this->view->num = $num;
        $this->view->data = $data;
        $this->view->address = $address;
        return $this->fetch('template/seo_list');
    }

    /**
     * @return mixed
     * 新增seo
     */
    public function seoAdd(){

        if(request()->isPost()){
            $seo_module = input('param.seo_module');
            $seo_remark = input('param.seo_remark');
            $seo_title  = input('param.seo_title');
            $seo_keywords = input('param.seo_keywords');
            $seo_description = input('param.seo_description');
            $is_disable = input('param.is_disable');



            //进行规则验证
            $result = $this->validate(
                [
                    'seo_module' => $seo_module,
                    'seo_remark' => $seo_remark,
                    'seo_title' => $seo_title,
                    'seo_keywords' => $seo_keywords,
                    'seo_description' => $seo_description,
                ],
                [
                    'seo_module' => 'require',
                    'seo_remark' => 'require',
                    'seo_title' => 'require',
                    'seo_keywords' => 'require',
                    'seo_description' => 'require',
                ],
                [
                    'seo_module' => '模块标识不能为空',
                    'seo_remark' => '模块备注不能为空',
                    'seo_title' => 'title不能为空',
                    'seo_keywords' => 'keywords不能为空',
                    'seo_description' => 'description不能为空',
                ]
            );

            if (true !== $result) {
                $this->error($result);
            }

            $has = Db::table('books_seo')->where('seo_module',$seo_module)->find();

            if(!empty($has)){
                $this->error('标识已存在');
            }

            $data = array('seo_module'=>$seo_module,'seo_remark'=>$seo_remark,'seo_title'=>$seo_title,'seo_keywords'=>$seo_keywords,'seo_description'=>$seo_description,'is_disable'=>$is_disable);


            $res = Db::table('books_seo')->insert($data);
            if(!empty($res)){
                return $this->success('新增成功');
            }else{
                return $this->success('新增失败');
            }
        }

        $address = array('角色管理','网站用户','seo添加');
        $this->view->address = $address;
        return $this->fetch('template/seo_add');
    }


    /**
     * @return mixed|void
     * 修改seo
     */
    public function seoEdit()
    {

        $seo_id = input('param.seo_id');
        $action = input('param.action');

        if (empty($seo_id)) {
            return $this->error('id为空');
        }

        if (request()->isPost() && $action == 'edit') {

            $seo_module = input('param.seo_module');
            $seo_remark = input('param.seo_remark');
            $seo_title = input('param.seo_title');
            $seo_keywords = input('param.seo_keywords');
            $seo_description = input('param.seo_description');
            $is_disable = input('param.is_disable');


            //进行规则验证
            $result = $this->validate(
                [
                    'seo_module' => $seo_module,
                    'seo_remark' => $seo_remark,
                    'seo_title' => $seo_title,
                    'seo_keywords' => $seo_keywords,
                    'seo_description' => $seo_description,
                ],
                [
                    'seo_module' => 'require',
                    'seo_remark' => 'require',
                    'seo_title' => 'require',
                    'seo_keywords' => 'require',
                    'seo_description' => 'require',
                ],
                [
                    'seo_module' => '模块标识不能为空',
                    'seo_remark' => '模块备注不能为空',
                    'seo_title' => 'title不能为空',
                    'seo_keywords' => 'keywords不能为空',
                    'seo_description' => 'description不能为空',
                ]
            );

            if (true !== $result) {
                $this->error($result);
            }

            $has = Db::table('books_seo')->where('seo_module', $seo_module)->where('seo_id', 'neq', $seo_id)->find();

            if (!empty($has)) {
                $this->error('模块标识已存在');
            }

            $data = array('seo_module' => $seo_module, 'seo_remark' => $seo_remark, 'seo_title' => $seo_title, 'seo_keywords' => $seo_keywords, 'seo_description' => $seo_description, 'is_disable' => $is_disable);


            $res = Db::table('books_seo')->where('seo_id', $seo_id)->update($data);

            if (!empty($res)) {
                return $this->success('编辑成功');
            } else {
                return $this->success('编辑失败');
            }
        }


        $address = array('设置','seo设置','seo管理');

        $data = Db::table('books_seo')->where('seo_id',$seo_id)->find();

        $this->view->data = $data;
        $this->view->address = $address;

        return $this->fetch('template/seo_edit');
    }

    /**
     * @return mixed|void
     * 删除seo
     */
    public function seoDelete(){
        $seo_id = input('param.seo_id');

        if(empty($seo_id)){
            return $this->error('模块id为空');
        }

        //删除
        $res = Db::table('books_seo')->where('seo_id',$seo_id)->delete();

        if($res){
            return $this->success('删除成功');
        }else{
            return $this->error('删除失败');
        }


    }




}