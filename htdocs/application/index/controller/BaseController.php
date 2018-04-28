<?php
namespace app\index\controller;
use think\Controller;

/**
 * 如果某个控制器必须用户登录才可以访问  
 * 请继承该控制器
 */
class BaseController extends Controller {

    protected $userid;
    protected $user;
    protected $isLogin=false;

    protected $config;
    protected $isWechat=false;
    protected $isMobile=false;

    public function _initialize(){
        $this->config=getSettings();
        $this->assign('config',$this->config);

        $this->checkLogin();


        $this->assign('isLogin',$this->isLogin);


        $this->checkPlatform();
    }

    public function seo($title='',$keys='',$desc=''){
        $sitename=$this->config['site-name'];
        if(empty($title)){
            $title .= $sitename;
        }elseif($title!=$sitename){
            $title .= ' - '.$sitename;
        }
        if(empty($keys)){
            $keys = $this->config['site-keywords'];
        }
        if(empty($desc)){
            $desc = $this->config['site-description'];
        }

        $this->assign('title',$title);
        $this->assign('keywords',$keys);
        $this->assign('description',$desc);
    }

    public function checkLogin(){
        $this->userid = session('userid');
        if(!empty($this->userid)){
            $this->user = M('member')->find($this->userid);
            /*$time=session('logintime');
            if($time != $this->user['logintime']){
                session('userid',null);
                $this->error('您的帐号已在其它地区登录！');
            }*/
            if(!empty($this->user)) {
                $this->isLogin=true;
                $this->assign('user', $this->user);
            }else{
                $this->userid=null;
                clearLogin(false);
                $this->error("登录失效",U('Login/index'));
            }
        }
    }

    public function checkPlatform(){
        $detected=session('detected');
        if(empty($detected)) {
            $useragent = $_SERVER['HTTP_USER_AGENT'];
            if (stripos($useragent, 'MicroMessenger') > 0) {
                $this->isWechat = true;
                $this->isMobile = true;
            }else {
                $mobileDetect = new \Extend\MobileDetect();
                $this->isMobile = $mobileDetect->isMobile();
            }
            session('detected',1);
            session('isWechat',$this->isWechat);
            session('isWechat',$this->isMobile);
        }else{
            $this->isWechat = session('isWechat');
            $this->isMobile = session('isMobile');
        }
        /**
        微信JSSDK
        详细用法参考：http://mp.weixin.qq.com/wiki/7/1c97470084b73f8e224fe6d9bab1625b.html
         */
        if($this->isWechat) {
            $jssdk = new \Extend\JSSDK(C('WX_APPID'), C('WX_APPSECRET'));
            $signPackage = $jssdk->GetSignPackage();
            $signPackage['logo'] = C('WX_DOMAIN') . 'Public/logo.png';
            $this->assign('signPackage', $signPackage);
        }
    }

    protected function sendEmail($user,$subject,$body,$attachment=array()){
        if(!is_array($user)){
            $split=explode('@',$user);
            $user=array(
                'username'=>$split[0],
                'email'=>$user
            );
        }

        vendor('PHPMailer.class#phpmailer'); //从PHPMailer目录导class.phpmailer.php类文件
        $mail             = new \PHPMailer(); //PHPMailer对象
        $mail->CharSet    = 'UTF-8'; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
        $mail->IsSMTP();  // 设定使用SMTP服务
        $mail->SMTPDebug  = 0;                     // 关闭SMTP调试功能
        // 1 = errors and messages
        // 2 = messages only
        $mail->SMTPAuth   = true;                  // 启用 SMTP 验证功能
        $mail->SMTPSecure = 'ssl';                 // 使用安全协议
        $mail->Host       = $this->config['mail_host'];  // SMTP 服务器
        $mail->Port       = $this->config['mail_port'];  // SMTP服务器的端口号
        $mail->Username   = $this->config['mail_user'];  // SMTP服务器用户名
        $mail->Password   = $this->config['mail_pass'];  // SMTP服务器密码
        $mail->SetFrom($this->config['mail_user'], $this->config['site-name']);
        /*$replyEmail       = $config['REPLY_EMAIL']?$config['REPLY_EMAIL']:$config['FROM_EMAIL'];
        $replyName        = $config['REPLY_NAME']?$config['REPLY_NAME']:$config['FROM_NAME'];
        $mail->AddReplyTo($replyEmail, $replyName);*/
        $mail->Subject    = $subject;
        $mail->MsgHTML($body);
        $mail->AddAddress($user['email'], $user['username']);
        if(!empty($attachment)){ // 添加附件
            foreach ($attachment as $file){
                is_file($file) && $mail->AddAttachment($file);
            }
        }
        return $mail->Send() ? true : $mail->ErrorInfo;
    }

}