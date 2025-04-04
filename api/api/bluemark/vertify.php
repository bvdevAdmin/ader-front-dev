<?php

$serial_code_arr = explode('-',strtoupper(trim($serial_code)));
if($serial_code_arr[0] == 'SSSSSSSSSSSS') $serial_code_arr[0] = 'TEST';
$where = 'BARCODE = "'.$serial_code_arr[0].'" AND SERIAL_CODE = "'.$serial_code_arr[1].'"';
$data = db_get($_TABLE['SERIAL'],$where);

if($_SESSION[SS_HEAD.'ID'] != '') {
	$id = $_SESSION[SS_HEAD.'ID'];
}
$id = strtolower(trim($id));

if($data['STATUS'] == "CONFIRM") {
	$code = 400;
	$msg = '이미 정품 인증되었습니다.';
}
elseif($id == '') {
	$code = 410;
	$msg = '회원 아이디를 입력해주세요.';
}
elseif(trim($serial_code) == '') {
	$code = 420;
	$msg = 'BLUEMARK를 입력해주세요.';
}
elseif($data['STATUS'] == 'N' || $data['STATUS'] == 'SUBMIT') {
	$query = '
		ID = "'.$id.'",
		NAME = "'.$name.'",
		MOBILE = "'.$mobile.'",
		EMAIL = "'.$email.'",
		IP = "'.$IP.'",
		STATUS = "CONFIRM",
		CONFIRM_DATE = NOW()
	';
	$result = db_update($_TABLE['SERIAL'],$query,$where);

	if(!$result) {
		$code = 500;
	}
	else {
		$serial_no = db_get($_TABLE['SERIAL'],$where,'IDX');
		$result = db_insert(
			$_TABLE['SERIAL_LOG'],
			'SERIAL_NO,ID,IP',
			'"'.$serial_no.'","'.$id.'","'.$IP.'"'
		);
		$goods_data = db_get($_TABLE['SHOP_GOODS'],'CODE="'.$serial_code_arr[0].'"');
		
		// 메일 발송
		if(is_email($id)) {
			$result = 
				send_mail(
					$_CONFIG['ADMIN_EMAIL'],
					$_CONFIG['ADMIN_NAME'],
					$id,
					$name,
					'[ADERerror] BLUE MARK CERTIFIED',
					get_mailform('bluemark',
						array(
							'NAME'=>$name,
							'ID'=>$id,
							'ITEM'=>$goods_data['NAME'],
							'DATE'=>date('Y-m-d H:i'),
							'NUMBER'=>$serial_code,
						)
					)
				);
		}
	}
}
else {
	$code = 401;
	$msg = '잘못된 시리얼 코드입니다.';
}
