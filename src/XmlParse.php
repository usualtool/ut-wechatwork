<?php
namespace usualtool\WechatWork;
use usualtool\WechatWork\ErrorCode;
/**
 * xml解析，修正错误，支持PHP7+
 */
class XmlParse{
	public function extract($xmltext){
		try {
		    $xml=simplexml_load_string($xmltext);
		    $encrypt=$xml->Encrypt;
			return array(0, $encrypt);
		} catch (Exception $e) {
			print $e . "\n";
			return array(ErrorCode::$ParseXmlError, null);
		}
	}
	public function generate($encrypt, $signature, $timestamp, $nonce){
		$format = "<xml>
            <Encrypt><![CDATA[%s]]></Encrypt>
            <MsgSignature><![CDATA[%s]]></MsgSignature>
            <TimeStamp>%s</TimeStamp>
            <Nonce><![CDATA[%s]]></Nonce>
            </xml>";
		return sprintf($format, $encrypt, $signature, $timestamp, $nonce);
	}

}