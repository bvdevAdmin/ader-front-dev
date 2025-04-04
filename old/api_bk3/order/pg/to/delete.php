<?php
/*
 +=============================================================================
 | 
 | 결제정보 입력화면 - 배송지 정보 조회
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.12.12
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$order_to_idx = 0;
if (isset($_POST['order_to_idx'])) {
	$order_to_idx = $_POST['order_to_idx'];
}

if (!isset($country) || $member_idx == 0) {
	$json_result['code'] = 401;
	$json_result['msg'] = "로그인 후 다시 시도해 주세요.";
	
	echo json_encode($json_result);
	exit;
}

if (isset($country) && $member_idx > 0 && isset($order_to_idx)) {
	$order_to_cnt = $db->count("ORDER_TO","IDX = ".$order_to_idx." AND COUNTRY = '".$country."' AND MEMBER_IDX = ".$member_idx);
	
	if ($order_to_idx > 0) {
		$db->delete(
			"ORDER_TO",
			"
				IDX = ? AND
				COUNTRY = ? AND
				MEMBER_IDX = ?
			",
			array(
				$order_to_idx,
				$country,
				$member_idx
			)
		);
	} else {
		$json_result['code'] = 401;
		$json_result['msg'] = "삭제하려는 배송지 정보가 존재하지 않습니다. 삭제하려는 배송지 정보를 확인해주세요.";
		
		echo json_encode($json_result);
		exit;
	}
}
?>