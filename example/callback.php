<?php
use usualtool\WechatWork\WorkMsg;
$signature = urldecode($_GET['msg_signature']);
$timestamp = urldecode($_GET['timestamp']);
$nonce = urldecode($_GET['nonce']);
$echostr = urldecode($_GET['echostr']);
$work=new WorkMsg();
if(!empty($echostr)):
    //数据回调
    $data=$work->Valid($signature,$timestamp,$nonce,$echostr);
else:
    //指令回调
    $post=file_get_contents("php://input");
    $data=$work->Ticket($signature,$timestamp,$nonce,$post);
endif;
echo$data;
