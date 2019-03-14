<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2018/6/2
 * Time: 1:31
 */

namespace app\books\controller;


use think\Controller;
use think\loader;
use think\Db;
use think\Session;
use think\Request;

class Base extends Controller {
    public function _initialize(){

        //保持登陆
        $user_name = cookie('user_name');
        $user_id = cookie('user_id');
        if(!empty($user_name) && !empty($user_id)){
            session('user_name', $user_name);
            session('user_id', $user_id);
        }


        $user_name = session('user_name');
        if(!empty($user_name)){
            $this->view->session_name = $user_name;
            $this->view->session_name_id = session('user_id');
        }else{

            $this->view->session_name_id = '';
        }

        if(!empty($_SERVER["HTTP_ACCEPT_ENCODING"])){
            if( !headers_sent() && // 如果页面头部信息还没有输出
                extension_loaded("zlib") && // 而且php已经加载了zlib扩展
                strstr($_SERVER["HTTP_ACCEPT_ENCODING"],"gzip")) //而且浏览器接受GZIP
            {
                ini_set('zlib.output_compression', 'On');
                ini_set('zlib.output_compression_level', '4');
            }
        }

        //搜索记录
        $search_arr = cookie('search_cookie');
        $this->view->search_arr = $search_arr;


        //头部meta设置
        $this->view->module_data = self::seo();

        //判断用户设备
        Loader::import('Mobile_Detect', EXTEND_PATH);
        $detect = new \Mobile_Detect;
        // Any mobile device (phones or tablets).
        if ( $detect->isMobile() ) {
           Session::set('mobile','1');
        }else{
            Session::delete('mobile');
        }

        //判断当前控制器
        $request = Request::instance();
        $cer = $request->controller();
       $this->view->cer = $cer;

    }


    /**
     * @return array|false|mixed|\PDOStatement|string|\think\Model
     * 头部meta,seo优化
     */
    protected static function seo()
    {
        //网站meta内容
        $module_data = Db::table('books_module')->field('module_data')->where('module_key','settings')->cache(60)->find();
        $module_data = json_decode($module_data['module_data'],true);
        //seo是否有单独设置
        $request = Request::instance();
        //取得当前控制器和操作名称
        $seo_module = $request->controller().'/'.$request->action();
        $seo = Db::table('books_seo')->field('seo_title,seo_keywords,seo_description')->where('seo_module',$seo_module)->where('is_disable','0')->cache(60)->find();
        if(!empty($seo)){
            $module_data['title'] = $seo['seo_title'];
            $module_data['keywords'] = $seo['seo_keywords'];
            $module_data['descript'] = $seo['seo_description'];
        }

        return $module_data;
    }

    //空操作
    public function _empty()
    {
        abort(404);
    }
}