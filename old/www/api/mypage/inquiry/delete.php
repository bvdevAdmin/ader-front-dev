<?php
/*
 +=============================================================================
 | 
 | 1:1문의 삭제
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2023.02.26
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

if (!isset($country) && $member_idx == 0) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	
	echo json_encode($json_result);
	exit;
}

if(isset($board_idx)){
	$db->delete(
		"PAGE_BOARD",
		"
			IDX = ? AND
			COUNTRY = ? AND
			MEMBER_IDX = ?
		",
		array(
			$board_idx,
			$country,
			$member_idx
		)
	);
}

?>