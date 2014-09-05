<?php
/**
 * Created by PhpStorm.
 * User: panchangyun
 * Date: 14-9-5
 * Time: 上午10:24
 */
if ($_REQUEST['from'] == 'console') {
	echo "PHP Version: ";
	echo phpversion();
} else {
	phpinfo();
}



