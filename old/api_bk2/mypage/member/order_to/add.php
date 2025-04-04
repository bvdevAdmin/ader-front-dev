<?php
/*
 +=============================================================================
 | 
 | 마이페이지 회원정보 - 배송지 추가
 | -------
 |
 | 최초 작성	: 윤재은
 | 최초 작성일	: 2023.01.12
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/
include_once("/var/www/www/api/common.php");
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}
else{
	if(isset($_POST['country'])){
        $country = $_POST['country'];
	}
}
$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$to_place = null;
if (isset($_POST['to_place'])) {
	$to_place = $_POST['to_place'];
}

$to_name = null;
if (isset($_POST['to_name'])) {
	$to_name = $_POST['to_name'];
}

$to_mobile = null;
if (isset($_POST['to_mobile'])) {
	$to_mobile = $_POST['to_mobile'];
}

$to_zipcode = null;
if (isset($_POST['to_zipcode'])) {
	$to_zipcode = $_POST['to_zipcode'];
}

$to_lot_addr = null;
if (isset($_POST['to_lot_addr'])) {
	$to_lot_addr = $_POST['to_lot_addr'];
}

$to_road_addr = null;
if (isset($_POST['to_road_addr'])) {
	$to_road_addr = $_POST['to_road_addr'];
}

$to_detail_addr = null;
if (isset($_POST['to_detail_addr'])) {
	$to_detail_addr = $_POST['to_detail_addr'];
}
$country_code = null;
if (isset($_POST['country_code'])) {
	$country_code = $_POST['country_code'];
}
$province_idx = null;
if (isset($_POST['province_idx'])) {
	$province_idx = $_POST['province_idx'];
}
$city = null;
if (isset($_POST['city'])) {
	$city = $_POST['city'];
}
$address = null;
if (isset($_POST['address'])) {
	$address = $_POST['address'];
}
$default_flg = null;
if (isset($_POST['default_flg'])) {
	$default_flg = $_POST['default_flg'];
}

if ($country == null || $member_idx == 0) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	exit;
}

if ($member_idx > 0 && $to_zipcode != null) {
	$db->begin_transaction();
	
	try {
		if ($default_flg == 'true') {
			$update_default_flg_sql = "
				UPDATE
					ORDER_TO
				SET
					DEFAULT_FLG = FALSE
				WHERE
					COUNTRY = '".$country."' AND
					MEMBER_IDX = ".$member_idx."
			";
			
			$db->query($update_default_flg_sql);
		}
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
		";
		if($country == "KR"){
			$insert_order_to_sql .= "
				TO_LOT_ADDR,
				TO_ROAD_ADDR,
				TO_DETAIL_ADDR,
			";
		}
		else{
			$insert_order_to_sql .= "
				TO_COUNTRY_CODE,
				TO_PROVINCE_IDX,
				TO_CITY,
				TO_ADDRESS,
			";
		}
		$insert_order_to_sql .= "
				DEFAULT_FLG
			) VALUES (
				'".$country."',
				".$member_idx.",
				'".$to_place."',
				'".$to_name."',
				'".$to_mobile."',
				'".$to_zipcode."',";
		if($country == "KR"){
			$insert_order_to_sql .= "
				'".$to_lot_addr."',
				'".$to_road_addr."',
				'".$to_detail_addr."',
			";
		}
		else{
			$insert_order_to_sql .= "
				'".$country_code."',
				".$province_idx.",
				'".$city."',
				'".$address."',
			";
		}
		$insert_order_to_sql .= "
				".$default_flg."
			);
		";
		$db->query($insert_order_to_sql);	
		
		$db->commit();
	} catch(mysqli_sql_exception $exception) {
		print_r($exception);
		$db->rollback();

		$json_result['code'] = 302;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0050', array());
		
		echo json_encode($json_result);
		exit;
	}
} else {
	$json_result['code'] = 301;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0032', array());
	exit;
}

?>