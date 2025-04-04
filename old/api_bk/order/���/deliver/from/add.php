<?php
/*
 +=============================================================================
 | 
 | 주문정보 확인 - 주문자 정보 추가
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.10.17
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

$member_idx		= $_SESSION[SS_HEAD.'MEMBER_IDX'];

$from_name		= $_POST['from_name'];
$from_tel		= $_POST['from_tel'];
$from_email		= $_POST['from_email'];

if ($from_name != null && $from_tel != null && $from_email != null) {
	$from_cnt => $db->count("ORDER_FROM"," MEMBER_IDX = ".$member_idx." AND FROM_NAME = ".$from_name);
	
	if ($from_cnt > 0) {
		$code = 402;
		$msg = "이미 등록된 주문자 정보입니다.";
		exit;
	}
	
	$sql = "INSERT INTO
					ORDER_FROM
				(
					MEMBER_IDX,
					FROM_NAME,
					FROM_MOBILE,
					FROM_EMAIL
				)
				VALUES
				(
					".$member_idx.",
					'".$from_name."',
					'".$from_mobile."',
					'".$from_email."'
				)";
	
	$db->query($sql);
}
?>