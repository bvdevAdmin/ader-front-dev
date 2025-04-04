<?php
/*
 +=============================================================================
 | 
 | 마이페이지 회원정보 - 현재 비밀번호 확인
 | -------
 |
 | 최초 작성	: 윤재은
 | 최초 작성일	: 2023.01.12
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

include_once(dir_f_api."/common.php");

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

if (!isset($country) || $member_idx == 0) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	
	echo json_encode($json_result);
	exit;
}

if (isset($country) && $member_idx > 0 && isset($member_pw)) {
	$member_cnt = $db->count("MEMBER_".$country, "IDX = ".$member_idx." AND MEMBER_PW = '".md5($member_pw)."'");
	
	$json_result = array();
	
	if ($member_cnt > 0) {
		$json_result['code'] = 200;
	} else {
		$json_result['code'] = 301;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0040', array());
	}
	
	echo json_encode($json_result);
	exit;
}
?>