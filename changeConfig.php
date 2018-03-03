<?php
if($_REQUEST['page']=='vehicle'){
copy('configs/vehicle/config.inc.php', '_config/config.inc.php');
} else if ($_REQUEST['page']=='motorc'){
copy('configs/motorc/config.inc.php', '_config/config.inc.php');
} else if ($_REQUEST['page']=='boatrv'){
copy('configs/boatrv/config.inc.php', '_config/config.inc.php');
} else if ($_REQUEST['page']=='international'){
copy('configs/international/config.inc.php', '_config/config.inc.php');
} else if ($_REQUEST['page']=='moving'){
copy('configs/moving/config.inc.php', '_config/config.inc.php');
}

sleep(2);

header( 'Location: http://www.haulingdepot.com/admin/carriers.php' ) ;
?>