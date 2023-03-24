<?php
namespace usualtool\WechatWork;
class Work{
      public function __construct(){
            global$config;
            $this->authcode=$config["UTCODE"];
            $this->authurl=$config["UTFURL"];
            $this->type="openapi";
      }
}
