<?php
if(isset($no)) {
	include '/var/www/www/api/posting/collection/product/get.php';
}
else {
	include '/var/www/www/api/posting/collection/product/list/get.php';
}