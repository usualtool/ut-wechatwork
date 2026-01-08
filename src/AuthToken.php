<?php
namespace usualtool\WechatWork;
use usualtool\WechatWork\Http;
use library\UsualToolInc\UTInc;
/*
  *第三方应用
**/
class AuthToken{
    public function __construct(){
        $config=Http::LoadConfig();
        $this->appid=$config["appid"];
        $this->secret=$config["secret"];
    }
    //获取TOKEN
    public function GetToken() {
        $file = file_get_contents(UTF_ROOT."/log/wechatwork.token.json");
        $result = json_decode($file,true);
        if(time()>$result['expires']):
            $data = array();
            $data['suite_access_token'] = $this->GetNewToken();
            $data['expires']=time()+5400;
            $json=json_encode($data);
            file_put_contents(UTF_ROOT."/log/wechatwork.token.json",$json);
            return $data['suite_access_token'];
        else:
            return $result['suite_access_token'];
        endif;   
    }
    //获取新的TOKEN
    public function GetNewToken(){
        $ticket=file_get_contents(UTF_ROOT."/log/wechatwork.ticket.log");
        $url = "https://qyapi.weixin.qq.com/cgi-bin/service/get_suite_token";
        $json=json_encode(array(
            "suite_id"=>$this->appid,
            "suite_secret"=>$this->secret,
            "suite_ticket"=>$ticket)
        );
        $data =  Http::PostData($url,$json);
        return $data['suite_access_token'];
    }
    //获取预授权码
    public function GetAuthCode(){
        $url = "https://qyapi.weixin.qq.com/cgi-bin/service/get_pre_auth_code?suite_access_token={$this->GetToken()}";
        $data =  Http::GetData($url);
        return $data['pre_auth_code'];
    }
    //获取企业永久授权码
    public function GetCorpCode($corpid,$code){
        $url = "https://qyapi.weixin.qq.com/cgi-bin/service/get_permanent_code?suite_access_token={$this->GetToken()}";
        $json='{
        	"auth_code":"'.$code.'"
        }';
        $data =  Http::PostData($url,$json);
        UTInc::MakeDir(UTF_ROOT."/log/wechatwork/com/");
        file_put_contents(UTF_ROOT."/log/wechatwork/com/".$corpid.".json",json_encode($data));
        return $data;
    }
    //据CODE令牌获取用户身份
    public function GetUser($code){
        $url="https://qyapi.weixin.qq.com/cgi-bin/service/auth/getuserinfo3rd?suite_access_token={$this->GetToken()}&code={$code}";
        $data=Http::GetData($url);
        if($data["errcode"]==0):
            if(array_key_exists("open_userid",$data)):
                $openid=$data["open_userid"];
            else:
                $openid=$data["openid"];
            endif;
            UTInc::MakeDir(UTF_ROOT."/log/wechatwork/");
            file_put_contents(UTF_ROOT."/log/wechatwork/".$openid.".json",json_encode($data));
        endif;
        return $data;
    }
}
