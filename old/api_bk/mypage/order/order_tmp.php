<?php
/*
 +=============================================================================
 | 
 | 마이페이지_주문취소,주문교환/반품 - [임시 주문 상태별 상품 테이블] 등록처리
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
include_once("/var/www/www/api/mypage/order/order-common.php");
include_once("/var/www/www/api/mypage/order/order-pg.php");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

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

$order_code = null;
if (isset($_POST['order_code'])) {
	$order_code = $_POST['order_code'];
}

$param_order_product = array();
if (isset($_POST['param_order_product'])) {
	$param_order_product = $_POST['param_order_product'];
	/*
		$param_order_product [
			'order_status'			=>order_status,
			'order_product_code'	=>order_product_code,
			'product_qty'			=>product_qty,
			'option_idx'			=>option_idx
		];
	*/
}

$depth1_idx = 0;
if (isset($_POST['depth1_idx'])) {
	$depth1_idx = $_POST['depth1_idx'];
}

$depth2_idx = 0;
if (isset($_POST['depth2_idx'])) {
	$depth2_idx = $_POST['depth2_idx'];
}

$reason_memo = null;
if (isset($_POST['reason_memo'])) {
	$reason_memo = xssEncode($_POST['reason_memo']);
}

if ($member_idx > 0 && $order_code != null) {
	try {
		$order_cnt = $db->count("ORDER_INFO","ORDER_CODE = '".$order_code."' AND COUNTRY = '".$country."' AND MEMBER_IDX = ".$member_idx);
		
		if ($order_cnt > 0) {
			if ($param_order_product != null) {
				//[주문교환] 하려는 주문상품 PARAM 설정
				$param_product_OEX = setParamProduct("OEX",$param_order_product);
				if ($param_product_OEX != null) {
					//1. [주문교환] 하려는 주문상품의 가격정보 계산
					$param_exchange_price = getParamPrice($db,$param_product_OEX);
					
					//2. 주문 갱신코드 생성
					$cnt_OEX = $db->count("ORDER_EXCHANGE","ORDER_CODE = '".$order_code."'");
					$order_update_code = $order_code."-E-".($cnt_OEX + 1);
					
					//3. 교환하려는 주문상품의 옵션정보 취득
					$exchange_option = getExchangeOption($db,$param_product_OEX['option_idx']);
					
					//[임시 주문 교환 상품 테이블] 등록 PARAM
					$param_product_exchange = array(
						'order_update_code'		=>$order_update_code,
						
						'param_product_OEX'		=>$param_product_OEX,
						'exchange_option'		=>$exchange_option,
						
						'depth1_idx'			=>$depth1_idx,
						'depth2_idx'			=>$depth2_idx,
						'reason_memo'			=>$reason_memo,
						
						'member_id'				=>$member_id
					);
					
					//4-1. [임시 주문 교환 상품 테이블] 등록처리
					addTmpOrderProductExchange($db,$param_product_exchange);
				}
				
				//[주문교환] 하려는 주문상품 PARAM 설정
				$param_product_ORF = setParamProduct("ORF",$param_order_product);
				if ($param_product_ORF != null) {
					//1. [주문반품] 하려는 주문상품의 가격정보 계산
					$param_refund_price = getParamPrice($db,$param_product_ORF);
					
					//2. 주문 갱신코드 생성
					$cnt_ORF = $db->count("ORDER_REFUND","ORDER_CODE = '".$order_code."'");
					$order_update_code = $order_code."-R-".($cnt_ORF + 1);
					
					//[임시 주문 반품 상품 테이블] 등록 PARAM
					$param_product_refund = array(
						'order_code'			=>$order_code,
						'order_update_code'		=>$order_update_code,
						
						'param_product_ORF'		=>$param_product_ORF,
						
						'depth1_idx'			=>$depth1_idx,
						'depth2_idx'			=>$depth2_idx,
						'reason_memo'			=>$reason_memo,
						
						'member_id'				=>$member_id
					);
					
					//3. [임시 주문 반품 상품 테이블] 등록처리
					addTmpOrderProductRefund($db,$param_product_refund);
				}
			} else {
				$json_result['code'] = 304;
				$json_result['msg'] = "교환/반품하려는 주문상품을 선택해주세요.";
				
				return $json_result;
			}
		} else {
			$json_result['code'] = 303;
			$json_result['msg'] = "선택한 주문정보가 존재하지 않습니다.";
			
			return $json_result;
		}
	} catch (mysqli_sql_exception $exception) {
		$db->rollback();
		print_r($exception);
		
		$json_result['code'] = 302;
		$json_result['msg'] = "주문 교환/반품상품 등록처리중 오류가 발생했습니다.";
	}
} else {
	$json_result['code'] = 301;
	$json_result['msg'] = "부적절한 주문 정보가 선택되었습니다. 취소/환불 하려는 주문을 다시 선택해주세요.";
	
	return $json_result;
}

function setParamProduct($order_status,$data) {
	$param_product = null;
	
	if ($data['order_status'] == $order_status) {
		$param_product = array(
			'order_product_code'	=>$data['order_product_code'],
			'product_qty'			=>$data['product_qty'],
			'option_idx'			=>$data['option_idx']
		);
	}
	
	return $param_product;
}

function getParamPrice($db,$data) {
	$param_price = array();
	
	$price_product = 0;
	
	$select_order_product_sql = "
		SELECT
			(
				(OP.PRODUCT_PRICE / OP.PRODUCT_QTY) * ".$data['product_qty']."
			)		AS PRODUCT_PRICE
		FROM
			ORDER_PRODUCT OP
		WHERE
			OP.ORDER_CODE = '".$data['order_product_code']."'
	";
	
	$db->query($select_order_product_sql);
	
	foreach($db->fetch() as $param_data) {
		$price_product += $param_data['PRODUCT_PRICE'];
	}
	
	$param_price = array(
		'price_product'		=>$price_product
	);
	
	return $param_price;
}

function getExchangeOption($db,$option_idx) {
	$exchange_option = array();

	$select_ordersheet_option_sql = "
		SELECT
			OO.IDX				AS OPTION_IDX,
			OO.BARCODE			AS BARCODE,
			OO.OPTION_NAME		AS OPTION_NAME
		FROM
			ORDERSHEET_OPTION OO
		WHERE
			OO.IDX = ".$option_idx."
	";
	
	$db->query($select_ordersheet_option_sql);
	
	foreach($db->fetch() as $exchange_data) {
		$exchange_option = array(
			'option_idx'	=>$exchange_data['OPTION_IDX'],
			'barcode'		=>$exchange_data['BARCODE'],
			'option_name'	=>$exchange_data['OPTION_NAME']
		);
	}
	
	return $exchange_option;
}

function addTmpOrderProductExchange($db,$param) {	
	$data = $param['param_product_OEX'];
	$option = $param['exchange_option'];
	
	$insert_tmp_order_product_exchange_sql = "
		INSERT INTO
			TMP_ORDER_PRODUCT_EXCHANGE
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
			
			DEPTH1_IDX,
			DEPTH2_IDX,
			REASON_MEMO,
			
			CREATER,
			UPDATER
		)
		SELECT
			0									AS ORDER_IDX,
			OP.ORDER_CODE						AS ORDER_CODE,
			OP.ORDER_PRODUCT_CODE				AS ORDER_PRODUCT_CDOE,
			'".$param['order_update_code']."'	AS ORDER_UPDATE_CODE,
			'OEH'								AS ORDER_STATUS,
			
			OP.PRODUCT_IDX						AS PRODUCT_IDX,
			OP.PRODUCT_TYPE						AS PRODUCT_TYPE,
			OP.PRODUCT_CODE						AS PRODUCT_CODE,
			OP.PRODUCT_NAME						AS PRODUCT_NAME,
			
			".$option['option_idx']."			AS OPTION_IDX,
			'".$option['barcode']."'			AS BARCODE,
			'".$option['option_name']."'		AS OPTION_NAME,
			
			OP.OPTION_IDX						AS PREV_OPTION_IDX,
			
			1									AS PRODUCT_QTY,
			(
				(OP.PRODUCT_PRICE / OP.PRODUCT_QTY) * ".$data['product_qty']."
			)									AS PRODUCT_PRICE,
			
			".$param['depth1_idx']."			AS DEPTH1_IDX,
			".$param['depth2_idx']."			AS DEPTH2_IDX,
			".$param['reason_memo']."			AS REASON_MEMO,
			
			'".$param['member_id']."'			AS CREATER,
			'".$param['member_id']."'			AS UPDATER
		FROM
			ORDER_PRODUCT OP
		WHERE
			OP.ORDER_PRODUCT_CODE = '".$data['order_product_code']."'
	";
	
	$db->query($insert_tmp_order_product_exchange_sql);
}

//[임시 주문 반품 테이블],[임시 주문 반품 상품 테이블] 등록|갱신처리
function addTmpOrderProductRefund($db,$param) {	
	$data = $param['param_product_ORF'];
	
	$insert_tmp_order_product_refund_sql = "
		INSERT INTO
			TMP_ORDER_PRODUCT_REFUND
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
			
			DEPTH1_IDX,
			DEPTH2_IDX,
			REASON_MEMO,
			
			CREATER,
			UPDATER
		)
		SELECT
			0									AS ORDER_IDX,
			OP.ORDER_CODE						AS ORDER_CODE,
			OP.ORDER_PRODUCT_CODE				AS ORDER_PRODUCT_CDOE,
			'".$param['order_update_code']."'	AS ORDER_UPDATE_CODE,
			'ORH'								AS ORDER_STATUS,
			
			OP.PRODUCT_IDX						AS PRODUCT_IDX,
			OP.PRODUCT_TYPE						AS PRODUCT_TYPE,
			OP.PRODUCT_CODE						AS PRODUCT_CODE,
			OP.PRODUCT_NAME						AS PRODUCT_NAME,
			
			OP.OPTION_IDX						AS OPTION_IDX,
			OP.BARCODE							AS BARCODE,
			OP.OPTION_NAME						AS OPTION_NAME,
			
			1									AS PRODUCT_QTY,
			(
				(OP.PRODUCT_PRICE / OP.PRODUCT_QTY) * ".$data['product_qty']."
			)									AS PRODUCT_PRICE,
			
			".$param['depth1_idx']."			AS DEPTH1_IDX,
			".$param['depth2_idx']."			AS DEPTH2_IDX,
			".$param['reason_memo']."			AS REASON_MEMO,
			
			'".$param['member_id']."'			AS CREATER,
			'".$param['member_id']."'			AS UPDATER
		FROM
			ORDER_PRODUCT OP
		WHERE
			OP.ORDER_PRODUCT_CODE = '".$data['order_product_code']."'
	";
	
	$db->query($insert_tmp_order_product_refund_sql);
}

?>