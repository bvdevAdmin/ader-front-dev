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

include_once(dir_f_api."/common.php");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

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
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	exit;
}

if ($member_idx > 0 && isset($to_zipcode)) {
	$db->begin_transaction();
	
	try {
		if ($default_flg == 'true') {
			$db->update(
				"ORDER_TO",
				array(
					'DEFAULT_FLG'		=>0,
				),
				"
					COUNTRY = '".$country."' AND
					MEMBER_IDX = ".$member_idx."
				"
			);
		}
		
		$bool_default_flg = 0;
		if ($default_flg == 'true') {
			$bool_default_flg = 1;
		} else {
			$bool_default_flg = 0;
		}
		
		if($country == "KR"){
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
					'TO_DETAIL_ADDR'	=>$to_detail_addr,
					
					'DEFAULT_FLG'		=>$bool_default_flg
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
					'TO_ADDRESS'		=>$to_address,
					
					'DEFAULT_FLG'		=>$bool_default_flg
				)
			);
		}
		
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