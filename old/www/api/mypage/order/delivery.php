<?php
/*
 +=============================================================================
 | 
 | 배송회사 정보 조회
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2023.01.30
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

if (isset($country) && $member_idx > 0) {
	$select_delivery_company_sql = "
		SELECT
			IDX				AS IDX,
			COMPANY_NAME	AS COMPANY_NAME
		FROM
			DELIVERY_COMPANY DC
		WHERE
			DC.COUNTRY = '".$country."'
	";
	
	$db->query($select_delivery_company_sql);
	
	$delivery_info = array();
	
	foreach($db->fetch() as $data) {
		array_push(
			$delivery_info,
			array(
				'label'		=>$data['COMPANY_NAME'],
				'value'		=>$data['IDX']
			)
		);
	}
	
	$json_result['code'] = 200;
	$json_result['data'] = $delivery_info;
}

?>