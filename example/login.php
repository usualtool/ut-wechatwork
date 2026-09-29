<?php
use usualtool\Lib\Inc;
$do=$_GET["do"];
if($do=="out"):
    unset($_SESSION['work_openid']);
    Inc::GoUrl("","登出成功");
endif;
