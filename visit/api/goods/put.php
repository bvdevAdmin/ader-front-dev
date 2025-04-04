<?php
if(!isset($title) || trim($title) == '') {
	$code = 999;
	$msg = '음료명을 입력해주세요.';
}
else {
	if(!$db->insert($_TABLE['GOODS'],array(
		'TITLE' => $title
	))) {
		$code = 500;
	}
	else {
		
	}
}