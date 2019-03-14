<?php
/**
 * User：李昊天
 * Tel: 15009392071
 * Email:1614369925@qq.com
 * Date: 2018/2/18
 * Time: 22:44
 */

namespace PHPMailer;
class SendEmail
{

    private  $Host; //smtp服务器
    private  $From; //发送者的邮件地址
    private  $FromName; //发送邮件的用户昵称
    private  $Username; //登录到邮箱的用户名
    private  $Password; //第三方登录的授权码，在邮箱里面设置
    private  $Port; //端口


    public function __construct($Host,$From,$FromName,$Username,$Password,$Port='465')
    {
        $this->Host = $Host;
        $this->From = $From;
        $this->FromName = $FromName;
        $this->Username = $Username;
        $this->Password = $Password;
        $this->Port = $Port;

    }

    /**
     * @desc 发送普通邮件
     * @param $title 邮件标题
     * @param $message 邮件正文
     * @param $emailAddress 邮件地址
     * @return bool|string 返回是否发送成功
     */
    public  function SendEmail($title=1,$message=1,$emailAddress='')
    {

        $mail = new PHPMailer();
        $mail->MailDebug = 2;
        //3.设置属性，告诉我们的服务器，谁跟谁发送邮件
        $mail -> IsSMTP();			//告诉服务器使用smtp协议发送
        $mail -> SMTPAuth = true;		//开启SMTP授权

        //如果不是qq邮箱，则不能用ssl
        if($this->Port == '465'){
            $mail->SMTPSecure = 'ssl';
        }else{
            $mail->SMTPSecure = 'tls';
        }

        $mail->Port = $this->Port;
        $mail -> Host = $this->Host;	//告诉我们的服务器使用163的smtp服务器发送
        $mail -> From = $this->From;	//发送者的邮件地址
        $mail -> FromName = $this->FromName;		//发送邮件的用户昵称
        $mail -> Username = $this->Username;	//登录到邮箱的用户名
        $mail -> Password = $this->Password;	    //第三方登录的授权码，在邮箱里面设置
        //编辑发送的邮件内容
        $mail -> IsHTML(true);		    //发送的内容使用html编写
        $mail -> CharSet = 'utf-8';		//设置发送内容的编码
        $mail -> Subject = $title;//设置邮件的标题
        $mail -> MsgHTML($message);	//发送的邮件内容主体
        $mail -> AddAddress($emailAddress);    //收人的邮件地址
        //调用send方法，执行发送
        $result = $mail -> Send();
        if($result){
            $data['code'] = '200';
            $data['msg'] = '';
           return $data;
        }else{
            $data['code'] = '100';
            $data['msg'] = $mail -> ErrorInfo;
        }

        return $data;
    }
}