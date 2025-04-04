<?php
/*
 +=============================================================================
 | 
 | 주문정보 확인 - 주문자 정보 개별 조회
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
	$sql = "SELECT
				OF.IDX			AS FROM_IDX,
				OF.FROM_NAME	AS FROM_NAME,
				OF.FROM_MOBILE	AS FROM_MOBILE,
				OF.FROM_EMAIL	AS FROM_EMAIL
			FROM
				ORDER_FROM OF
			WHERE
				OF.IDX = ".$from_idx." AND 
				OF.MEMBER_IDX = ".$member_idx;
				
	$db->query($sql);
	
	foreach($db->fetch() as $data) {
		$json_result['data'][] = array(
			'from_idx'		=>$data['FROM_IDX'],
			'from_name'		=>$data['FROM_NAME'],
			'from_mobile'	=>$data['FROM_MOBILE'],
			'from_email'	=>$data['FROM_EMAIL']
		);
	}
}
?>