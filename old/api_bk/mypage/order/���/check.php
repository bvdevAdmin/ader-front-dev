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

$housing_company = null;
if (isset($_POST['housing_company'])) {
	$housing_company = $_POST['housing_company'];
}

$housing_num = null;
if (isset($_POST['housing_num'])) {
	$housing_num = $_POST['housing_num'];
}

if ($country != null && $member_idx > 0 && $order_idx > 0 && $delivery_type != null) {
	$select_reason_idx_sql = "
		(
			SELECT
				'OEX'						AS ORDER_STATUS,
				S_TOE.ORDER_PRODUCT_CODE	AS ORDER_PRODUCT_CODE,
				S_TOE.REASON_DEPTH1_IDX		AS REASON_DEPTH1_IDX,
				S_TOE.REASON_DEPTH2_IDX		AS REASON_DEPTH2_IDX
			FROM
				TMP_ORDER_PRODUCT_EXCHANGE S_TOE
			WHERE
				S_TOE.ORDER_IDX = ".$order_idx."
		) UNION (
			SELECT
				'ORF'						AS ORDER_STATUS,
				S_TOR.ORDER_PRODUCT_CODE	AS ORDER_PRODUCT_CODE,
				S_TOR.REASON_DEPTH1_IDX		AS REASON_DEPTH1_IDX,
				S_TOR.REASON_DEPTH2_IDX		AS REASON_DEPTH1_IDX
			FROM
				TMP_ORDER_PRODUCT_REFUND S_TOR
			WHERE
				S_TOR.ORDER_IDX = ".$order_idx."
		)
	";
	
	$db->query($select_reason_idx_sql);
	
	$order_exchange_cnt = 0;
	$param_order_product_code = array();
	$reason_pg_cnt = 0;
	
	foreach($db->fetch() as $reason_data) {
		if ($reason_data['ORDER_STATUS'] == "OEX") {
			$order_exchange_cnt++;
		}
		
		array_push($param_order_product_code,$reason_data['ORDER_PRODUCT_CODE']);
		
		$depth1_idx = $reason_data['REASON_DEPTH1_IDX'];
		$depth2_idx = $reason_data['REASON_DEPTH2_IDX'];
		
		$depth1_pg_cnt = $db->count("REASON_DEPTH_1","IDX = ".$depth1_idx." AND REASON_TYPE = '".$reason_data['ORDER_STATUS']."' AND PG_FLG = TRUE");
		$depth2_pg_cnt = $db->count("REASON_DEPTH_2","IDX = ".$depth2_idx." AND DEPTH_1_IDX = ".$depth1_idx." AND REASON_TYPE = '".$reason_data['ORDER_STATUS']."' AND PG_FLG = TRUE");
		
		if ($depth1_pg_cnt > 0 && $depth2_pg_cnt > 0) {
			$reason_pg_cnt++;
		}
	}
	
	$order_info = getOrderInfoPriceByIdx($db,$order_idx);
	$price_total = intval($order_info['price_total']) + intval($order_info['price_mileage_point']);
	
	$reason_pg_flg = false;
	$delivery_price = 0;
	
	if ($delivery_type == "APL") {
		if ($reason_pg_cnt > 0) {
			$reason_pg_flg = true;
		}
		
		if ($reason_pg_flg == true) {
			//주문 교환/배송비가 발생 한 경우
			
			//1. 한국몰 주문 교환/반품 배송비 계산 (수거신청)
			//*교환 주문이 1건 이상 존재하는 경우 주문 교환 배송비로 계산
			if ($order_exchange_cnt > 0) {
				//1-1. 주문 교환 배송비 계산
				$delivery_price = 5000;
			} else {
				//1-2. 주문 환불 배송비 계산
				//전체/부분 환불 확인 (현재 진행중인 주문상품, 교환신청,교환완료,교환반려, 환불신청,환불완료,환불반려 를 제외한 주문상품 건수 체크)
				$refund_cnt = $db->count("ORDER_PRODUCT","ORDER_IDX = ".$order_idx." AND ORDER_STATUS NOT IN ('OEX','OEP','OEE','ORF','ORP','ORE') AND PRODUCT_TYPE NOT IN ('D','V') AND ORDER_PRODUCT_CODE NOT REGEXP '".implode("|",$param_order_product_code)."'");
				
				if ($price_total >= 80000) {
					//1-3. 총 결제금액 80000원 이상일 경우
					if ($refund_cnt > 0) {
						//1-3-1. 부분 환불
						$delivery_price = 2500;
					} else {
						//1-3-2. 전체 환불
						$delivery_price = 5000;
					}
				} else {
					//1-4. 총 결제금액 80000원 미만일 경우
					$delivery_price = 2500;
				}
			}
		} else {
			//주문 교환/배송비가 발생하지 않은 경우
			$delivery_price = 0;
		}
	} else if ($delivery_type == "DRC") {
		if ($country == "KR") {
			//주문 교환/배송비가 발생 여부 체크
				
			//2. 한국몰 주문 교환/반품 배송비 계산 (직접발송)
			//*교환 주문이 1건 이상 존재하는 경우 주문 교환 배송비로 계산
			if ($order_exchange_cnt > 0) {
				//2-1. 주문 교환 배송비 계산
				$delivery_price = 2500;
			} else {
				//2-2. 주문 환불 배송비 계산
				if ($price_total >= 80000) {
					$refund_cnt = $db->count("ORDER_PRODUCT","ORDER_IDX = ".$order_idx." AND ORDER_STATUS NOT IN ('OEX','OEP','OEE','ORF','ORP','ORE') AND PRODUCT_TYPE NOT IN ('D','V') AND ORDER_PRODUCT_CODE NOT REGEXP '".implode("|",$param_order_product_code)."'");
					
					if ($refund_cnt > 0) {
						$delivery_price = 0;
					} else {
						$delivery_price = 2500;
					}
				} else {
					$delivery_price = 0;
				}
			}
		}
		
		putOrderProductHousingInfo($db,$order_idx,$housing_company,$housing_num);
		
		if ($delivery_price > 0) {
			$reason_pg_flg = true;
		}
	}
	
	$order_product_code = null;
	if ($delivery_price > 0) {
		$product_num = $db->count("ORDER_PRODUCT","ORDER_IDX = ".$order_info['order_idx']);
		$order_product_code = $order_info['order_code']."_".($product_num + 1);;
		
		$insert_tmp_delivery_sql = "
			INSERT INTO
				TMP_ORDER_PRODUCT
			(
				ORDER_IDX,
				ORDER_CODE,
				ORDER_PRODUCT_CODE,
				ORDER_STATUS,
				
				PRODUCT_IDX,
				PRODUCT_TYPE,
				PRODUCT_CODE,
				PRODUCT_NAME,
				
				PRODUCT_QTY,
				PRODUCT_PRICE,
				
				CREATE_DATE,
				CREATER,
				UPDATE_DATE,
				UPDATER
			) VALUES (
				".$order_info['order_idx'].",
				'".$order_info['order_code']."',
				'".$order_product_code."',
				'PCP',
				
				0,
				'D',
				'DLVXXXXXXXXXXXX',
				'교환/반품 추가 배송비',
				
				0,
				".$delivery_price.",
				
				NOW(),
				'".$member_id."',
				NOW(),
				'".$member_id."'
			)
		";
		
		$db->query($insert_tmp_delivery_sql);
	}
	
	$json_result['data'] = array(
		'reason_pg_flg'			=>$reason_pg_flg,
		'order_product_code'	=>$order_product_code,
		'delivery_price'		=>$delivery_price
	);
	
	return $json_result;
}

function putOrderProductHousingInfo($db,$order_idx,$housing_company,$housing_num) {
	$exchange_cnt = $db->count("TMP_ORDER_PRODUCT_EXCHANGE","ORDER_IDX = ".$order_idx." AND HOUSING_COMPANY = 0 AND HOUSING_NUM IS NULL");
	if ($exchange_cnt > 0) {
		$update_tmp_order_product_exchange_sql = "
			UPDATE
				TMP_ORDER_PRODUCT_EXCHANGE
			SET
				HOUSING_COMPANY = '".$housing_company."',
				HOUSING_NUM = '".$housing_num."'
			WHERE
				ORDER_IDX = ".$order_idx."
		";
		
		$db->query($update_tmp_order_product_exchange_sql);
	}
	
	$refund_cnt = $db->count("TMP_ORDER_PRODUCT_REFUND","ORDER_IDX = ".$order_idx." AND HOUSING_COMPANY IS NULL AND HOUSING_NUM IS NULL");
	if ($refund_cnt > 0) {
		$update_tmp_order_product_refund_sql = "
			UPDATE
				TMP_ORDER_PRODUCT_REFUND
			SET
				HOUSING_COMPANY = '".$housing_company."',
				HOUSING_NUM = '".$housing_num."'
			WHERE
				ORDER_IDX = ".$order_idx."
		";
		
		$db->query($update_tmp_order_product_refund_sql);
	}
}

?>