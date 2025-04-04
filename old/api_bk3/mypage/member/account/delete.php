<?php
/*
 +=============================================================================
 | 
 | 마이페이지 회원정보 - 회원 탈퇴
 | -------
 |
 | 최초 작성	: 이은형
 | 최초 작성일	: 2023.01.16
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

include_once("/var/www/www/api/common.php");

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

if (!isset($country) && $member_idx == 0) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	
	echo json_encode($json_result);
	exit;
}

if (isset($country) && $member_idx > 0) {
	$db->update(
		"MEMBER_".$country,
		array(
			'MEMBER_STATUS'	=>'DRP',
			'DROP_TYPE'		=>'NDP',
			'DROP_DATE'		=>NOW()
		),
		"IDX = ".$member_idx
	);
	
	$db_result = $db->affectedRows();
	
	if ($db_result > 0) {
		$json_result['code'] = 200;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_INF_0001', array());
		session_destroy();
		
		echo json_encode($json_result);
		exit;
	} else {
		$json_result['code'] = 301;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0103', array());
		
		echo json_encode($json_result);
		exit;
	}
}

?>