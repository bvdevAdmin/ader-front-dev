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
	try {
		$order_cnt = $db->count("ORDER_INFO","IDX = ".$order_idx." AND COUNTRY = '".$country."' AND MEMBER_IDX = ".$member_idx);
		
		if ($order_cnt > 0) {
			$order_info = getOrderInfoPriceByIdx($db,$order_idx);
			
			if (count($order_product_info) > 0) {
				for ($i=0; $i<count($order_product_info); $i++) {
					//1. 현재 선택중인 상품 가격정보 조회
					$select_order_product_sql = "
						SELECT
							OP.IDX					AS ORDER_PRODUCT_IDX,
							OP.ORDER_PRODUCT_CODE	AS ORDER_PRODUCT_CODE,
							OP.PRODUCT_QTY			AS PRODUCT_QTY,
							OP.PRODUCT_PRICE		AS PRODUCT_PRICE
						FROM
							ORDER_PRODUCT OP
						WHERE
							OP.IDX = ".$order_product_info[$i]['order_product_idx']." AND
							OP.PARENT_IDX = 0 AND
							OP.PRODUCT_QTY > 0
							
					";
					
					$db->query($select_order_product_sql);
					
					$order_product = array();
					foreach($db->fetch() as $product_data) {
						$tmp_product_price = ($product_data['PRODUCT_PRICE'] / $product_data['PRODUCT_QTY']);
						$product_price = ($tmp_product_price * $order_product_info[$i]['product_qty']);
						
						$order_product = array(
							'order_product_idx'		=>$product_data['ORDER_PRODUCT_IDX'],
							'order_product_code'	=>$product_data['ORDER_PRODUCT_CODE'],
							'product_qty'			=>$order_product_info[$i]['product_qty'],
							'product_price'			=>$product_price
						);
					}
					
					//2-1. 주문취소 환불가격 조회
					$cancel_total = getOrderTableTotalRefundPrice($db,"OCC",$order_info['order_idx']);
					$refundable_price = ($order_info['price_total'] - $cancel_total) - $order_product['product_price'];
					
					//4-2. 할인취소금액(바우처)
					$discount_price = 0;
					if ($order_info['price_discount'] > 0) {
						$discount_price = (($order_product['product_price'] / $order_info['price_product']) * $order_info['price_discount']);
					}
					
					//4-3. 적립금환불
					$mileage_price = 0;
					if ($order_product['product_price'] > $refundable_price) {
						$mileage_price = ($order_product['product_price'] / $order_info['price_product']) * $order_info['price_mileage_point'];
					}
					
					$delivery_price = 0;
					$refund_price = 0 ;
					
					if ($order_product['product_price'] > ($discount_price + $mileage_price)) {
						$refund_price = $order_product['product_price'] - $discount_price - $mileage_price;
					} else {
						$refund_price = $mileage_price;
					}
					
					$refundable_cnt = $db->count("ORDER_PRODUCT","IDX != ".$order_product['order_product_idx']." AND ORDER_IDX = ".$order_info['order_idx']." AND PRODUCT_TYPE NOT IN ('V','D') AND PARENT_IDX = 0 AND PRODUCT_QTY > 0");
					$cancel_delivery = $db->count("ORDER_PRODUCT_CANCEL","ORDER_IDX = ".$order_info['order_idx']." AND DELIVERY_PRICE > 0");
					
					if ($refundable_cnt == 0 && $cancel_delivery > 0) {
						$delivery_price = -2500;
						
						$refund_price += 2500;
					} else if ($refundable_cnt > 0 && $refundable_price < 80000 && $cancel_delivery == 0) {
						$delivery_price = 2500;
						
						$refund_price -= 2500;
					}
					
					$pg_cancel_info = array();
					if ($refund_price > 0) {
						$refund_price_info = array(
							'order_product_idx'		=>$order_product['order_product_idx'],
							'order_product_code'	=>$order_product['order_product_code'],
							'product_qty'			=>$order_product['product_qty'],
							'product_price'			=>$order_product['product_price'],
							
							'discount_price'		=>$discount_price,
							'mileage_price'			=>$mileage_price,
							'charge_price'			=>0,
							'delivery_price'		=>$delivery_price,
							'refund_price'			=>$refund_price
						);
						
						if ($order_info['pg_payment_key'] != null) {
							$pg_cancel_info = orderPgCancel($db,$order_info['pg_payment_key'],$refund_price);
							
							if (count($pg_cancel_info) > 0) {
								$db_result = addOrderProductCancelInfo($db,$pg_cancel_info,$refund_price_info,$reason_depth1_idx,$reason_depth2_idx,$reason_memo,$member_id);
								if (!empty($db_result)) {
									//putOrderInfoByStatus($db,$order_idx,$refund_price_info,$member_id);
									putOrderProductByOrderProductInfo($db,$refund_price_info['order_product_idx'],$refund_price_info['product_qty']);
								}
							}
						} else {
							$db_result = addOrderProductCancelInfo($db,null,$refund_price_info,$reason_depth1_idx,$reason_depth2_idx,$reason_memo,$member_id);
							if (!empty($db_result)) {
								//putOrderInfoByStatus($db,$order_idx,$refund_price_info,$member_id);
								putOrderProductByOrderProductInfo($db,$refund_price_info['order_product_idx'],$refund_price_info['product_qty']);
							}
						}
					}
				}
			} else {
				$json_result['code'] = 304;
				$json_result['msg'] = "취소하려는 상품의 정보가 존재하지 않습니다.";
				
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
		$json_result['msg'] = "주문 취소처리중 오류가 발생했습니다.";
	}
} else {
	$json_result['code'] = 301;
	$json_result['msg'] = "부적절한 주문 정보가 선택되었습니다. 취소/환불 하려는 주문을 다시 선택해주세요.";
	
	return $json_result;
}

//PG사 결제취소처리
function orderPgCancel($db,$param_payment_key,$refund_price) {	
	$pg_cancel_info = array();
	
	$curl = curl_init();

	curl_setopt_array($curl, [
		CURLOPT_URL => "https://api.tosspayments.com/v1/payments/".$param_payment_key."/cancel",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => "{\"cancelReason\":\"주문취소\",\"cancelAmount\":".$refund_price."}",
		CURLOPT_HTTPHEADER => [
			"Authorization: Basic dGVzdF9za19ONU9XUmFwZEE4ZFkyMTc1N2piM28xekVxWktMOg==",
			"Content-Type: application/json"
		],
	]);

	$response = curl_exec($curl);
	$err = curl_error($curl);
	
	curl_close($curl);
	
	if (!$err) {
		$result = json_decode($response,true);
		
		$pg_payment_key = null;
		if (isset($result['paymentKey'])) {
			$pg_payment_key = $result['paymentKey'];
		}
		
		if ($pg_payment_key != null) {
			$pg_mid = null;
			if (isset($result['mId'])) {
				$pg_mid = $result['mId'];
			}
			
			$pg_payment = null;
			if (isset($result['method'])) {
				$pg_payment = $result['method'];
			}
			
			$pg_status = null;
			if (isset($result['status'])) {
				$pg_status = $result['status'];
			}
			
			$pg_currency = null;
			if (isset($result['currency'])) {
				$pg_currency = $result['currency'];
			}
			
			$pg_cancel = null;
			if (isset($result['cancels'])) {
				$cancels = $result['cancels'];
				$pg_cancel = $cancels[count($cancels) - 1];
			}
			
			$pg_price = null;
			if (isset($pg_cancel['cancelAmount'])) {
				$pg_price = $pg_cancel['cancelAmount'];
			}
			
			$pg_date = null;
			if (isset($pg_cancel['canceledAt'])) {
				$pg_date = $pg_cancel['canceledAt'];
			}
			
			$pg_receipt_url = null;
			if (isset($pg_canceels['receiptKey'])) {
				$pg_receipt_url = $pg_canceels['receiptKey'];
			}
			
			$pg_cancel_info = array(
				'pg_mid'			=>$pg_mid,
				'pg_payment'		=>$pg_payment,
				'pg_payment_key'	=>$pg_payment_key,
				'pg_status'			=>$pg_status,
				'pg_date'			=>$pg_date,
				'pg_price'			=>$pg_price,
				'pg_currency'		=>$pg_currency,
				'pg_receipt_url'	=>$pg_receipt_url
			);
		}
	} else {
		print_r($err);
	}
	
	return $pg_cancel_info;
}

function addOrderProductCancelInfo($db,$pg_cancel_info,$refund_price_info,$reason_depth1_idx,$reason_depth2_idx,$reason_memo,$member_id) {
	$pg_cancel_info_column_sql = "";
	$pg_cancel_info_sql = "";
	
	if ($pg_cancel_info != null) {
		$pg_cancel_info_column_sql = "
			PG_CANCEL_DATE,
			
			PG_MID,
			PG_PAYMENT,
			PG_PAYMENT_KEY,
			PG_STATUS,
			PG_DATE,
			PG_PRICE,
			PG_CURRENCY,
			PG_RECEIPT_URL,
		";
		
		$pg_cancel_info_sql = "
			'".$pg_cancel_info['pg_date'].",'				AS PG_CANCEL_DATE,
				
			'".$pg_cancel_info['pg_mid']."'					AS PG_MID,
			'".$pg_cancel_info['pg_payment']."'				AS PG_PAYMENT,
			'".$pg_cancel_info['pg_payment_key']."'			AS PG_PAYMENT_KEY,
			'".$pg_cancel_info['pg_status']."'				AS PG_STATUS,
			'".$pg_cancel_info['pg_date']."'				AS PG_DATE,
			'".$pg_cancel_info['pg_price']."'				AS PG_PRICE,
			'".$pg_cancel_info['pg_currency']."'			AS PG_CURRENCY,
			'".$pg_cancel_info['pg_receipt_url']."'			AS PG_RECEIPT_URL,
		";
	} else {
		$pg_cancel_info_column_sql = "
			PG_CANCEL_DATE,
			
			PG_PAYMENT,
			PG_DATE,
			PG_PRICE,
		";
		
		$pg_cancel_info_sql = "
			NOW()											AS PG_CANCEL_DATE,
				
			'적립금'											AS PG_PAYMENT,
			NOW()											AS PG_DATE,
			".$refund_price_info['refund_price']."			AS PG_PRICE,
		";
	}
	
	$insert_order_product_cancel_sql = "
		INSERT INTO
			ORDER_PRODUCT_CANCEL
		(
			ORDER_IDX,
			ORDER_CODE,
			ORDER_PRODUCT_CODE,
			ORDER_STATUS,
			
			CANCEL_DATE,
			
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
			
			DISCOUNT_PRICE,
			MILEAGE_PRICE,
			DELIVERY_PRICE,
			REFUND_PRICE,
			
			".$pg_cancel_info_column_sql."
			
			REASON_DEPTH1_IDX,
			REASON_DEPTH2_IDX,
			REASON_MEMO,
			
			CREATE_DATE,
			CREATER,
			UPDATE_DATE,
			UPDATER
		)
		SELECT
			OP.ORDER_IDX									AS ORDER_IDX,
			OP.ORDER_CODE									AS ORDER_CODE,
			OP.ORDER_PRODUCT_CODE							AS ORDER_PRODUCT_CODE,
			'OCC'											AS ORDER_PRODUCT_CODE,
			
			NOW()											AS CANCEL_DATE,
			
			OP.PRODUCT_IDX									AS PRODUCT_IDX,
			OP.PRODUCT_TYPE									AS PRODUCT_TYPE,
			OP.REORDER_CNT									AS REORDER_CNT,
			OP.PREORDER_FLG									AS PREORDER_FLG,
			OP.PRODUCT_CODE									AS PRODUCT_CODE,
			OP.PRODUCT_NAME									AS PRODUCT_NAME,
			
			OP.OPTION_IDX									AS OPTION_IDX,
			OP.BARCODE										AS BARCODE,
			OP.OPTION_NAME									AS OPTION_NAME,
			
			".$refund_price_info['product_qty']."			AS PRODUCT_QTY,
			".$refund_price_info['product_price']."			AS PRODUCT_PRICE,
			
			".$refund_price_info['discount_price']."		AS DISCOUNT_PRICE,
			".$refund_price_info['mileage_price']."			AS MILEAGE_PRICE,
			".$refund_price_info['delivery_price']."		AS DELIVERY_PRICE,
			".$refund_price_info['refund_price']."			AS REFUND_PRICE,
			
			".$pg_cancel_info_sql."
			
			".$reason_depth1_idx."							AS REASON_DEPTH1_IDX,
			".$reason_depth2_idx."							AS REASON_DEPTH2_IDX,
			".$reason_memo."								AS REASON_MEMO,
			
			NOW()											AS CREATE_DATE,
			'".$member_id."'								AS CREATER,
			NOW()											AS UPDATE_DATE,
			'".$member_id."'								AS UPDATER
		FROM
			ORDER_PRODUCT OP
		WHERE
			IDX = ".$refund_price_info['order_product_idx']."
	";
	
	$db->query($insert_order_product_cancel_sql);
	
	return $db->last_id();
}

?>