<?php

if($_SESSION[SS_HEAD.'ID'] != '') $id = $_SESSION[SS_HEAD.'ID'];
$id = str_replace(' ','',$id);
$id = strtolower($id);
$id = strip_tags($id);

$to_id = str_replace(' ','',$to_id);
$to_id = strtolower($to_id);
$to_id = strip_tags($to_id);

$where = 'IDX="'.$no.'" AND ID="'.$id.'"';

if($id == '') {
	$result = false;
	$code = 999;
	$msg = 'id가 없습니다.';
}
elseif($to_id == '') {
	$result = false;
	$code = 999;
	$msg = '받는 회원이 없습니다.';
}
elseif($id == $to_id) {
	$result = false;
	$code = 999;
	$msg = '양수인과 양도인은 달라야 합니다.';
}
elseif(!is_numeric($no)) {
	$result = false;
	$code = 999;
	$msg = '양도할 상품이 지정돼있지 않습니다.';
}
elseif(db_count($_TABLE['SERIAL'],$where) == 0) {
	$result = false;
	$code = 999;
	$msg = '정품인증 데이터가 존재하지 않습니다.';
}

if($result) {
	$result = db_update(
		$_TABLE['SERIAL'],
		'ID="'.$to_id.'"',
		$where
	);

	if($result) {
		$result = db_insert(
			$_TABLE['SERIAL_LOG'],
			'SERIAL_NO,ID,IP,CATEGORY',
			'"'.$no.'","'.$to_id.'","'.$IP.'","양수"'
		);
	}
	else {
		$code = 500;
	}
}
