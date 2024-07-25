<?php
if(isset($page_idx)) {
	include '/var/www/www/api/posting/editorial/get.php';
}
else {
	include '/var/www/www/api/posting/editorial/list/get.php';
}