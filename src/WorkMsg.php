<?php
namespace usualtool\WechatWork;
use usualtool\WechatWork\Sha;
use usualtool\WechatWork\XmlParse;
use usualtool\WechatWork\Pkcs7Encoder;
use usualtool\WechatWork\Http;
use usualtool\WechatWork\ErrorCode;
class WorkMsg{
	public function __construct(){
        $config=Http::LoadConfig();
		$this->appid = $config["appid"];
		$this->token = $config["token"];
		$this->aeskey = $config["aeskey"];
	}
    //验证消息
	public function Valid($signature, $timestamp, $nonce, $data){
	    //验证签名
		if(strlen($this->aeskey) != 43):return ErrorCode::$IllegalAesKey;endif;
		$sha1 = new Sha;
		$array = $sha1->getSHA1($this->token, $timestamp, $nonce, $data);
		$ret = $array[0];
		if ($ret != 0):return $ret;endif;
		$sign = $array[1];
		if ($sign != $signature):
			return ErrorCode::$ValidateSignatureError;
		endif;
		//解析加密数据返回明文
		$prpcrypt = new PrpCrypt($this->aeskey);
		$result = $prpcrypt->decrypt($data, $this->appid);
		if($result[0]!=0):
			return $result[0];
		else:
		    return $result[1];
		endif;
	}
	//验证接收ticket
	public function Ticket($signature, $timestamp, $nonce, $data){
	    $result=$this->DecryptMsg($signature,$timestamp,$nonce,$data,$msg);
	    if($result==0):
	        $xml=simplexml_load_string($msg);
	        $ticket=$xml->SuiteTicket;
	        file_put_contents(APP_ROOT."/log/wechatwork.ticket.log",$ticket);
	        return "success";
	    else:
	        return $result;
	    endif;
	}
	public function EncryptMsg($sReplyMsg, $sTimeStamp, $sNonce, &$sEncryptMsg){
		$prpcrypt = new PrpCrypt($this->aeskey);
		$array = $prpcrypt->encrypt($sReplyMsg, $this->appid);
		$ret = $array[0];
		if ($ret != 0) {
			return $ret;
		}
		if ($sTimeStamp == null) {
			$sTimeStamp = time();
		}
		$encrypt = $array[1];
		//生成安全签名
		$sha1 = new Sha;
		$array = $sha1->getSHA1($this->token, $sTimeStamp, $sNonce, $encrypt);
		$ret = $array[0];
		if ($ret != 0) {
			return $ret;
		}
		$signature = $array[1];
		//生成发送的xml
		$xmlparse = new XmlParse;
		$sEncryptMsg = $xmlparse->generate($encrypt, $signature, $sTimeStamp, $sNonce);
		return ErrorCode::$OK;
	}
	public function DecryptMsg($sMsgSignature, $sTimeStamp = null, $sNonce, $sPostData, &$msg){
		if (strlen($this->aeskey) != 43) {
			return ErrorCode::$IllegalAesKey;
		}
		//提取密文
		$xmlparse = new XmlParse;
		$array = $xmlparse->extract($sPostData);
		$ret = $array[0];
		if ($ret != 0) {
			return $ret;
		}
		if ($sTimeStamp == null) {
			$sTimeStamp = time();
		}
		$encrypt = $array[1];
		//验证安全签名
		$sha1 = new Sha;
		$array = $sha1->getSHA1($this->token, $sTimeStamp, $sNonce, $encrypt);
		$ret = $array[0];
		if ($ret != 0) {
			return $ret;
		}
		$signature = $array[1];
		if ($signature != $sMsgSignature) {
			return ErrorCode::$ValidateSignatureError;
		}
		$prpcrypt = new PrpCrypt($this->aeskey);
		$result = $prpcrypt->decrypt($encrypt, $this->appid);
		if ($result[0] != 0) {
			return $result[0];
		}
		$msg = $result[1];
		return ErrorCode::$OK;
	}

}
