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

$order_idx = 0;
if (isset($_POST['order_idx'])) {
	$order_idx = $_POST['order_idx'];
}

$delivery_type = null;
if (isset($_POST['delivery_type'])) {
	$delivery_type = $_POST['delivery_type'];
}

if ($member_idx > 0 && $order_idx > 0) {
	$order_cnt = $db->count("ORDER_INFO","IDX = ".$order_idx." AND COUNTRY = '".$country."' AND MEMBER_IDX = ".$member_idx);
	
	if ($order_cnt > 0) {
		$db_result = 0;
		
		$exchange_cnt = $db->count("TMP_ORDER_PRODUCT_EXCHANGE","ORDER_IDX = ".$order_idx);
		if ($exchange_cnt > 0) {
			updateOrderProductInfo($db,"OEX",$order_idx);
			addOrderProductExchange($db,$order_idx);
		}
		
		$refund_cnt = $db->count("TMP_ORDER_PRODUCT_REFUND","ORDER_IDX = ".$order_idx);
		if ($refund_cnt > 0) {
			updateOrderProductInfo($db,"ORF",$order_idx);
			addOrderProductRefund($db,$order_idx);
		}
		
		$db->query("DELETE FROM TMP_ORDER_PRODUCT_EXCHANGE WHERE ORDER_IDX = ".$order_idx);
		$db->query("DELETE FROM TMP_ORDER_PRODUCT_REFUND WHERE ORDER_IDX = ".$order_idx);
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

function updateHousingInfo($db,$order_status,$order_idx,$housing_company,$housing_num) {
	$order_table = null;
	switch ($order_status) {
		case "OEX" :
			$order_table = "TMP_ORDER_PRODUCT_EXCHANGE";
			
			break;
		
		case "ORF" :
			$order_table = "TMP_ORDER_PRODUCT_REFUND";
			break;
	}
	
	$update_housing_info_sql = "
		UPDATE
			".$order_table."
		SET
			HOUSING_COMPANY = '".$housing_company."',
			HOUSING_NUM = '".$housing_num."',
			HOUSING_DATE = NOW()
		WHERE
			ORDER_IDX = ".$order_idx."
	";
	
	$db->query($update_housing_info_sql);
}

function updateOrderProductInfo($db,$order_status,$order_idx) {
	$order_table = null;
	switch ($order_status) {
		case "OEX" :
			$order_table = "TMP_ORDER_PRODUCT_EXCHANGE";
			
			break;
		
		case "ORF" :
			$order_table = "TMP_ORDER_PRODUCT_REFUND";
			break;
	}
	
	$update_order_product_sql = "
		UPDATE
			ORDER_PRODUCT OP,
			(
				SELECT
					ORDER_PRODUCT_CODE		AS ORDER_PRODUCT_CODE,
					SUM(PRODUCT_QTY)		AS PRODUCT_QTY,
					SUM(PRODUCT_PRICE)		AS PRODUCT_PRICE
				FROM
					".$order_table."
				WHERE
					ORDER_IDX = ".$order_idx."
				GROUP BY
					ORDER_PRODUCT_CODE
			) OT
		SET
			OP.ORDER_STATUS = '".$order_status."',
			OP.PRODUCT_PRICE = OP.PRODUCT_PRICE / (OP.PRODUCT_QTY - OT.PRODUCT_QTY),
			OP.PRODUCT_QTY = (OP.PRODUCT_QTY - OT.PRODUCT_QTY)
		WHERE
			OP.ORDER_PRODUCT_CODE = OT.ORDER_PRODUCT_CODE
	";
	
	$db->query($update_order_product_sql);
}

function addOrderProductExchange($db,$order_idx) {
	$insert_order_product_exchange_sql = "
		INSERT INTO
			ORDER_PRODUCT_EXCHANGE
		SELECT
			*
		FROM
			TMP_ORDER_PRODUCT_EXCHANGE
		WHERE
			ORDER_IDX = ".$order_idx."
	";
	
	$db->query($insert_order_product_exchange_sql);
	
	$last_idx = $db->last_id();
	
	return $last_idx;
}

function addOrderProductRefund($db,$order_idx) {
	$insert_order_product_refund_sql = "
		INSERT
			ORDER_PRODUCT_REFUND
		SELECT
			*
		FROM
			TMP_ORDER_PRODUCT_REFUND
		WHERE
			ORDER_IDX = ".$order_idx."
	";
	
	$db->query($insert_order_product_refund_sql);
	
	$last_idx = $db->last_id();
	
	return $last_idx;
}

?>