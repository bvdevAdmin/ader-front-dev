<?php
/*
 +=============================================================================
 | 
 | 마이페이지_주문취소,주문교환/반품 - 교환/반품 사유별 배송비 체크
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

include_once("/var/www/www/api/mypage/order/order-common.php");
include_once("/var/www/www/api/mypage/order/order-pg.php");

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

$order_code = null;
if (isset($_POST['order_code'])) {
	$order_code = $_POST['order_code'];
}

$housing_type = null;
if (isset($_POST['housing_type'])) {
	$housing_type = $_POST['housing_type'];
}

$housing_company = null;
if (isset($_POST['housing_company'])) {
	$housing_company = $_POST['housing_company'];
}

$housing_num = null;
if (isset($_POST['housing_num'])) {
	$housing_num = $_POST['housing_num'];
}

if ($country != null && $member_idx > 0 && $order_idx > 0 && $order_code != null && $housing_type != null) {
	$select_order_reason_sql = "
		(
			SELECT
				'OEX'						AS ORDER_STATUS,
				S_TE.ORDER_PRODUCT_CODE		AS ORDER_PRODUCT_CODE,
				S_TE.DEPTH1_IDX				AS DEPTH1_IDX,
				S_TE.DEPTH2_IDX				AS DEPTH2_IDX
			FROM
				TMP_ORDER_PRODUCT_EXCHANGE S_TE
			WHERE
				S_TE.ORDER_CODE = '".$order_code."'
		) UNION (
			SELECT
				'ORF'						AS ORDER_STATUS,
				S_TR.ORDER_PRODUCT_CODE		AS ORDER_PRODUCT_CODE,
				S_TR.DEPTH1_IDX				AS DEPTH1_IDX,
				S_TR.DEPTH2_IDX				AS DEPTH2_IDX
			FROM
				TMP_ORDER_PRODUCT_REFUND S_TR
			WHERE
				S_TR.ORDER_CODE = '".$order_code."'
		)
	";
	
	$db->query($select_order_reason_sql);
	
	$order_product_code = array();
	
	$cnt_pg = 0;
	
	foreach($db->fetch() as $reason_data) {
		array_push($order_product_code,"'".$reason_data['ORDER_PRODUCT_CODE']."'");
		
		$depth1_idx = $reason_data['DEPTH1_IDX'];
		$depth2_idx = $reason_data['DEPTH2_IDX'];
		
		$depth1_pg_cnt = $db->count("REASON_DEPTH_1","IDX = ".$depth1_idx." AND REASON_TYPE = '".$reason_data['ORDER_STATUS']."' AND PG_FLG = TRUE");
		$depth2_pg_cnt = $db->count("REASON_DEPTH_2","IDX = ".$depth2_idx." AND DEPTH_1_IDX = ".$depth1_idx." AND REASON_TYPE = '".$reason_data['ORDER_STATUS']."' AND PG_FLG = TRUE");
		
		if ($depth1_pg_cnt > 0 && $depth2_pg_cnt > 0) {
			$cnt_pg++;
		}
	}
	
	$cnt_TE = $db->count("TMP_ORDER_PRODUCT_EXCHANGE","ORDER_CODE = '".$order_code."'");
	$cnt_TR = $db->count("TMP_ORDER_PRODUCT_REFUND","ORDER_CODE = '".$order_code."'");
	
	$check_result = checkOrderProductCancelType($db,$order_code);
	
	$order_pg_info = getOrderPgInfo($db,$order_code);
	
	$total_cancel_price = getTotalCancelPrice($db,"OCC",$order_code);
	$total_refund_price = getTotalCancelPrice($db,"ORF",$order_code);
	
	$price_total = $order_pg_info['price_total'] - $total_cancel_price - $total_refund_price;
	
	$reason_pg_flg = false;
	$price_delivery = 0;
	
	if ($housing_type == "APL") {
		//[주문교환],[주문반품]시 상품 [수거신청] 처리는 한국몰에서만 가능
		if ($cnt_pg > 0) {
			$reason_pg_flg = true;
		}
		
		if ($reason_pg_flg == true) {
			//주문 교환/배송비가 발생 한 경우
			
			//1. 한국몰 주문 교환/반품 배송비 계산 (수거신청)
			//*교환 주문이 1건 이상 존재하는 경우 주문 교환 배송비로 계산
			if ($cnt_TE > 0) {
				//1-1. 주문 교환 배송비 계산
				$price_delivery = 5000;
			} else {
				//1-2. 주문 환불 배송비 계산
				if ($price_total >= 80000) {
					//1-3. 현재 총 결제금액 80000원 이상일 경우
					if ($check_result == "IND") {
						//1-3-1. 부분 환불
						$price_delivery = 2500;
					} else if ($check_result == "ALL") {
						//1-3-2. 전체 환불
						$price_delivery = 5000;
					}
				} else {
					//1-4. 현재 총 결제금액 80000원 미만일 경우
					$price_delivery = 2500;
				}
			}
		} else {
			//주문 교환/배송비가 발생하지 않은 경우
			$price_delivery = 0;
		}
	} else if ($housing_type == "DRC") {
		if ($country == "KR") {
			//주문 교환/배송비가 발생 여부 체크
				
			//2. 한국몰 주문 교환/반품 배송비 계산 (직접발송)
			//*교환 주문이 1건 이상 존재하는 경우 주문 교환 배송비로 계산
			if ($cnt_TE > 0) {
				//2-1. 주문 교환 배송비 계산
				$price_delivery = 2500;
			} else {
				//2-2. 주문 환불 배송비 계산
				if ($price_total >= 80000) {
					if ($check_result == "IND") {
						//2-2-1. 부분 환불
						$price_delivery = 0;
					} else {
						//2-2-2. 부분 환불
						$price_delivery = 2500;
					}
				} else {
					$price_delivery = 0;
				}
			}
		}
	}
	
	if ($housing_type == "APL" && $price_delivery > 0) {
		$reason_pg_flg = true;
	}
	
	$order_update_code = null;
	$cnt_OEX = $db->count("TMP_ORDER_PRODUCT_EXCHANGE","ORDER_CODE = '".$order_code."'");
	if ($cnt_OEX > 0 && $price_delivery > 0) {
		$delivery_code = "DLV-E-XXXXXXXXXX";
		$delivery_title = "주문 교환 추가 배송비";
		
		$order_update_code = addTmpOrder($db,"OEX",$order_code,$housing_type,$housing_company,$housing_num,$price_delivery);
		addTmpDelivery($db,"OEX",$order_code,$order_update_code,$delivery_code,$delivery_title,$price_delivery,$member_id);
	}
	
	$cnt_ORF = $db->count("TMP_ORDER_PRODUCT_REFUND","ORDER_CODE = '".$order_code."'");
	if ($cnt_ORF > 0 && $price_delivery > 0) {
		$delivery_code = "DLV-R-XXXXXXXXXX";
		$delivery_title = "주문 반품 추가 배송비";

		$order_update_code = addTmpOrder($db,"ORF",$order_code,$housing_type,$housing_company,$housing_num,$price_delivery);
		addTmpDelivery($db,"ORF",$order_code,$order_update_code,$delivery_code,$delivery_title,$price_delivery,$member_id);
	}
	
	$json_result['data'] = array(
		'reason_pg_flg'			=>$reason_pg_flg,
		'price_delivery'		=>$price_delivery,
		'order_update_code'		=>$order_update_code
	);
	
	return $json_result;
}

function putHousingInfo($db,$order_code,$housing_company,$housing_num) {
	$cnt_TE = $db->count("TMP_ORDER_EXCHANGE","ORDER_CODE = '".$order_code."'");
	if ($cnt_TE > 0) {
		$update_tmp_order_exchange_sql = "
			UPDATE
				TMP_ORDER_EXCHANGE
			SET
				HOUSING_COMPANY = '".$housing_company."',
				HOUSING_NUM = '".$housing_num."',
				HOUSING_START_DATE = NOW()
			WHERE
				ORDER_CODE = '".$order_code."'
		";
		
		$db->query($update_tmp_order_exchange_sql);
	}
	
	$cnt_TR = $db->count("TMP_ORDER_REFUND","ORDER_CODE = '".$order_code."'");
	if ($cnt_TR > 0) {
		$update_tmp_order_refund_sql = "
			UPDATE
				TMP_ORDER_REFUND
			SET
				HOUSING_COMPANY = '".$housing_company."',
				HOUSING_NUM = '".$housing_num."',
				HOUSING_START_DATE = NOW()
			WHERE
				ORDER_CODE = '".$order_code."'
		";
		
		$db->query($update_tmp_order_refund_sql);
	}
}

function addTmpOrder($db,$order_status,$order_code,$housing_type,$housing_company,$housing_num,$price_delivery) {
	$order_table = getOrderTable($order_status,true);
	
	$order_update_code = null;
	$price_product = 0;
	
	$select_tmp_order_product_table_sql = "
		SELECT
			OT.ORDER_UPDATE_CODE	AS ORDER_UPDATE_CODE,
			(
				IFNULL(
					SUM(OT.PRODUCT_PRICE),0
				)
			)						AS PRICE_PRODUCT
		FROM
			".$order_table['product']." OT
		WHERE
			OT.ORDER_CODE = '".$order_code."'
		GROUP BY
			OT.ORDER_UPDATE_CODE
	";
	
	$db->query($select_tmp_order_product_table_sql);
	
	foreach($db->fetch() as $tmp_data) {
		$order_update_code = $tmp_data['ORDER_UPDATE_CODE'];
		$price_product = $tmp_data['PRICE_PRODUCT'];
	}
	
	$column_to_place = array();
	if ($order_status == "OEX") {
		$column_to_place[0] = "
			TO_PLACE,
			TO_NAME,
			TO_MOBILE,
			TO_ZIPCODE,
			TO_LOT_ADDR,
			TO_ROAD_ADDR,
			TO_DETAIL_ADDR,
		";
		
		$column_to_place[1] = "
			OI.TO_PLACE					AS TO_PLACE,
			OI.TO_NAME					AS TO_NAME,
			OI.TO_MOBILE				AS TO_MOBILE,
			OI.TO_ZIPCODE				AS TO_ZIPCODE,
			OI.TO_LOT_ADDR				AS TO_LOT_ADDR,
			OI.TO_ROAD_ADDR				AS TO_ROAD_ADDR,
			OI.TO_DETAIL_ADDR			AS TO_DETAIL_ADDR,
		";
	} else {
		$column_to_place[0] = "";
		$column_to_place[1] = "";
	}
	
	$insert_tmp_order_table_sql = "
		INSERT INTO
			".$order_table['info']."
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
			
			".$column_to_place[0]."
			
			CREATER,
			UPDATER
		)
		SELECT
			OI.country					AS COUNTRY,
			OI.ORDER_CODE				AS ORDER_CODE,
			'".$order_update_code."'	AS ORDER_UPDATE_CODE,
			OI.ORDER_TITLE				AS ORDER_TITLE,
			OI.ORDER_STATUS				AS ORDER_STATUS,
			
			OI.MEMBER_IDX				AS MEMBER_IDX,
			OI.MEMBER_ID				AS MEMBER_ID,
			OI.MEMBER_NAME				AS MEMBER_NAME,
			OI.MEMBER_MOBILE			AS MEMBER_MOBILE,
			OI.MEMBER_LEVEL				AS MEMBER_LEVEL,
			
			".$price_product."			AS PRICE_PRODUCT,
			".$price_delivery."			AS PRICE_DELIVERY,
			
			'".$housing_type."'			AS HOUSING_TYPE,
			'".$housing_company."'		AS HOUSING_COMPANY,
			'".$housing_num."'			AS HOUSING_NUM,
			NOW()						AS HOUSING_START_DATE,
			
			".$column_to_place[1]."
			
			OI.MEMBER_ID				AS CREATER,
			OI.MEMBER_ID				AS UPDATER
		FROM
			ORDER_INFO OI
		WHERE
			ORDER_CODE = '".$order_code."'
	";
	
	$db->query($insert_tmp_order_table_sql);
	
	$db_result = $db->last_id();
	if (!empty($db_result)) {
		return $order_update_code;
	}
}

function addTmpDelivery($db,$order_status,$order_code,$order_update_code,$delivery_code,$delivery_title,$price_delivery,$member_id) {
	$order_table = getOrderTable($order_status,true);
	
	$order_product_code = $order_code."-D";
	
	$insert_tmp_delivery_sql = "
		INSERT INTO
			".$order_table['product']."
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
			
			CREATER,
			UPDATER
		) VALUES (
			0,
			'".$order_code."',
			'".$order_product_code."',
			'".$order_update_code."',
			
			'PCP',
			
			0,
			'D',
			'DLVXXXXXXXXXXXXX',
			'주문 추가 배송비',
			
			0,
			'".$delivery_code."',
			'".$delivery_title."',
			
			1,
			".$price_delivery.",
			
			'".$member_id."',
			'".$member_id."'
		)
	";
	
	$db->query($insert_tmp_delivery_sql);
}

?>