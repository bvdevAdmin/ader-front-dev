<?php
/*
 +=============================================================================
 | 
 | 주문정보 확인 - 배송지 정보 개별 조회
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
$to_idx			= $_POST['to_idx'];

if ($member_idx != null && $from_idx != null) {
	$sql = "SELECT
				OT.IDX					AS TO_IDX,
				OT.TO_PLACE				AS TO_PLACE,
				OT.TO_NAME				AS TO_NAME,
				OT.TO_TEL				AS TO_TEL,
				OT.TO_MOBILE			AS TO_MOBILE,
				OT.TO_ZIPCODE			AS TO_ZIPCODE,
				OT.TO_LOT_ADDR			AS TO_LOT_ADDR,
				OT.TO_LOT_DETAIL_ADDR	AS TO_LOT_DETAIL_ADDR,
				OT.TO_ROAD_ADDR			AS TO_ROAD_ADDR,
				OT.TO_ROAD_DETAIL_ADDR	AS TO_ROAD_DETAIL_ADDR
			FROM
				ORDER_TO OT
			WHERE
				OT.IDX = ".$to_idx." AND 
				OT.MEMBER_IDX = ".$member_idx;
				
	$db->query($sql);
	
	foreach($db->fetch() as $data) {
		$json_result['data'][] = array(
			'to_idx'				=>$data['TO_IDX'],
			'to_place'				=>$data['TO_PLACE'],
			'to_name'				=>$data['TO_NAME'],
			'to_tel'				=>$data['TO_TEL'],
			'to_mobile'				=>$data['TO_MOBILE'],
			'to_zipcode'			=>$data['TO_ZIPCODE'],
			'to_lot_addr'			=>$data['TO_LOT_ADDR'],
			'to_detail_addr'		=>$data['TO_DETAIL_ADDR'],
			'to_road_addr'			=>$data['TO_ROAD_ADDR'],
			'to_road_detail_addr'	=>$data['TO_ROAD_DETAIL_ADDR']
		);
	}
}
?>