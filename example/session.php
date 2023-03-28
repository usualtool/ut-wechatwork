<?php
use library\UsualToolInc\UTInc;
use usualtool\WechatWork\AuthToken;
use usualtool\WechatWork\Http;
if(isset($_SESSION['work_openid']) && !empty($_SESSION['work_openid'])):
    $openid=$_SESSION['work_openid'];
    $data=file_get_contents(APP_ROOT."/log/wechatwork/".$openid.".json");
else:
    $code=$_GET["code"];
    if(!empty($code)):
        $work=new AuthToken();
        $user=$work->GetUser($code);
        if(array_key_exists("open_userid",$user)):
            $openid=$user["open_userid"];
        else:
            $openid=$user["openid"];
        endif;
        $_SESSION["work_openid"]=$openid;
    else:
        if(UTInc::IsApp()):
            $config=Http::LoadConfig();
            $appid=$config["appid"];
            $appurl=$config["appurl"];
            UTInc::GoUrl('https://open.weixin.qq.com/connect/oauth2/authorize?appid={$appid}&redirect_uri={urlencode($appurl)}&response_type=code&scope=snsapi_base&state=usualtool#wechat_redirect','');
        else:
            UTInc::GoUrl('','本应用暂只支持在企业微信中使用');
        endif;
    endif;
endif;
