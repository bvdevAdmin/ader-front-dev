<?php
/*
 +=============================================================================
 | 
 | 마이페이지_주문조회화면 - 주문 상태 변경 (주문취소)
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

include_once("/var/www/www/api/common/common.php");
include_once("/var/www/www/api/mypage/order/common.php");

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

$order_status = null;
if (isset($_POST['order_status'])) {
	$order_status = $_POST['order_status'];
}

$order_idx = 0;
if (isset($_POST['order_idx'])) {
	$order_idx = $_POST['order_idx'];
}

$order_product_info = array();
if (isset($_POST['order_product_info'])) {
	$order_product_info = json_decode($_POST['order_product_info'],true);
}

$reason_depth1_idx = 0;
if (isset($_POST['reason_depth1_idx'])) {
	$reason_depth1_idx = $_POST['reason_depth1_idx'];
}

$reason_depth2_idx = 0;
if (isset($_POST['reason_depth2_idx'])) {
	$reason_depth2_idx = $_POST['reason_depth2_idx'];
}

$reason_memo = null;
if (isset($_POST['reason_memo'])) {
	$reason_memo = xssEncode($_POST['reason_memo']);
}

if ($member_idx > 0 && $order_idx > 0) {
	$order_cnt = $db->count("ORDER_INFO","IDX = ".$order_idx." AND COUNTRY = '".$country."' AND MEMBER_IDX = ".$member_idx);
	
	if ($order_cnt > 0) {
		if (count($order_product_info) > 0) {
			if ($order_status == "OEX") {
				addTmpOrderProductExchangeInfo($db,$order_product_info,$reason_depth1_idx,$reason_depth2_idx,$reason_memo,$member_id);
			} else if ($order_status == "ORF") {
				addTmpOrderProductRefundInfo($db,$order_product_info,$reason_depth1_idx,$reason_depth2_idx,$reason_memo,$member_id);
			}
		} else {
			$json_result['code'] = 303;
			$json_result['msg'] = "교환/반품하려는 주문상품을 선택해주세요.";
			
			return $json_result;
		}
	} else {
		$json_result['code'] = 302;
		$json_result['msg'] = "선택한 주문정보가 존재하지 않습니다.";
		
		return $json_result;
	}
} else {
	$json_result['code'] = 301;
	$json_result['msg'] = "부적절한 주문 정보가 선택되었습니다. 취소/환불 하려는 주문을 다시 선택해주세요.";
	
	return $json_result;
}

function addTmpOrderProductExchangeInfo($db,$order_product_info,$reason_depth1_idx,$reason_depth2_idx,$reason_memo,$member_id) {
	$select_order_product_option_sql = "
		SELECT
			OO.IDX				AS OPTION_IDX,
			OO.BARCODE			AS BARCODE,
			OO.OPTION_NAME		AS OPTION_NAME
		FROM
			ORDERSHEET_OPTION OO
		WHERE
			OO.IDX = ".$order_product_info['option_idx']." AND
			OO.ORDERSHEET_IDX = (
				SELECT
					S_PR.ORDERSHEET_IDX
				FROM
					SHOP_PRODUCT S_PR
				WHERE
					S_PR.IDX = (
						SELECT
							S_OP.PRODUCT_IDX
						FROM
							ORDER_PRODUCT S_OP
						WHERE
							S_OP.IDX = ".$order_product_info['order_product_idx']."
					)
			)
	";
	
	$db->query($select_order_product_option_sql);
	
	$option_info = array();
	foreach($db->fetch() as $option_data) {
		$option_info = array(
			'option_idx'		=>$option_data['OPTION_IDX'],
			'barcode'			=>$option_data['BARCODE'],
			'option_name'		=>$option_data['OPTION_NAME']
		);
	}
	
	$insert_tmp_order_product_exchange_info_sql = "
		INSERT INTO
			TMP_ORDER_PRODUCT_EXCHANGE
		(
			ORDER_IDX,
			ORDER_CODE,
			ORDER_PRODUCT_CODE,
			ORDER_STATUS,
			
			EXCHANGE_DATE,
			
			PRODUCT_IDX,
			PRODUCT_TYPE,
			REORDER_CNT,
			PREORDER_FLG,
			PRODUCT_CODE,
			PRODUCT_NAME,
			
			PREV_OPTION_IDX,
			OPTION_IDX,
			BARCODE,
			OPTION_NAME,
			
			PRODUCT_QTY,
			PRODUCT_PRICE,
			
			REASON_DEPTH1_IDX,
			REASON_DEPTH2_IDX,
			REASON_MEMO,
			
			CREATE_DATE,
			CREATER,
			UPDATE_DATE,
			UPDATER
		)
		SELECT
			OP.ORDER_IDX							AS ORDER_IDX,
			OP.ORDER_CODE							AS ORDER_CODE,
			OP.ORDER_PRODUCT_CODE					AS ORDER_PRODUCT_CDOE,
			'OEX'									AS ORDER_STATUS,
			
			NOW()									AS EXCHANGE_DATE,
			
			OP.PRODUCT_IDX							AS PRODUCT_IDX,
			OP.PRODUCT_TYPE							AS PRODUCT_TYPE,
			OP.REORDER_CNT							AS REORDER_CNT,
			OP.PREORDER_FLG							AS PREORDER_FLG,
			OP.PRODUCT_CODE							AS PRODUCT_CODE,
			OP.PRODUCT_NAME							AS PRODUCT_NAME,
			
			OP.OPTION_IDX							AS PREV_OPTION_IDX,
			".$option_info['option_idx']."			AS OPTION_IDX,
			'".$option_info['barcode']."'			AS BARCODE,
			'".$option_info['option_name']."'		AS OPTION_NAME,
			
			1										AS PRODUCT_QTY,
			(OP.PRODUCT_PRICE / OP.PRODUCT_QTY)		AS PRODUCT_PRICE,
			
			".$reason_depth1_idx."					AS REASON_DEPTH1_IDX,
			".$reason_depth2_idx."					AS REASON_DEPTH2_IDX,
			".$reason_memo."						AS REASON_MEMO,
			
			NOW()									AS CREATE_DATE,
			'".$member_id."'						AS CREATER,
			NOW()									AS UPDATE_DATE,
			'".$member_id."'						AS UPDATER
		FROM
			ORDER_PRODUCT OP
		WHERE
			OP.IDX = ".$order_product_info['order_product_idx']."
	";
	
	$db->query($insert_tmp_order_product_exchange_info_sql);
	
	return $db->last_id();
}

function addTmpOrderProductRefundInfo($db,$order_product_info,$reason_depth1_idx,$reason_depth2_idx,$reason_memo,$member_id) {
	$insert_tmp_order_product_refund_info_sql = "
		INSERT INTO
			TMP_ORDER_PRODUCT_REFUND
		(
			ORDER_IDX,
			ORDER_CODE,
			ORDER_PRODUCT_CODE,
			ORDER_STATUS,
			
			REFUND_DATE,
			
			PRODUCT_IDX,
			PRODUCT_TYPE,
			REORDER_CNT,
			PREORDER_FLG,
			PRODUCT_CODE,
			PRODUCT_NAME,
			
			OPTION_IDX,
			BARCODE,
			OPTION_NAME,
			
			PRODUCT_QTY,
			PRODUCT_PRICE,
			
			REASON_DEPTH1_IDX,
			REASON_DEPTH2_IDX,
			REASON_MEMO,
			
			CREATE_DATE,
			CREATER,
			UPDATE_DATE,
			UPDATER
		)
		SELECT
			OP.ORDER_IDX								AS ORDER_IDX,
			OP.ORDER_CODE								AS ORDER_CODE,
			OP.ORDER_PRODUCT_CODE						AS ORDER_PRODUCT_CDOE,
			'ORF'										AS ORDER_STATUS,
			
			NOW()										AS REFUND_DATE,
			
			OP.PRODUCT_IDX								AS PRODUCT_IDX,
			OP.PRODUCT_TYPE								AS PRODUCT_TYPE,
			OP.REORDER_CNT								AS REORDER_CNT,
			OP.PREORDER_FLG								AS PREORDER_FLG,
			OP.PRODUCT_CODE								AS PRODUCT_CODE,
			OP.PRODUCT_NAME								AS PRODUCT_NAME,
			
			OP.OPTION_IDX								AS OPTION_IDX,
			OP.BARCODE									AS BARCODE,
			OP.OPTION_NAME								AS OPTION_NAME,
			
			1											AS PRODUCT_QTY,
			(OP.PRODUCT_PRICE / OP.PRODUCT_QTY)			AS PRODUCT_PRICE,
			
			".$reason_depth1_idx."						AS REASON_DEPTH1_IDX,
			".$reason_depth2_idx."						AS REASON_DEPTH2_IDX,
			".$reason_memo."							AS REASON_MEMO,
			
			NOW()										AS CREATE_DATE,
			'".$member_id."'							AS CREATER,
			NOW()										AS UPDATE_DATE,
			'".$member_id."'							AS UPDATER
		FROM
			ORDER_PRODUCT OP
		WHERE
			OP.IDX = ".$order_product_info['order_product_idx']."
	";
	
	$db->query($insert_tmp_order_product_refund_info_sql);
	
	return $db->last_id();
}

?>