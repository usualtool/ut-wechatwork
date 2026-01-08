<?php
namespace usualtool\WechatWork;
use usualtool\WechatWork\Http;
/*
  *企业微信自建应用
**/
class Corp{
    private $apiurl;
    private $corpid;
    private $corpsecret;
    public function __construct($corpid,$corpsecret){
        if(!empty($corpid) && !empty($corpsecret)):
            $this->corpid=$corpid;
            $this->secret=$corpsecret;
            $this->apiurl="https://qyapi.weixin.qq.com/cgi-bin";
        else:
            echo "企业ID与应用密钥不能为空";
        endif;
    }
    /*
      *获取access_token
    **/
    public function GetToken(){
        $content = file_get_contents(UTF_ROOT."/log/wechatwork.".$this->corpid.".json");
        $result = json_decode($content,true);
        if(empty($result["access_token"]) || time() > $result["expires"]):
            $newToken = $this->GetNewToken();
            $data = array();
            $data['access_token'] = $this->GetNewToken();
            $data['expires']=time()+5400;
            $jsonstr = json_encode($data);
            file_put_contents(UTF_ROOT."/log/wechatwork.".$this->corpid.".json",$jsonstr);
            return $data["access_token"];
        else:
            return $result["access_token"];
        endif;
    }
    public function GetNewToken(){
        $result=Http::GetData($this->apiurl."/gettoken?corpid={$this->corpid}&corpsecret={$this->secret}");
        return $result['access_token'];
    }
    /*
      *企业成员
    **/
    /*
      *获取企业成员ID列表
    **/
    public function GetParter(){
        $json=json_encode(["limit"=>10000]);
        $data=Http::PostData($this->apiurl."/user/list_id?access_token={$this->GetToken()}",$json);
        return $data;
    }
    /*
      *以手机号获取成员ID
    **/
    public function ToParterId($telphone){
        $json=json_encode(["mobile"=>$telphone]);
        $data=Http::PostData($this->apiurl."/user/getuserid?access_token={$this->GetToken()}",$json);
        return $data;
    }
    /*
      *读取企业成员详情
    **/
    public function GetParterDetail($userid){
        $data=Http::GetData($this->apiurl."/user/get?access_token={$this->GetToken()}&userid=".$userid);
        return $data;
    }
    /*
      *删除成员
    **/
    public function DelParter($userid){
        $data=Http::GetData($this->apiurl."/user/delete?access_token={$this->GetToken()}&userid=".$userid);
        return $data;
    }
    /*
      *企业微信客户联系
    **/
    /*
      *获取具有客户联系功能的员工
    **/
    public function GetStaff($uid){
        $data=Http::GetData($this->apiurl."/externalcontact/get_follow_user_list?access_token={$this->GetToken()}");
        return $data;
    }
    /*
      *获取员工名下客户
    **/
    public function GetCustomer($uid){
        $data=Http::GetData($this->apiurl."/externalcontact/list?access_token={$this->GetToken()}&userid=".$uid);
        return $data;
    }
    /*
      *获取客户详情
    **/
    public function GetCustomerDetail($userid){
        $data=Http::GetData($this->apiurl."/externalcontact/get?access_token={$this->GetToken()}&external_userid=".$userid);
        return $data;
    }
    /*
      *获取企业客户标签
    **/
    public function GetTag(){
        $data=Http::GetData($this->apiurl."/externalcontact/get_corp_tag_list?access_token={$this->GetToken()}");
        return $data;
    }
    /*
      *获取客户群
    **/
    public function GetGroup(){
        $json=json_encode(["limit"=>500]);
        $list=Http::PostData($this->apiurl."/externalcontact/groupchat/list?access_token={$this->GetToken()}",$json);
        $group=$list["group_chat_list"];
        return $group;
    }
    public function GetGroupList($group){
        $arr=[];
        foreach($group as $item):
            $chatid=$item["chat_id"];
            $detail=$this->GetGroupDetail($chatid,0);
            if($detail["errcode"]===0):
                $arr[]=$detail["group_chat"];
            else:
                echo "获取群{$chatid}详情失败";
            endif;
            usleep(200000);
        endforeach;
        return $arr;
    }
    public function GetGroupDetail($chat_id,$need_name=0){
        $data=json_encode(["chat_id"=>$chat_id,"need_name"=>$need_name]);
        $url=$this->apiurl."/externalcontact/groupchat/get?access_token=".$this->GetToken();
        $result=Http::PostData($url,$data);
        return $result;
    }
    /*
      *创建对群的群发
      msgtype类型：text,link,miniprogram,video,file
      attach格式：
      text: "纯文本内容"
      link: {"title": "消息标题","picurl": "","desc": "消息描述","url": ""}
      miniprogram: {"title": "消息标题","pic_media_id": "MEDIA_ID","appid": "wx","page": "/path"}
      video: {"media_id": "MEDIA_ID"}
      file: {"media_id": "MEDIA_ID"}
    **/
    public function AddSendGroup($groupid,$sender,$msgtype,$attach){
        $data=[
            "chat_type"=>"group",
            "chat_id_list"=>$groupid,
            "sender"=>$sender
        ];
        if($msgtype=="text"):
            $data["text"]=array(
                "content"=>$attach
            );
        else:
            $data["attachments"]=array(
                "msgtype"=>$msgtype,
                "".$msgtype.""=>$attach
            );
        endif;
        $json=json_encode($data);
        echo$json;
        $url=$this->apiurl."/externalcontact/add_msg_template?access_token=".$this->GetToken();
        $result=Http::PostData($url,$json);
        return $result;
    }
    /*
      *创建对客户的群发
      tag格式：array("tag_list"=>["ete19MT1231","ete19MT12278"],"tag_list"=>["ete19MT2235"])
      同组标签之间按或关系进行筛选，不同组标签按且关系筛选
    **/
    public function AddSendUser($tag,$sender,$msgtype,$attach){
        $data=[
            "chat_type"=>"single",
            "tag_filter"=>array(
                "group_list"=>$tag
            ),
            "sender"=>$sender
        ];
        if($msgtype="text"):
            $data["text"]=array(
                "content"=>$attach
            );
        else:
            $data["attachments"]=array(
                "msgtype"=>$msgtype,
                "".$msgtype.""=>$attach
            );
        endif;
        $json=json_encode($data);
        $url=$this->apiurl."/externalcontact/add_msg_template?access_token=".$this->GetToken();
        $result=Http::PostData($url,$json);
        return $result;
    }
    /*
      *停止群发
    **/
    public function StopSend($msgid){
        $data=json_encode(["msgid"=>$msgid]);
        $url=$this->apiurl."/externalcontact/cancel_groupmsg_send?access_token=".$this->GetToken();
        $result=Http::PostData($url,$data);
        return $result;
    }
    /*
      *opengid转群id
    **/
    public function ToGroupId($opengid){
        $data=json_encode(["opengid"=>$msgid]);
        $url=$this->apiurl."/externalcontact/opengid_to_chatid?access_token=".$this->GetToken();
        $result=Http::PostData($url,$data);
        return $result;
    }
}
