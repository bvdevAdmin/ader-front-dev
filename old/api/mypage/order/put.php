<?php
/*
 +=============================================================================
 | 
 | 마이페이지_주문취소,주문교환/반품 - [주문 상태별 상품 테이블] 등록처리, [주문 상품 테이블] 상품 수량, 상품 가격 갱신
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

include_once(dir_f_api."/mypage/order/order-common.php");
include_once(dir_f_api."/mypage/order/order-pg.php");

include_once(dir_f_api."/delivery/common.php");

include_once(dir_f_api."/send/send-mail.php");

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

if ($member_idx > 0 && isset($order_code)) {
	$order_cnt = $db->count("ORDER_INFO","ORDER_CODE = '".$order_code."' AND COUNTRY = '".$country."' AND MEMBER_IDX = ".$member_idx);
	
	if ($order_cnt > 0) {
		/* 배송추적 토큰 발행 */
		$token_num = checkToken($db);
		
		/* 임시 주문 교환 상품 체크처리 */
		$cnt_TE = $db->count("TMP_ORDER_PRODUCT_EXCHANGE","ORDER_CODE = '".$order_code."'");
		if ($cnt_TE > 0) {
			/* 주문 교환 정보 등록처리 */
			$order_idx = addOrderExchange($db,$order_code);
			if (!empty($order_idx)) {
				/* 주문 교환 상품 정보 등록처리 */
				addProductExchange($db,$order_idx,$order_code);
			}
			
			/* 주문 로그 등록처리 */
			addOrderLogByIdx($db,"OEX",$order_idx,$member_id);
			
			/* 주문 추적정보 등록처리 */
			addOrderTrace($db,$token_num,"OEX",$order_update_code);
		}
		
		/* 임시 주문 반품 상품 체크처리 */
		$cnt_TR = $db->count("TMP_ORDER_PRODUCT_REFUND","ORDER_CODE = '".$order_code."'");
		if ($cnt_TR > 0) {
			/* 임시 주문 반품 상품 체크처리 */
			$order_idx = addOrderRefund($db,$order_code);
			if (!empty($order_idx)) {
				/* 주문 반품 상품 정보 등록처리 */
				addProductRefund($db,$order_idx,$order_code);
			}
			
			/* 주문 반품 로그 등록처리 */
			addOrderLogByIdx($db,"ORF",$order_idx,$member_id);
			
			/* 주문 추적정보 등록처리 */
			addOrderTrace($db,$token_num,"ORF",$order_update_code);
		}
		
		/* 임시 주문 교환/반품 정보 삭제처리 */
		delTmpOrderProduct($db,$order_code);
		
		/* ========== NAVER CLOUD PLATFORM::교환/반품 접수처리 메일 발송 ========== */
		/* PARAM::MAIL */
		$param_mail = array(
			'country'		=>$country,
			'mail_type'		=>"M",
			'mail_code'		=>"MAIL_CODE_0007",
			
			'param_member'	=>$member_idx,
			'param_admin'	=>null
		);
		
		/* PARAM::MAIL DATA */
		$param_data = array(
			'member_name'	=>$_SESSION['MEMBER_NAME'],
			'member_id'		=>$member_id
		);
		
		/* 교환/반품 접수처리 메일 발송 */
		callSEND_mail($db,$param_mail,$param_data);
	} else {
		$json_result['code'] = 301;
		$json_result['msg'] = "선택한 주문정보가 존재하지 않습니다.";
		
		echo json_encode($json_result);
		exit;
	}
} else {
	$json_result['code'] = 302;
	$json_result['msg'] = "부적절한 주문 정보가 선택되었습니다. 취소/환불 하려는 주문을 다시 선택해주세요.";
	
	echo json_encode($json_result);
	exit;
}

function addOrderExchange($db,$order_code) {
	$order_idx = null;
	
	$insert_order_exchange_sql = "
		INSERT INTO
			ORDER_EXCHANGE
		(
			COUNTRY,
			ORDER_CODE,
			ORDER_UPDATE_CODE,
			ORDER_TITLE,
			ORDER_STATUS,
			
			MEMBER_IDX,
			MEMBER_ID,
			MEMBER_NAME,
			MEMBER_MOBILE,
			MEMBER_LEVEL,
			
			PRICE_PRODUCT,
			PRICE_DELIVERY,
			
			HOUSING_TYPE,
			HOUSING_COMPANY,
			HOUSING_NUM,
			HOUSING_START_DATE,
			HOUSING_END_DATE,
			
			PG_UID,
			PG_TID,
			PG_MID,
			PG_PAYMENT,
			PG_PAYMENT_KEY,
			PG_ISSUE_CODE,
			PG_CARD_NUMBER,
			PG_PROVIDER,
			PG_STATUS,
			PG_DATE,
			PG_PRICE,
			PG_CURRENCY,
			PG_RECEIPT_URL,
			
			TO_PLACE,
			TO_NAME,
			TO_MOBILE,
			TO_ZIPCODE,
			TO_LOT_ADDR,
			TO_ROAD_ADDR,
			TO_DETAIL_ADDR,
			
			CREATER,
			UPDATER
		)
		SELECT
			COUNTRY,
			ORDER_CODE,
			ORDER_UPDATE_CODE,
			ORDER_TITLE,
			'OEH',
			
			MEMBER_IDX,
			MEMBER_ID,
			MEMBER_NAME,
			MEMBER_MOBILE,
			MEMBER_LEVEL,
			
			PRICE_PRODUCT,
			PRICE_DELIVERY,
			
			HOUSING_TYPE,
			HOUSING_COMPANY,
			HOUSING_NUM,
			HOUSING_START_DATE,
			HOUSING_END_DATE,
			
			PG_UID,
			PG_TID,
			PG_MID,
			PG_PAYMENT,
			PG_PAYMENT_KEY,
			PG_ISSUE_CODE,
			PG_CARD_NUMBER,
			PG_PROVIDER,
			PG_STATUS,
			PG_DATE,
			PG_PRICE,
			PG_CURRENCY,
			PG_RECEIPT_URL,
			
			TO_PLACE,
			TO_NAME,
			TO_MOBILE,
			TO_ZIPCODE,
			TO_LOT_ADDR,
			TO_ROAD_ADDR,
			TO_DETAIL_ADDR,
			
			CREATER,
			UPDATER
		FROM
			TMP_ORDER_EXCHANGE
		WHERE
			ORDER_CODE = ?
	";
	
	$db->query($insert_order_exchange_sql,array($order_code));
	
	$order_idx = $db->last_id();
	
	return $order_idx;
}

function addProductExchange($db,$order_idx,$order_code) {
	$insert_order_product_exchange_sql = "
		INSERT INTO
			ORDER_PRODUCT_EXCHANGE
		(
			ORDER_IDX,
			ORDER_CODE,
			ORDER_PRODUCT_CODE,
			ORDER_UPDATE_CODE,
			ORDER_STATUS,
			
			PRODUCT_IDX,
			PRODUCT_TYPE,
			PRODUCT_CODE,
			PRODUCT_NAME,
			
			OPTION_IDX,
			BARCODE,
			OPTION_NAME,
			
			PREV_OPTION_IDX,
			
			PRODUCT_QTY,
			PRODUCT_PRICE,
			DELIVERY_PRICE,
			
			DELIVERY_IDX,
			DELIVERY_NUM,
			
			DEPTH1_IDX,
			DEPTH2_IDX,
			REASON_MEMO,
			
			CREATER,
			UPDATER
		)
		SELECT
			".$order_idx."			AS ORDER_IDX,
			TE.ORDER_CODE			AS ORDER_CODE,
			TE.ORDER_PRODUCT_CODE	AS ORDER_PRODUCT_CDOE,
			TE.ORDER_UPDATE_CODE	AS ORDER_UPDATE_CODE,
			'OEH'					AS ORDER_STATUS,
			
			TE.PRODUCT_IDX			AS PRODUCT_IDX,
			TE.PRODUCT_TYPE			AS PRODUCT_TYPE,
			TE.PRODUCT_CODE			AS PRODUCT_CODE,
			TE.PRODUCT_NAME			AS PRODUCT_NAME,
			
			TE.OPTION_IDX			AS OPTION_IDX,
			TE.BARCODE				AS BARCODE,
			TE.OPTION_NAME			AS OPTION_NAME,
			
			TE.PREV_OPTION_IDX		AS PREV_OPTION_IDX,
			
			SUM(TE.PRODUCT_QTY)		AS PRODUCT_QTY,
			TE.PRODUCT_PRICE		AS PRODUCT_PRICE,
			TE.DELIVERY_PRICE		AS DELIVERY_PRICE,
			
			TE.DELIVERY_IDX			AS DELIVERY_IDX,
			TE.DELIVERY_NUM			AS DELIVERY_NUM,
			
			TE.DEPTH1_IDX			AS DEPTH1_IDX,
			TE.DEPTH2_IDX			AS DEPTH2_IDX,
			TE.REASON_MEMO			AS REASON_MEMO,
			
			TE.CREATER				AS CREATER,
			TE.UPDATER				AS UPDATER
		FROM
			TMP_ORDER_PRODUCT_EXCHANGE TE
		WHERE
			TE.ORDER_CODE = ?
		GROUP BY
			TE.ORDER_PRODUCT_CODE
		ORDER BY
			TE.ORDER_PRODUCT_CODE ASC
	";
	
	$db->query($insert_order_product_exchange_sql,array($order_code));
}

function addOrderRefund($db,$order_code) {
	$order_idx = null;
	
	$insert_order_exchange_sql = "
		INSERT INTO
			ORDER_REFUND
		(
			COUNTRY,
			ORDER_CODE,
			ORDER_UPDATE_CODE,
			ORDER_TITLE,
			ORDER_STATUS,
			
			MEMBER_IDX,
			MEMBER_ID,
			MEMBER_NAME,
			MEMBER_MOBILE,
			MEMBER_LEVEL,
			
			PRICE_PRODUCT,
			PRICE_DELIVERY,
			
			HOUSING_TYPE,
			HOUSING_COMPANY,
			HOUSING_NUM,
			HOUSING_START_DATE,
			HOUSING_END_DATE,
			
			PG_UID,
			PG_TID,
			PG_MID,
			PG_PAYMENT,
			PG_PAYMENT_KEY,
			PG_ISSUE_CODE,
			PG_CARD_NUMBER,
			PG_PROVIDER,
			PG_STATUS,
			PG_DATE,
			PG_PRICE,
			PG_CURRENCY,
			PG_RECEIPT_URL,
			
			CREATER,
			UPDATER
		)
		SELECT
			COUNTRY,
			ORDER_CODE,
			ORDER_UPDATE_CODE,
			ORDER_TITLE,
			'ORH',
			
			MEMBER_IDX,
			MEMBER_ID,
			MEMBER_NAME,
			MEMBER_MOBILE,
			MEMBER_LEVEL,
			
			PRICE_PRODUCT,
			PRICE_DELIVERY,
			
			HOUSING_TYPE,
			HOUSING_COMPANY,
			HOUSING_NUM,
			HOUSING_START_DATE,
			HOUSING_END_DATE,
			
			PG_UID,
			PG_TID,
			PG_MID,
			PG_PAYMENT,
			PG_PAYMENT_KEY,
			PG_ISSUE_CODE,
			PG_CARD_NUMBER,
			PG_PROVIDER,
			PG_STATUS,
			PG_DATE,
			PG_PRICE,
			PG_CURRENCY,
			PG_RECEIPT_URL,
			
			CREATER,
			UPDATER
		FROM
			TMP_ORDER_REFUND
		WHERE
			ORDER_CODE = ?
	";
	
	$db->query($insert_order_exchange_sql,array($order_code));
	
	$order_idx = $db->last_id();
	
	return $order_idx;
}

function addProductRefund($db,$order_idx,$order_code) {
	$insert_order_product_refund_sql = "
		INSERT INTO
			ORDER_PRODUCT_REFUND
		(
			ORDER_IDX,
			ORDER_CODE,
			ORDER_PRODUCT_CODE,
			ORDER_UPDATE_CODE,
			ORDER_STATUS,
			
			PRODUCT_IDX,
			PRODUCT_TYPE,
			PRODUCT_CODE,
			PRODUCT_NAME,
			
			OPTION_IDX,
			BARCODE,
			OPTION_NAME,
			
			PRODUCT_QTY,
			PRODUCT_PRICE,
			DELIVERY_PRICE,
			
			DELIVERY_IDX,
			DELIVERY_NUM,
			
			DEPTH1_IDX,
			DEPTH2_IDX,
			REASON_MEMO,
			
			CREATER,
			UPDATER
		)
		SELECT
			".$order_idx."			AS ORDER_IDX,
			TF.ORDER_CODE			AS ORDER_CODE,
			TF.ORDER_PRODUCT_CODE	AS ORDER_PRODUCT_CDOE,
			TF.ORDER_UPDATE_CODE	AS ORDER_UPDATE_CODE,
			'ORH'					AS ORDER_STATUS,
			
			TF.PRODUCT_IDX			AS PRODUCT_IDX,
			TF.PRODUCT_TYPE			AS PRODUCT_TYPE,
			TF.PRODUCT_CODE			AS PRODUCT_CODE,
			TF.PRODUCT_NAME			AS PRODUCT_NAME,
			
			TF.OPTION_IDX			AS OPTION_IDX,
			TF.BARCODE				AS BARCODE,
			TF.OPTION_NAME			AS OPTION_NAME,
			
			SUM(TF.PRODUCT_QTY)		AS PRODUCT_QTY,
			TF.PRODUCT_PRICE		AS PRODUCT_PRICE,
			TF.DELIVERY_PRICE		AS DELIVERY_PRICE,
			
			TF.DELIVERY_IDX			AS DELIVERY_IDX,
			TF.DELIVERY_NUM			AS DELIVERY_NUM,
			
			TF.DEPTH1_IDX			AS DEPTH1_IDX,
			TF.DEPTH2_IDX			AS DEPTH2_IDX,
			TF.REASON_MEMO			AS REASON_MEMO,
			
			TF.CREATER				AS CREATER,
			TF.UPDATER				AS UPDATER
		FROM
			TMP_ORDER_PRODUCT_REFUND TF
		WHERE
			TF.ORDER_CODE = ?
		GROUP BY
			TF.ORDER_PRODUCT_CODE
		ORDER BY
			TF.ORDER_PRODUCT_CODE ASC
	";
	
	$db->query($insert_order_product_refund_sql,array($order_code));
}

?>