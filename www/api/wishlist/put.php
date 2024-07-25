<?php
/*
 +=============================================================================
 | 
 | 상품 상세 - 찜한 상품 등록 // '/var/www/www/api/order/whish/add.php';
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.10.13
 | 최종 수정    : 양한빈
 | 최종 수정일	: 2024.06.26
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$member_id = null;
if (isset($_SESSION['MEMBER_ID'])) {
	$member_id = $_SESSION['MEMBER_ID'];
}

if ($member_idx > 0 && isset($product_idx)) {
	//찜한 상품 리스트 등록 전 동일 상품 중복체크
	$whish_list_cnt = $db->count("WHISH_LIST"," MEMBER_IDX = ".$member_idx." AND PRODUCT_IDX = ".$product_idx." AND DEL_FLG = FALSE");
	
	if ($whish_list_cnt > 0) {
		$json_result['code'] = 402;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0065', array());
		
		echo json_encode($json_result);
		exit;
	} else {
		$insert_whish_sql = "
			INSERT INTO
				WHISH_LIST
			(
				COUNTRY,
				MEMBER_IDX,
				MEMBER_ID,
				PRODUCT_IDX,
				PRODUCT_CODE,
				PRODUCT_NAME,
				CREATER,
				UPDATER
			)
			SELECT
				'".$country."'		AS COUNTRY,
				".$member_idx."		AS MEMBER_IDX,
				'".$member_id."'	AS MEMBER_ID,
				IDX					AS PRODUCT_IDX,
				PRODUCT_CODE		AS PRODUCT_CODE,
				PRODUCT_NAME		AS PRODUCT_NAME,
				'".$member_id."'	AS CREATER,
				'".$member_id."'	AS UPDATER
			FROM
				SHOP_PRODUCT
			WHERE
				IDX = ".$product_idx."
		";
	
		$db->query($insert_whish_sql);
	}
	
	$whish_cnt = $db->count("WHISH_LIST","MEMBER_IDX = ".$member_idx." AND DEL_FLG = FALSE");
	
	$json_result['data'] = $whish_cnt;
}
