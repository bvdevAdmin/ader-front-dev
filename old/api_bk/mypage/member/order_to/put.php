<?php
/*
 +=============================================================================
 | 
 | 마이페이지 회원정보 - 배송지 수정
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
include_once("/var/www/www/api/common/common.php");
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
  $member_idx = $_SESSION['MEMBER_IDX'];
}

if ($country == null || $member_idx == 0) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	exit;
}

$order_to_idx = 0;
if (isset($_POST['order_to_idx'])) {
	$order_to_idx = $_POST['order_to_idx'];
}

$to_place_sql = null;
if (isset($_POST['to_place'])) {
	$to_place_sql = " TO_PLACE = '".$_POST['to_place']."', ";
}

$to_name_sql = null;
if (isset($_POST['to_name'])) {
	$to_name_sql = " TO_NAME = '".$_POST['to_name']."', ";
}

$to_mobile_sql = null;
if (isset($_POST['to_mobile'])) {
  $to_mobile_sql = " TO_MOBILE = '".$_POST['to_mobile']."', ";
}

$to_zipcode_sql = null;
if (isset($_POST['to_zipcode'])) {
  $to_zipcode_sql = " TO_ZIPCODE = '".$_POST['to_zipcode']."', ";
}

$set_addr = "";
if($country == 'KR'){
	$to_lot_addr_sql = null;
	if (isset($_POST['to_lot_addr'])) {
	  $to_lot_addr_sql = " TO_LOT_ADDR = '".$_POST['to_lot_addr']."', ";
	}
	
	$to_road_addr_sql = null;
	if (isset($_POST['to_road_addr'])) {
	  $to_road_addr_sql = " TO_ROAD_ADDR = '".$_POST['to_road_addr']."', ";
	}
	
	$to_detail_addr_sql = null;
	if (isset($_POST['to_detail_addr'])) {
	  $to_detail_addr_sql  = " TO_DETAIL_ADDR = '".$_POST['to_detail_addr']."' ";
	}
	
	$set_addr = $to_lot_addr_sql.$to_road_addr_sql.$to_detail_addr_sql;
} else {
	$to_country_code = null;
	if (isset($_POST['country_code'])) {
	  $to_country_code = " TO_COUNTRY_CODE = '".$_POST['country_code']."', ";
	}
	
	$to_province_idx = null;
	if (isset($_POST['province_idx'])) {
	  $to_province_idx = " TO_PROVINCE_IDX = '".$_POST['province_idx']."', ";
	}
	
	$to_city = null;
	if (isset($_POST['city'])) {
	  $to_city = " TO_CITY = '".$_POST['city']."', ";
	}
	
	$to_address = null;
	if (isset($_POST['address'])) {
	  $to_address = " TO_ADDRESS = '".$_POST['address']."' ";
	}
	
	$set_addr = $to_country_code.$to_province_idx.$to_city.$to_address;
}

$default_flg = null;
$default_flg_sql = null;

if (isset($_POST['default_flg'])) {
	$default_flg = $_POST['default_flg'];
	$default_flg_sql = " DEFAULT_FLG = ".$_POST['default_flg']." ";
	
	if($country == 'KR'){
		if (strlen($set_addr) > 0) {
			$default_flg_sql = " , ".$default_flg_sql;
		}
	}
	else if($country == 'EN' || $country == 'CN'){
		if (strlen($set_addr) > 0) {
			$default_flg_sql = " , ".$default_flg_sql;
		}
	}
}

if ($country != null && $member_idx > 0 && $order_to_idx > 0) {
	$db->begin_transaction();
    
	try {
		$update_order_to_sql = "
			UPDATE
				ORDER_TO
			SET
				".$to_place_sql."
				".$to_name_sql."
				".$to_mobile_sql."
				".$to_zipcode_sql."
				".$set_addr."
				".$default_flg_sql."
			WHERE
				IDX = ".$order_to_idx." AND
				COUNTRY = '".$country."' AND
				MEMBER_IDX = ".$member_idx."
		";
		$db->query($update_order_to_sql);
		  
		if ($default_flg == 'true') {
			$db_result = $db->affectedRows();
			  
			if ($db_result > 0) {
				$update_default_flg_sql = "
					UPDATE
						ORDER_TO
					SET
						DEFAULT_FLG = FALSE
					WHERE
						IDX != ".$order_to_idx." AND
						COUNTRY = '".$country."' AND
						MEMBER_IDX = ".$member_idx."
				";
		  
				$db->query($update_default_flg_sql);
			}
		}
		  
		$db->commit();
  } catch (mysqli_sql_exception $exception) {
	$db->rollback();
	print_r($exception);
	$json_result['code'] = 302;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0022', array());
	return $json_result;
  }
}
else{
	$json_result['code'] = 301;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0023', array());
	exit;
}
?>