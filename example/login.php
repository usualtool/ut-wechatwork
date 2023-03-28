<?php
use library\UsualToolInc\UTInc;
$do=$_GET["do"];
if($do=="out"):
    unset($_SESSION['work_openid']);
    UTInc::GoUrl("","登出成功");
endif;