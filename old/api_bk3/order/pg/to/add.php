<?php
/*
 +=============================================================================
 | 
 | 결제정보 입력화면 - 배송지 정보 개별 조회
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

if (!isset($country) || $member_idx == 0) {
	$json_result['code'] = 401;
	$json_result['msg'] = "로그인 후 다시 시도해 주세요.";
	
	echo json_encode($json_result);
	exit;
} else {
	if ($country == "KR") {
		$db->insert(
			"ORDER_TO",
			array(
				'COUNTRY'			=>$country,
				'MEMBER_IDX'		=>$member_idx,
				'TO_PLACE'			=>$to_place,
				'TO_NAME'			=>$to_name,
				'TO_MOBILE'			=>$to_mobile,
				'TO_ZIPCODE'		=>$to_zipcode,
				
				'TO_LOT_ADDR'		=>$to_lot_addr,
				'TO_ROAD_ADDR'		=>$to_road_addr,
				'TO_DETAIL_ADDR'	=>$to_detail_addr
			)
		);
	} else {
		$db->insert(
			"ORDER_TO",
			array(
				'COUNTRY'			=>$country,
				'MEMBER_IDX'		=>$member_idx,
				'TO_PLACE'			=>$to_place,
				'TO_NAME'			=>$to_name,
				'TO_MOBILE'			=>$to_mobile,
				'TO_ZIPCODE'		=>$to_zipcode,
				
				'TO_COUNTRY_CODE'	=>$to_country_code,
				'TO_PROVINCE_IDX'	=>$to_province_idx,
				'TO_CITY'			=>$to_city,
				'TO_ADDRESS'		=>$to_address
			)
		);
	}
}

?>