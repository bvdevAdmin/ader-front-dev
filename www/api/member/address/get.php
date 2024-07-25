<?php
/*
 +=============================================================================
 | 
 | 마이페이지 회원정보 - 배송지 목록 조회 // '/var/www/www/api/mypage/member/order_to/list/get.php'
 | -------
 |
 | 최초 작성	: 윤재은
 | 최초 작성일	: 2023.01.11
 | 최종 수정    : 양한빈
 | 최종 수정일	: 2024.05.28
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
  $member_idx = $_SESSION['MEMBER_IDX'];
}

if (!isset($country) || $member_idx == 0) {
	$code = 401;
	$msg = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
}

else {
	$select_order_to_sql = "
		SELECT
			OT.IDX				AS ORDER_TO_IDX,
			OT.TO_PLACE			AS TO_PLACE,
			OT.TO_NAME			AS TO_NAME,
			OT.TO_MOBILE		AS TO_MOBILE,
			OT.TO_ZIPCODE		AS TO_ZIPCODE,
			OT.TO_LOT_ADDR		AS TO_LOT_ADDR,
			OT.TO_ROAD_ADDR		AS TO_ROAD_ADDR,
			OT.TO_DETAIL_ADDR	AS TO_DETAIL_ADDR,
			IFNULL(CI.COUNTRY_NAME,'')
								AS TO_COUNTRY_NAME,
			IFNULL(PI.PROVINCE_NAME,'')	
								AS TO_PROVINCE_NAME,
			OT.TO_CITY			AS TO_CITY,
			OT.TO_ADDRESS		AS TO_ADDRESS,
			OT.DEFAULT_FLG		AS DEFAULT_FLG
		FROM
			ORDER_TO OT LEFT JOIN
			COUNTRY_INFO CI
		ON
			OT.TO_COUNTRY_CODE = CI.COUNTRY_CODE LEFT JOIN
			PROVINCE_INFO PI
		ON
			OT.TO_PROVINCE_IDX = PI.IDX
		WHERE
			OT.COUNTRY = '".$country."' AND
			OT.MEMBER_IDX = ".$member_idx."
		ORDER BY
			OT.IDX 		DESC
	";

	$db->query($select_order_to_sql);

	foreach($db->fetch() as $data) {
		$json_result['data'][] = array(
			'order_to_idx'		=>$data['ORDER_TO_IDX'],
			'to_place'			=>$data['TO_PLACE'],
			'to_name'			=>$data['TO_NAME'],
			'to_mobile'			=>$data['TO_MOBILE'],
			'to_zipcode'		=>$data['TO_ZIPCODE'],
			'to_lot_addr'		=>$data['TO_LOT_ADDR'],
			'to_road_addr'		=>$data['TO_ROAD_ADDR'],
			'to_detail_addr'	=>$data['TO_DETAIL_ADDR'],
			'to_country_name' 	=>$data['TO_COUNTRY_NAME'],
			'to_province_name' 	=>$data['TO_PROVINCE_NAME'],
			'to_city' 			=>$data['TO_CITY'],
			'to_address' 		=>$data['TO_ADDRESS'],
			'default_flg'		=>$data['DEFAULT_FLG']
		);
	}
}
