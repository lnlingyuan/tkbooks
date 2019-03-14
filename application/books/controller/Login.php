<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2018/6/1
 * Time: 17:06
 */

namespace app\books\controller;


use think\Controller;
use think\Model;
use think\Request;
use think\Db;
use think\Validate;
use think\view;
use PHPMailer\SendEmail;
use think\Session;
use think\Cookie;
use think\loader;

class Login extends Controller
{
    public function _initialize()
    {
        //网站信息
        $module_data = Db::table('books_module')->field('module_data')->where('module_key','settings')->cache(true)->find();
        $module_data = json_decode($module_data['module_data'],true);
        $this->view->module_data = $module_data;

        //判断用户设备
        Loader::import('Mobile_Detect', EXTEND_PATH);
        $detect = new \Mobile_Detect;
        // Any mobile device (phones or tablets).
        if ( $detect->isMobile() ) {
            Session::set('mobile','1');
        }else{
            Session::delete('mobile');
        }
    }

    public function index(){

        $user_id = session('user_id');

        if(!empty($user_id)){
            $this->redirect('/user/index');
        }

        $mobile = session('mobile');
        if($mobile){
            return $this->fetch('mobile/login');
        }else{
            return $this->fetch('mobile/login');
        }
    }

    public function register(){

        $mobile = session('mobile');
        if($mobile){
            return $this->fetch('mobile/register');
        }else{
            return $this->fetch('template/register');
        }

    }

    public function login(){

        //接收前端表单提交的数据
        $user_name = input('post.username');
        $user_password = input('post.password');
        $userCheckPic = input('post.userCheckPic');

        if(empty($user_name) || empty($user_password)){
            return $this->error('用户名或密码不能为空');
        }

        if(empty($userCheckPic)){
            return $this->error('验证码不能为空');
        }else{
            if(!captcha_check($userCheckPic)) {
                // 校验失败
                return $this->error('验证码不正确');
            }
        }


        $user_password = md5($user_password);
        $hasuser = Db::table('books_user')->where("`user_name` = '{$user_name}' AND `user_password` = '{$user_password}'")->find();


        if(!empty($hasuser)){
            // 赋值（当前作用域）
            session('user_name', $hasuser['user_name']);
            session('user_id', $hasuser['user_id']);

            cookie('user_name', $hasuser['user_name'],604800);
            cookie('user_id', $hasuser['user_id'],604800);


            return $this->success('登陆成功','/user/index');
        }else{
            return $this->error('用户名或密码错误！');
        }

    }


    /**
     * 用户注册
     */
    public function userRegister(){

        //接收前端表单提交的数据
        $user_name = input('post.username');
        $user_email = input('post.email');
        $email_vodevalue = input('post.email_vodevalue');
        $user_password = input('post.password');
        $userpassword = input('post.userpassword');
        //进行规则验证
        $result = $this->validate(
            [
                'username' => $user_name,
                'email' => $user_email,
                'email_vodevalue' => $email_vodevalue,
                'password' => $user_password,
                'userpassword' => $userpassword,
            ],
            [
                'username' => 'require|max:25',
                'email' => 'require|email',
                'email_vodevalue' => 'require|number',
                'password' => 'require|min:5',
                'userpassword' => 'require|max:25',
            ],
            [
                'username' => '用户名不能为空',
                'email' => '邮箱格式不正确',
                'email_vodevalue' => '验证码不能为空',
                'password' => '密码不能为空或少于五位',
                'userpassword' => '确认密码不能为空',
            ]
            );

        if (true !== $result) {
           $this->error($result);
        }

        $vodevalue = session::get($user_email);

        if(!empty($vodevalue)){
            if($vodevalue!=$email_vodevalue){
                return $this->error('邮箱验证码有误');
            }
        }else{
            return $this->error('请获取邮箱验证码');
        }

        if($user_password != $userpassword){
            return $this->error('两次密码不一致');
        }


        $hasuser = Db::table('books_user')->where("`user_name` = '{$user_name}' or `user_email` = '{$user_email}'")->find();

        if(!empty($hasuser)){
            return $this->error('用户名或邮箱已被注册');
        }else{
            $data = ['user_name' => $user_name, 'user_email' => $user_email,'user_password' =>md5($user_password),'add_time'=>date('Y-m-d H:i:s',time()),'user_img'=>'/static/images/timg.jpg'];
            Db::table('books_user')->insert($data);
            $userId = Db::name('user')->getLastInsID();
        }


        //写入数据库
        if (!empty($userId)){
            Session::set('user_id',$userId);
            Session::set('user_name',$user_name);
            return $this->success('注册成功', 'User/index');
        } else {
            return $this->error('注册失败');
        }

    }


    /**
     * 退出登陆
     */
    public function loginOut(){
        // 删除（当前作用域）
        session('user_name', null);
        session('user_id', null);

        // 删除（当前作用域）
        cookie('user_name', null);
        cookie('user_id', null);

        return $this->success('登出成功');
    }


    /**
     * @param $user_email
     * @return bool|string 邮箱验证
     */
    public function email($user_email){


            // 生成随机六位数，不足六位两边补零，这个是比较好的做法
            $param = str_pad(mt_rand(0, 999999), 6, "0", STR_PAD_BOTH);

            //查出网站信息
            $settings = Db::table('books_module')->where('module_key','settings')->find();
            $settings = json_decode($settings['module_data'],true);

            $se = Session::set($user_email,$param);


        $content = '<style type="text/css">.qmbox *{margin: 0;padding: 0;} .qmbox html,.qmbox body{background:#fff;} .qmbox td img {display: block; border:none;} .qmbox a { border:0 none;}</style>
    <div style="text-align:left;padding: 0 10px;">
      <table style="width:100%;" border="0" cellpadding="0" cellspacing="0">
        <tbody><tr>
          <td style="height: 60px;background: #0F97FF;">
            <a href="javascript:;" target="_blank" rel="noopener">
              </a>
          </td>
        </tr>
        <tr>
          <td style="font-family: \'Microsoft YaHei\', sans-serif; color: #000000;font-size: 18px;padding: 20px 0 10px;">尊敬的用户：</td></tr>
        <tr>
          <td style="font-family: \'Microsoft YaHei\', sans-serif; color: #000000;font-size: 14px;padding: 10px 0;">您正在进行<span style="color: #0F97FF;">邮箱验证</span>，验证码：<span style="color: #0F97FF;"><span style="border-bottom: 1px dashed rgb(204, 204, 204); z-index: 1; position: static;" t="7" onclick="return false;" >'.$param.'</span></span>，请及时输入验证码。若非本人操作，请忽视此邮件。</td>
		</tr>
        <tr>
          <td style="font-family: \'Microsoft YaHei\', sans-serif; color: #0F97FF;font-size: 14px;padding-bottom: 30px;">（此验证码30分钟内有效，超时需要重新获取验证邮件）</td></tr>
        <tr>
          <td style="font-family: \'Microsoft YaHei\', sans-serif;font-size: 14px;">
            <p style="width: 390px;border-top: 1px dashed #DDDDDD;padding-top: 20px;">如有疑问，请联系客服中心：<a href="'.$settings['domain'].'" target="_blank" style="color: #0F97FF;margin-left: 5px;" rel="noopener">'.$settings['domain'].'</a></p>
          </td>
        </tr>
        <tr>
          <td style="font-family: \'Microsoft YaHei\', sans-serif;font-size: 14px;">此为系统邮件，请勿回复。</td></tr>
      </tbody></table>
    </div>
<style type="text/css">.qmbox style, .qmbox script, .qmbox head, .qmbox link, .qmbox meta {display: none !important;}</style>';


        $email = Db::table('books_module')->where('module_key','stmp')->find();
        $email = json_decode($email['module_data'],true);
        $SendEmail = new SendEmail($email['smtp_server'],$email['send_email'],$email['send_nickname'],$email['send_username'],$email['send_password'],$email['smtp_number']);

        $result = $SendEmail->SendEmail('邮箱验证-'.$settings['name'],$content,$user_email);

        return $result;

    }

    /**
     * 发送邮箱验证码
     * @return \think\response\Json
     */
    public function getEmail(){

        $email = input('post.email');
        $type = input('post.type');
        $checkmail="/\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/";//定义正则表达式
        if(isset($email) && $email!=""){			//判断文本框中是否有值

            if(preg_match($checkmail,$email)){						//用正则表达式函数进行判断

                //判断邮箱是否已经被注册
                $hasemail = Db::table('books_user')->where('user_email',$email)->find();

                //r表示注册，f表示找回密码
                if($type=='r'){
                    if(!empty($hasemail)){
                        return ajaxJson('100','邮箱已被注册','');
                    }
                }elseif ($type=='f'){
                    if(empty($hasemail)){
                        return ajaxJson('100','邮箱尚未注册','');
                    }
                }

                $se = Session::get($email);
                if(empty($se)){

                    $result = $this->email($email);
                    if($result['code'] == '200'){
                        return ajaxJson('200','验证码发送成功','');
                    }else{
                        return ajaxJson('100','验证码发送失败','');
                    }


                }else{
                    return ajaxJson('100','验证码已发送','');
                }


            }else{
                return ajaxJson('100','电子邮箱格式不正确','');
            }
        }



    }

    /**
     * @return mixed|void
     * 忘记密码
     */
    public function forgetpassword(){


        if(request()->isPost()){
          //接收前端表单提交的数据
        $user_email = input('post.email');
        $email_vodevalue = input('post.email_vodevalue');
        $user_password = input('post.password');
        $userpassword = input('post.userpassword');
        //进行规则验证
        $result = $this->validate(
            [
                'email' => $user_email,
                'email_vodevalue' => $email_vodevalue,
                'password' => $user_password,
                'userpassword' => $userpassword,
            ],
            [
                'email' => 'require|email',
                'email_vodevalue' => 'require|number',
                'password' => 'require|min:5',
                'userpassword' => 'require|max:25',
            ],
            [
                'email' => '邮箱格式不正确',
                'email_vodevalue' => '验证码不能为空',
                'password' => '密码不能为空或少于五位',
                'userpassword' => '确认密码不能为空',
            ]
        );

        if (true !== $result) {
            $this->error($result);
        }

        $hasuser = Db::table('books_user')->where("`user_email` = '{$user_email}'")->find();

        if(empty($hasuser)){
            return $this->error('邮箱尚未注册');
        }


        $vodevalue = session::get($user_email);

        if(!empty($vodevalue)){
            if($vodevalue!=$email_vodevalue){
                return $this->error('邮箱验证码有误');
            }
        }else{
            return $this->error('请获取邮箱验证码');
        }

        if($user_password != $userpassword){
            return $this->error('两次密码不一致');
        }



        $data = ['user_password' =>md5($user_password)];
        $res =  Db::table('books_user')->where('user_id',$hasuser['user_id'])->update($data);


        if ($res!== false){
            return $this->success('修改成功,请重新登陆', 'index/index');
        } else {
            return $this->error('修改失败');
        }


        }


        $mobile = session('mobile');
        if($mobile){
            return $this->fetch('mobile/forgetpassword');
        }else{
            return $this->fetch('template/forgetpassword');
        }

    }






    }