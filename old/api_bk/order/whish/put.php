<?php
/*
 +=============================================================================
 | 
 | 찜한 상품 리스트 - 상품 정보 수정
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.10.13
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/
include_once("/var/www/www/api/common/common.php");
$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$member_id = null;
if (isset($_SESSION['MEMBER_ID'])) {
	$member_id = $_SESSION['MEMBER_ID'];
}

$whish_idx = 0;
if (isset($_POST['whish_idx'])) {
	$whish_idx = $_POST['whish_idx'];
}

$product_idx = 0;
if (isset($_POST['product_idx'])) {
	$product_idx = $_POST['product_idx'];
}

$option_idx = 0;
if (isset($_POST['option_idx'])) {
	$option_idx = $_POST['option_idx'];
}

$product_qty = 0;
if (isset($_POST['product_qty'])) {
	$product_qty = $_POST['product_qty'];
}

if ($member_idx == 0 || $member_id == null) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	exit;
}

if ($whish_idx > 0 && $product_idx > 0 && $option_idx > 0 && $product_qty > 0) {
	$update_whish_sql = "
		UPDATE
			WHISH_LIST WL,
			(
				SELECT
					IDX				AS OPTION_IDX,
					BARCODE			AS BARCODE,
					OPTION_NAME		AS OPTION_NAME
				FROM
					ORDERSHEET_OPTION
				WHERE
					S_OO.IDX =".$option_idx."
			) AS OO
		SET
			WL.OPTION_IDX = OO.OPTION_IDX,
			WL.BARCODE = OO.BARCODE,
			WL.OPTION_NAME = OO.OPTION_NAME,
			WL.PRODUCT_QTY = ".$product_qty.",
			WL.UPDATE_DATE = NOW(),
			WL.UPDATER = '".$member_id."'
		WHERE
			WL.IDX = ".$whish_idx." AND
			WL.MEMBER_IDX = ".$member_idx." AND
			WL.PRODUCT_IDX = ".$product_idx." AND
			WL.OPTION_IDX = ".$option_idx."
	";
	
	$db->query($sql);
}
?>