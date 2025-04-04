<?php

if(!isset($tel) || !is_tel($tel)) {
	$code = 999;
	$msg = '형식에 맞지 않는 전화번호입니다.';
}

/* 입사처리를 위한 프로세스는 추후 추가하여 사용
elseif($db->count($_TABLE['ACCOUNT'],'ID=?',array($tel)) == 0) {
	$code = 999;
	$msg = '존재하지 않는 계정입니다.';
}
*/

else {

	// 임시. 
	$tel = str_replace('-','',$tel);
	if($db->count($_TABLE['ACCOUNT'],'TEL=?',array($tel)) == 0) {
		$db->insert($_TABLE['ACCOUNT'],array(
			'ID' => $tel,
			'TEL' => $tel
		));
	}
	
	// 계정 정보 가져옴
	$data = $db->get($_TABLE['ACCOUNT'],'TEL=?',array($tel));

	$json_result = array(
		'no' => intval($data[0]['IDX']),
		'name' => $data[0]['NAME'],
		'status' => $data[0]['STATUS']
	);
}