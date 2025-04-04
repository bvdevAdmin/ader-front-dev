<?php
/*
 +=============================================================================
 | 
 | A/S신청 리스트 조회
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2023.04.23
 | 최종 수정일	: 
 | 버전		: 1.1
 | 설명		: 
 | 
 +=============================================================================
*/
include_once("/var/www/www/api/common.php");
$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$member_id = null;
if (isset($_SESSION['MEMBER_ID'])) {
	$member_id = $_SESSION['MEMBER_ID'];
}

$update_type = null;
if (isset($_POST['update_type'])) {
	$update_type = $_POST['update_type'];
}

$as_idx = 0;
if (isset($_POST['as_idx'])) {
	$as_idx = $_POST['as_idx'];
}

$as_code = null;
if (isset($_POST['as_code'])) {
	$as_code = $_POST['as_code'];
}

$housing_company = 0;
if (isset($_POST['housing_company'])) {
	$housing_company = $_POST['housing_company'];
}

$housing_num = null;
if (isset($_POST['housing_num'])) {
	$housing_num = $_POST['housing_num'];
}

$order_to_idx = 0;
if (isset($_POST['order_to_idx'])) {
	$order_to_idx = $_POST['order_to_idx'];
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

$to_detail_addr = null;;
if (isset($_POST['to_detail_addr'])) {
	$to_detail_addr = $_POST['to_detail_addr'];
}

$to_country_code = null;;
if (isset($_POST['to_country_code'])) {
	$to_country_code = $_POST['to_country_code'];
}

$to_province_idx = null;;
if (isset($_POST['to_province_idx'])) {
	$to_province_idx = $_POST['to_province_idx'];
}

$to_city = null;;
if (isset($_POST['to_city'])) {
	$to_city = $_POST['to_city'];
}

$to_address = null;;
if (isset($_POST['to_address'])) {
	$to_address = $_POST['to_address'];
}

$order_memo = null;
if (isset($_POST['order_memo'])) {
	$order_memo = $_POST['order_memo'];
}

if ($as_idx > 0 && $update_type == "HOS") {
	$as_cnt = $db->count("MEMBER_AS","IDX = ".$as_idx." AND COUNTRY = '".$country."' AND MEMBER_IDX = ".$member_idx."");
	if ($as_cnt > 0) {
		$update_member_as_sql = "
			UPDATE
				MEMBER_AS
			SET
				HOUSING_COMPANY = '".$housing_company."',
				HOUSING_NUM = '".$housing_num."',
				HOUSING_START_DATE = NOW(),
				
				UPDATE_DATE = NOW(),
				UPDATER = '".$member_id."'
			WHERE
				IDX = ".$as_idx."
		";
		
		$db->query($update_member_as_sql);
		
		$select_member_as_sql = "
			SELECT
				MA.HOUSING_COMPANY		AS HOUSING_COMPANY,
				MA.HOUSING_NUM			AS HOUSING_NUM,
				MA.HOUSING_START_DATE	AS HOUSING_START_DATE
			FROM
				MEMBER_AS MA
			WHERE
				MA.IDX = ".$as_idx."
		";
		
		$db->query($select_member_as_sql);
		
		foreach($db->fetch() as $as_data) {
			$json_result['data'] = array(
				'housing_company'		=>$as_data['HOUSING_COMPANY'],
				'housing_num'			=>$as_data['HOUSING_NUM'],
				'housing_start_date'	=>$as_data['HOUSING_START_DATE']
			);
		}
	} else {
		$json_result['code'] = 301;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0006', array());
	}
}

if ($as_idx > 0 && $update_type == "APG") {
	$as_cnt = $db->count("MEMBER_AS","IDX = ".$as_idx." AND COUNTRY = '".$country."' AND MEMBER_IDX = ".$member_idx." AND AS_STATUS = '".$update_type."'");
	if ($as_cnt > 0) {
		if ($order_to_idx > 0) {
			$select_order_to_sql = "
				SELECT
					OT.TO_PLACE			AS TO_PLACE,
					OT.TO_NAME			AS TO_NAME,
					OT.TO_MOBILE		AS TO_MOBILE,
					OT.TO_ZIPCODE		AS TO_ZIPCODE,
					OT.TO_LOT_ADDR		AS TO_LOT_ADDR,
					OT.TO_ROAD_ADDR		AS TO_ROAD_ADDR,
					OT.TO_DETAIL_ADDR	AS TO_DETAIL_ADDR,
					OT.TO_COUNTRY_CODE	AS TO_COUNTRY_CODE,
					OT.TO_PROVINCE_IDX	AS TO_PROVINCE_IDX,
					OT.TO_CITY			AS TO_CITY,
					OT.TO_ADDRESS		AS TO_ADDRESS
				FROM
					ORDER_TO OT
				WHERE
					OT.IDX = ".$order_to_idx."
			";
			
			$db->query($select_order_to_sql);
			
			foreach($db->fetch() as $order_to_data) {
				$to_place			= $order_to_data['TO_PLACE'];
				$to_name			= $order_to_data['TO_NAME'];
				$to_mobile			= $order_to_data['TO_MOBILE'];
				$to_zipcode			= $order_to_data['TO_ZIPCODE'];
				$to_lot_addr		= $order_to_data['TO_LOT_ADDR'];
				$to_road_addr		= $order_to_data['TO_ROAD_ADDR'];
				$to_detail_addr		= $order_to_data['TO_DETAIL_ADDR'];
				
				$to_country_code	= $order_to_data['TO_COUNTRY_CODE'];
				$to_province_idx	= $order_to_data['TO_PROVINCE_IDX'];
				$to_city			= $order_to_data['TO_CITY'];
				$to_address			= $order_to_data['TO_ADDRESS'];
			}
		}

		if($country == "KR") {
			$road_addr_str		= $to_road_addr;
			$lot_addr_str		= $to_lot_addr;
			$detail_addr_str	= $to_detail_addr;
		} else {
			$to_country_name = "";
			if ($to_country_code != null) {
				$to_country_name = $db->get('COUNTRY_INFO','COUNTRY_CODE = ?',array($to_country_code))[0]['COUNTRY_NAME'];
			}
			
			$to_province_name = "";
			if($to_province_idx != null) {
				$to_province_name = $db->get('PROVINCE_INFO','IDX = ?', array($to_province_idx))[0]['PROVINCE_NAME'];
			} else {
				$to_province_name = "";
			}
			
			$road_addr_str		= $to_city.' '.$to_province_name.' '.$to_country_name;
			$lot_addr_str		= $road_addr_str;
			$detail_addr_str	= $to_address;
		}
		
		$update_member_as_sql = "
			UPDATE
				MEMBER_AS
			SET
				TO_PLACE = '".$to_place."',
				TO_NAME = '".$to_name."',
				TO_MOBILE = '".$to_mobile."',
				TO_ZIPCODE = '".$to_zipcode."',
				TO_LOT_ADDR = '".$lot_addr_str."',
				TO_ROAD_ADDR = '".$road_addr_str."',
				TO_DETAIL_ADDR = '".$detail_addr_str."',
				TO_COUNTRY_CODE = '".$to_country_code."',
				TO_PROVINCE_IDX = '".$to_province_idx."',
				TO_CITY = '".$to_city."',
				ORDER_MEMO = '".$order_memo."'
			WHERE
				IDX = ".$as_idx."
		";
		
		$db->query($update_member_as_sql);
		
		$json_result['data'] = array(
			'to_place'			=>$to_place,
			'to_name'			=>$to_name,
			'to_mobile'			=>$to_mobile,
			'to_zipcode'		=>$to_zipcode,
			'to_lot_addr'		=>$lot_addr_str,
			'to_road_addr'		=>$road_addr_str,
			'to_detail_addr'	=>$detail_addr_str,
			'to_country_code'	=>$to_country_code,
			'to_province_idx'	=>$to_province_idx,
			'to_city'			=>$to_city,
			'order_memo'		=>$order_memo
		);
		
		echo json_encode($json_result);
		exit;
	} else {
		$json_result['code'] = 301;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0006', array());
	}
}

?>