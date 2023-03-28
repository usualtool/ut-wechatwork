<?php
namespace usualtool\WechatWork;
class Http{
    public static function LoadConfig(){
        $json=file_get_contents(dirname(__FILE__)."/config.json");
        $data=json_decode($json,true);
        return $data;
    }
    public static function GetData($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($ch);
        curl_close($ch);
        return json_decode($output,true);
    }
    public static function PostData($url,$data=''){         
        $ch = curl_init();  
        curl_setopt($ch, CURLOPT_URL, $url);  
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);   
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);  
        if(!empty($data)){  
            curl_setopt($ch, CURLOPT_POST, TRUE);  
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);  
        }  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
        $output = curl_exec($ch);  
        curl_close($ch);  
        if(is_null(json_decode($output))){
            return "data:image/png;base64,".base64_encode($output);
        }else{
            return json_decode($output,true);
        }        
    }    
    //给URL地址追加参数
    public static function AppendParamter($url,$key,$value){  
        return strrpos($url,"?",0) > -1 ? "$url&$key=$value" : "$url?$key=$value";
    }
    //生成指定长度的随机字符串
    public static function CreateNonceStr($length = 16) {
      $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
      $str = "";
      for ($i = 0; $i < $length; $i++) {
        $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
      }
      return $str;
    }
}