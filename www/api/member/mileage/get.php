<?php
/*
 +=============================================================================
 | 
 | 마이페이지 마일리지 현황정보 취득 // member/mileage/get
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2023.01.11
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

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

if (isset($country) && $member_idx > 0) {
	$select_mileage_info_sql = "
		SELECT 
			IFNULL(
				(
					SELECT 
						S_MI.MILEAGE_BALANCE 
					FROM 
						MILEAGE_INFO  S_MI
					WHERE 
						COUNTRY = '".$country."' AND
						MEMBER_IDX = ".$member_idx."
					ORDER BY 
						IDX DESC 
					LIMIT 0,1
				),0
			)								AS MILEAGE_BALANCE,
			SUM(MI.MILEAGE_USABLE_DEC)		AS REFUND_SCHEDULED,
			SUM(MI.MILEAGE_UNUSABLE)		AS USED_MILEAGE
		FROM
			MILEAGE_INFO MI
		WHERE
			MI.COUNTRY = '".$country."' AND
			MI.MEMBER_IDX = ".$member_idx."
	";

	$db->query($select_mileage_info_sql);

	foreach($db->fetch() as $data){
		$json_result['data'] = array(
			'mileage_balance'		=> number_format($data['MILEAGE_BALANCE']),
			'refund_scheduled'		=> number_format($data['REFUND_SCHEDULED']),
			'used_mileage'			=> number_format($data['USED_MILEAGE']),
		);
	}
}

