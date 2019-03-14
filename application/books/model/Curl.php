<?php
/**
 * Created by PhpStorm.
 * User: end
 * Date: 2018/5/2
 * Time: 17:33
 */

namespace app\books\model;


use think\Model;
use think\Request;

class Curl extends Model
{
    public function getUrlData($url){
        set_time_limit (0);
        $UserAgent = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; SLCC1; .NET CLR 2.0.50727; .NET CLR 3.0.04506; .NET CLR 3.5.21022; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
        $curl = curl_init();    //创建一个新的CURL资源
        curl_setopt($curl, CURLOPT_URL, $url);  //设置URL和相应的选项
        curl_setopt($curl, CURLOPT_HEADER, 0);  //0表示不输出Header，1表示输出
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);  //设定是否显示头信息,1显示，0不显示。
        //如果成功只将结果返回，不自动输出任何内容。如果失败返回FALSE

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_ENCODING, '');   //设置编码格式，为空表示支持所有格式的编码
        //header中“Accept-Encoding: ”部分的内容，支持的编码格式为："identity"，"deflate"，"gzip"。

        curl_setopt($curl, CURLOPT_USERAGENT, $UserAgent);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        //设置这个选项为一个非零值(象 “Location: “)的头，服务器会把它当做HTTP头的一部分发送(注意这是递归的，PHP将发送形如 “Location: “的头)。

        $data = curl_exec($curl);

        if (curl_errno($curl)) {
            echo 'Curl error: ' . curl_error($curl);
            die;
        }

        //关闭URL请求
        curl_close($curl);

        return $data;
    }

    public  function downloadImg($url, $path = 'images/')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        $file = curl_exec($ch);
        curl_close($ch);
        $filename = pathinfo($url, PATHINFO_BASENAME);

        if(!is_dir($path)){
            mkdir($path,0777,true);
        }
        $filename = rand(0,9999999).".jpg";
        $resource = fopen($path . $filename, 'a');
        fwrite($resource, $file);
        fclose($resource);
        return $filename;
    }

    /**
     * 模拟post进行url请求
     * @param string $url
     * @param string $param
     */
    public function request_post($url = '', $param = '') {
        if (empty($url) || empty($param)) {
            return false;
        }

        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);

        return $data;
    }

    //php curl模拟https请求
    public function getDataHttps($url){

        $header_ip = array( 'CLIENT-IP:8.8.8.8', 'X-FORWARDED-FOR:8.8.8.8', );


        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 0);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Expect:'));

        curl_setopt($curl, CURLOPT_TIMEOUT,60);   //只需要设置一个秒的数量就可以

        //伪造来源referer
        curl_setopt($curl, CURLOPT_REFERER, 'http://www.baidu.com/');//模拟来路
        // //伪造来源ip
        curl_setopt($curl, CURLOPT_HTTPHEADER,$header_ip);


        //重要！
        curl_setopt($curl, CURLOPT_HTTPPROXYTUNNEL, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_ENCODING, '');   //设置编码格式，为空表示支持所有格式的编码
        //header中“Accept-Encoding: ”部分的内容，支持的编码格式为："identity"，"deflate"，"gzip"。
        curl_setopt($curl,CURLOPT_USERAGENT,"Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; SLCC1; .NET CLR 2.0.50727; .NET CLR 3.0.04506; .NET CLR 3.5.21022; .NET CLR 1.0.3705; .NET CLR 1.1.4322)'"); //模拟浏览器代理

        //执行命令
        $data = curl_exec($curl);

        if (curl_errno($curl)) {

            echo 'Curl error: ' . curl_error($curl)."<br/>";
           // $data = file_get_contents($url);

        }

        //关闭URL请求
        curl_close($curl);

        return $data;
    }


    /**
     * @param $url
     * @param array $postData
     * @param array $header
     * @return resource 并发处理
     */
    private static function getCurlObject($url,$postData=array(),$header=array()){
        $options = array();
        $url = trim($url);
        $options[CURLOPT_URL] = $url;
        $options[CURLOPT_TIMEOUT] = 3;
        $options[CURLOPT_RETURNTRANSFER] = true;
        foreach($header as $key=>$value){
            $options[$key] =$value;
        }
        if(!empty($postData) && is_array($postData)){
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = http_build_query($postData);
        }
        if(stripos($url,'https') === 0){
            $options[CURLOPT_SSL_VERIFYPEER] = false;
        }
        $ch = curl_init();
        curl_setopt_array($ch,$options);
        return $ch;
    }
    /**
     * [request description]
     * @param  [type] $chList
     * @return [type]
     */
    private static function request($chList){
        $downloader = curl_multi_init();
        // 将三个待请求对象放入下载器中
        foreach ($chList as $ch){
            curl_multi_add_handle($downloader,$ch);
        }
        $res = array();
        // 轮询
        do {
            while (($execrun = curl_multi_exec($downloader, $running)) == CURLM_CALL_MULTI_PERFORM);
            if ($execrun != CURLM_OK) {
                break;
            }
            // 一旦有一个请求完成，找出来，处理,因为curl底层是select，所以最大受限于1024
            while ($done = curl_multi_info_read($downloader)){
                // 从请求中获取信息、内容、错误
                // $info = curl_getinfo($done['handle']);
                $output = curl_multi_getcontent($done['handle']);
                // $error = curl_error($done['handle']);
                $res[] = $output;
                // 把请求已经完成了得 curl handle 删除
                curl_multi_remove_handle($downloader, $done['handle']);
            }
            // 当没有数据的时候进行堵塞，把 CPU 使用权交出来，避免上面 do 死循环空跑数据导致 CPU 100%
            if ($running) {
                $rel = curl_multi_select($downloader, 1);
                if($rel == -1){
                    usleep(1000);
                }
            }
            if($running == false){
                break;
            }
        }while(true);
        curl_multi_close($downloader);
        return $res;
    }
    /**
     * [get description]
     * @param  [type] $urlArr
     * @return [type]
     * curl并发处理
     */
    public static function getManyUrl($urlArr){
        $data = array();
        if (!empty($urlArr)) {
            $chList = array();
            foreach ($urlArr as $key => $url) {

                $chList[] = self::getCurlObject($url);
            }
            $data = self::request($chList);
        }
        return $data;
    }




}