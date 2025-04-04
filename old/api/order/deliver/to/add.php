<?php
/*
 +=============================================================================
 | 
 | 주문정보 확인 - 배송지 정보 추가
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

$to_place				= $_POST['to_place'];
$to_name				= $_POST['to_name'];
$to_tel					= $_POST['to_tel'];
$to_mobile				= $_POST['to_mobile'];
$to_zipcode				= $_POST['to_zipcode'];
$to_lot_addr			= $_POST['to_lot_addr'];
$to_lot_detail_addr		= $_POST['to_lot_detail_addr'];
$to_road_addr			= $_POST['to_road_addr'];
$to_road_detail_addr	= $_POST['to_road_detail_addr'];

if ($to_place != null || $to_name != null || $to_zipcode != null || $to_lot_addr != null || $to_road_addr != null) {
	$to_cnt => $db->count("ORDER_TO"," MEMBER_IDX = ".$member_idx." AND TO_PLACE = ".$to_place." AND TO_NAME = ".$to_name." AND TO_ZIPCODE = ".$to_zipcode." AND TO_LOT_ADDR = ".$to_lot_addr." AND TO_ROAD_ADDR = ".$to_road_addr);
	
	if ($to_cnt > 0) {
		$code = 402;
		$msg = "이미 등록된 배송지 정보입니다.";
		exit;
	}
	
	$sql = "INSERT INTO
					ORDER_TO
				(
					MEMBER_IDX,
					TO_PLACE,
					TO_NAME,
					TO_TEL,
					TO_MOBILE,
					TO_ZIPCODE,
					TO_LOT_ADDR,
					TO_LOT_DETAIL_ADDR,
					TO_ROAD_ADDR,
					TO_ROAD_DETAIL_ADDR
				)
				VALUES
				(
					".$member_idx.",
					'".$to_place."',
					'".$to_name."',
					'".$to_tel."',
					'".$to_mobile."',
					'".$to_zipcode."',
					'".$to_lot_addr."',
					'".$to_lot_detail_addr."',
					'".$to_road_addr."',
					'".$to_road_detail_addr."'
				)";
	
	$db->query($sql);
}
?>