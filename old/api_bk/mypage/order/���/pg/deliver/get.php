<?php
/*
 +=============================================================================
 | 
 | 마이페이지_주문조회화면
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

include_once("/var/www/www/api/mypage/order/common.php");

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$order_idx = 0;
if (isset($_POST['order_idx'])) {
	$order_idx = $_POST['order_idx'];
}

if ($country != null && $member_idx > 0 && $order_idx > 0) {
	$select_order_addr_sql = "
		SELECT
			OI.TO_PLACE				TO_PLACE,
			OI.TO_NAME				TO_NAME,
			OI.TO_MOBILE			TO_MOBILE,
			OI.TO_ZIPCODE			TO_ZIPCODE,
			IFNULL(
				OI.TO_ROAD_ADDR,
				OI.TO_LOT_ADDR
			)						AS TO_ADDR,
			OI.TO_DETAIL_ADDR		AS TO_DETAIL_ADDR,
			OI.ORDER_MEMO			AS ORDER_MEMO
		FROM
			ORDER_INFO OI
		WHERE
			OI.IDX = ".$order_idx." AND
			OI.COUNTRY = '".$country."' AND
			OI.MEMBER_IDX = ".$member_idx."
	";
	
	$db->query($select_order_addr_sql);
	
	foreach($db->fetch() as $addr_data) {
		$json_result['data'] = array(
			'to_place'			=>$addr_data['TO_PLACE'],
			'to_name'		=>$addr_data['TO_NAME'],
			'to_mobile'		=>$addr_data['TO_MOBILE'],
			'to_zipcode'		=>$addr_data['TO_ZIPCODE'],
			
			'to_addr'			=>$addr_data['TO_ADDR'],
			'to_detail_addr'	=>$addr_data['TO_DETAIL_ADDR'],
			'order_memo'		=>$addr_data['ORDER_MEMO']
		);
	}
} else {
	$json_result['code'] = 301;
	$json_result['msg'] = "로그인 정보가 없습니다. 로그인 후 다시 시도해주세요.";
}

?>