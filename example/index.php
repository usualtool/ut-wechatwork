<?php
require dirname(__FILE__).'/'.'session.php';
echo"用户唯一标志：".$openid."<br/>缓存用户数据：<br/>";
print_r($data);
echo"<br/><a href='?m=service&p=login&do=out'>退出应用</a>";
