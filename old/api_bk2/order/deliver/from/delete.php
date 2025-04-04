<?php
/*
 +=============================================================================
 | 
 | 주문정보 확인 - 주문자 정보 삭제
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

$from_idx		= $_POST['from_idx'];

if ($member_idx != null && $from_idx != null) {
	$sql = "DELETE FROM
				ORDER_FROM
			WHERE
				IDX = ".$from_idx;
		
	$db->query($sql);
}
?>