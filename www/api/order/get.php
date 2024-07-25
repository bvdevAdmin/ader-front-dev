<?php
if(isset($order_idx) && is_numeric($order_idx)) {
	include '/var/www/www/api/mypage/order/get.php';
}
else {
	include '/var/www/www/api/mypage/order/list/get.php';
}