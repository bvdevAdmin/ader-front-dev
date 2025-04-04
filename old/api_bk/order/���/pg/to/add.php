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

if ($member_idx == 0 || $country == null) {
	$json_result['code'] = 401;
	$json_result['msg'] = "로그인 후 다시 시도해 주세요.";
	
	return $json_result;
}

$to_place			= $_POST['to_place'];
$to_name			= $_POST['to_name'];
$to_mobile			= $_POST['to_mobile'];

$to_zipcode = null;
if(isset($_POST['to_zipcode'])){
	$to_zipcode		= $_POST['to_zipcode'];
}
$to_lot_addr = null;
if(isset($_POST['to_lot_addr'])){
	$to_lot_addr		= $_POST['to_lot_addr'];
}
$to_road_addr = null;
if(isset($_POST['to_road_addr'])){
	$to_road_addr		= $_POST['to_road_addr'];
}
$to_detail_addr = null;
if(isset($_POST['to_detail_addr'])){
	$to_detail_addr		= $_POST['to_detail_addr'];
}
$to_country_code = null;
if(isset($_POST['to_country_code'])){
	$to_country_code 	= $_POST['to_country_code'];
}
$to_province_idx = null;
if(isset($_POST['to_province_idx'])){
	$to_province_idx 	= $_POST['to_province_idx'];
}
$to_city = null;
if(isset($_POST['to_city'])){
	$to_city 			= $_POST['to_city'];
}
$to_address = null;
if(isset($_POST['to_address'])){
	$to_address 		= $_POST['to_address'];
}

$update_set_arr = array();
if($country == "KR"){
	$update_set_arr[0] = "
		TO_LOT_ADDR,
		TO_ROAD_ADDR,
		TO_DETAIL_ADDR
	";

	$update_set_arr[1] = "
		'".$to_lot_addr."',
		'".$to_road_addr."',
		'".$to_detail_addr."'
	";
}
else if($country == 'EN' || $country == "CN"){
	$update_set_arr[0] = "
		TO_COUNTRY_CODE,
		TO_PROVINCE_IDX,
		TO_CITY,
		TO_ADDRESS
	";
	$update_set_arr[1] = "
		'".$to_country_code."',
		".$to_province_idx.",
		'".$to_city."',
		'".$to_address."'
	";
}
if ($member_idx > 0 && $country != null) {
	$insert_order_to_sql = "
		INSERT INTO
			ORDER_TO
		(
			COUNTRY,
			MEMBER_IDX,
			TO_PLACE,
			TO_NAME,
			TO_MOBILE,
			TO_ZIPCODE,
			".$update_set_arr[0]."
		) VALUES (
			'".$country."',
			".$member_idx.",
			'".$to_place."',
			'".$to_name."',
			'".$to_mobile."',
			'".$to_zipcode."',
			".$update_set_arr[1]."
		)
	";
	
	$db->query($insert_order_to_sql);
}
?>