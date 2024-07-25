<?php

if(!isset($_SESSION['MEMBER_ID'])) {
	$code = 360;
}
else {
	$data = $db->get('MEMBER_KR','IDX=?',array($_SESSION['MEMBER_IDX']))[0];
	
	$json_result = array(
		'id' => $_SESSION['MEMBER_ID'],
		'email' => $_SESSION['MEMBER_ID'],
		'name' => $data['MEMBER_NAME'],
		'tel' => $data['TEL_MOBILE'],
		'birthday' => $data['MEMBER_BIRTH'],
		'mileage' => 1000,
		'voucher' => 10,
		'membership' => 'BLUE',
		'pg' => array(
			'key' => PG['KEY']
		)
	);
}