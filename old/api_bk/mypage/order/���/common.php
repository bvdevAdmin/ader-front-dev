<?php
/*
 +=============================================================================
 | 
 | 마이페이지_주문조회화면 - 공통
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

//주문상태코드 변환
function setTxtOrderStatus($param) {
	$order_status = '';
	$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}
	switch ($param) {
		case "PCP" :
			switch ($country) {
				case "KR" :
					$order_status = "결제완료";
					break;
				
				case "EN" :
					$order_status = "Payment Completed";
					break;
				
				case "CN" :
					$order_status = "支付完成";
					break;
			}
			break;
		
		case "PPR" :
			switch ($country) {
				case "KR" :
					$order_status = "상품준비";
					break;
				
				case "EN" :
					$order_status = "Product Preparation";
					break;
				
				case "CN" :
					$order_status = "商品准备";
					break;
			}
			break;
		
		case "POP" :
			switch ($country) {
				case "KR" :
					$order_status = "프리오더 준비";
					break;
				
				case "EN" :
					$order_status = "Pre-order Preparation";
					break;
				
				case "CN" :
					$order_status = "预购准备";
					break;
			}
			break;
		
		case "POD" :
			switch ($country) {
				case "KR" :
					$order_status = "프리오더 상품 생산";
					break;
				
				case "EN" :
					$order_status = "Pre-order Product Production";
					break;
				
				case "CN" :
					$order_status = "预购商品生产";
					break;
			}
			break;
		
		case "DPR" :
			switch ($country) {
				case "KR" :
					$order_status = "배송준비";
					break;
				
				case "EN" :
					$order_status = "Shipping Preparation";
					break;
				
				case "CN" :
					$order_status = "配送准备";
					break;
			}
			break;
		
		case "DPG" :
			switch ($country) {
				case "KR" :
					$order_status = "배송중";
					break;
				
				case "EN" :
					$order_status = "Shipping in Progress";
					break;
				
				case "CN" :
					$order_status = "运送中";
					break;
			}
			break;
		
		case "DCP" :
			switch ($country) {
				case "KR" :
					$order_status = "배송완료";
					break;
				
				case "EN" :
					$order_status = "Shipping Completed";
					break;
				
				case "CN" :
					$order_status = "运送完成";
					break;
			}
			break;
		
		case "OCC" :
			switch ($country) {
				case "KR" :
					$order_status = "주문취소";
					break;
				
				case "EN" :
					$order_status = "Order Canceled";
					break;
				
				case "CN" :
					$order_status = "订单取消";
					break;
			}
			break;
		
		case "OEX" :
			switch ($country) {
				case "KR" :
					$order_status = "주문교환접수";
					break;
				
				case "EN" :
					$order_status = "Order Exchange Requested";
					break;
				
				case "CN" :
					$order_status = "订单换货申请";
					break;
			}
			break;
		
		case "OEP" :
			switch ($country) {
				case "KR" :
					$order_status = "주문교환완료";
					break;
				
				case "EN" :
					$order_status = "Order Exchange Completed";
					break;
				
				case "CN" :
					$order_status = "订单换货完成";
					break;
			}
			break;
		
		case "OEE" :
			switch ($country) {
				case "KR" :
					$order_status = "주문교환반려";
					break;
				
				case "EN" :
					$order_status = "Order Exchange Rejected";
					break;
				
				case "CN" :
					$order_status = "订单换货拒绝";
					break;
			}
			break;
		
		case "ORF" :
			switch ($country) {
				case "KR" :
					$order_status = "주문반품접수";
					break;
				
				case "EN" :
					$order_status = "Order Return Requested";
					break;
				
				case "CN" :
					$order_status = "订单退货申请";
					break;
			}
			break;
		
		case "ORP" :
			switch ($country) {
				case "KR" :
					$order_status = "주문반품완료";
					break;
				
				case "EN" :
					$order_status = "Order Return Completed";
					break;
				
				case "CN" :
					$order_status = "订单退货完成";
					break;
			}
			break;
		
		case "ORE" :
			switch ($country) {
				case "KR" :
					$order_status = "주문반품반려";
					break;
				
				case "EN" :
					$order_status = "Order Return Rejected";
					break;
				
				case "CN" :
					$order_status = "订单退货拒绝";
					break;
			}
			break;
	}
	
	return $order_status;
}

function deleteTmpOrderProduct($db,$order_idx) {
	$db->query("DELETE FROM TMP_ORDER_PRODUCT_EXCHANGE WHERE ORDER_IDX = ".$order_idx);
	$db->query("DELETE FROM TMP_ORDER_PRODUCT_REFUND WHERE ORDER_IDX = ".$order_idx);
}

function getOrderProductInfoByStatus($db,$order_status,$order_idx,$select_type,$tmp_flg) {
	$order_table = null;
	$column_order_status = null;
	
	$select_prev_option_name = " NULL		AS PREV_OPTION_NAME, ";
	
switch ($order_status) {
		case "PRD" :
			$order_table = "ORDER_PRODUCT";
			break;
			
		case "OCC" :
			$order_table = "ORDER_PRODUCT_CANCEL";
			break;
		
		case "OEX" :
			if ($tmp_flg == false) {
				$order_table = "ORDER_PRODUCT_EXCHANGE";
			} else if ($tmp_flg == true) {
				$order_table = "TMP_ORDER_PRODUCT_EXCHANGE";
			}
			
			$select_prev_option_name = "
				(
					SELECT
						S_OO.OPTION_NAME
					FROM
						ORDERSHEET_OPTION S_OO
					WHERE
						S_OO.IDX = OT.PREV_OPTION_IDX
				)		AS PREV_OPTION_NAME,
			";
			
			break;
		
		case "ORF" :
			if ($tmp_flg == false) {
				$order_table = "ORDER_PRODUCT_REFUND";
			} else if ($tmp_flg == true) {
				$order_table = "TMP_ORDER_PRODUCT_REFUND";
			}
			
			break;
	}
	
	$order_table_info = array();
	if (strlen($order_table) > 0) {
		$select_order_table_sql = "";
		if ($select_type == "IND") {
			$select_order_table_sql = "
				SELECT
					OT.IDX					AS ORDER_PRODUCT_IDX,
					OT.ORDER_PRODUCT_CODE	AS ORDER_PRODUCT_CODE,
					OT.ORDER_STATUS			AS ORDER_STATUS,
					OT.PRODUCT_TYPE			AS PRODUCT_TYPE,
					(
						SELECT
							S_PI.IMG_LOCATION
						FROM
							PRODUCT_IMG S_PI
						WHERE
							S_PI.PRODUCT_IDX = PR.IDX AND
							S_PI.IMG_TYPE = 'P' AND
							S_PI.IMG_SIZE = 'S'
						ORDER BY
							S_PI.IDX ASC
						LIMIT
							0,1
					)						AS IMG_LOCATION,
					OT.PRODUCT_NAME			AS PRODUCT_NAME,
					PR.COLOR				AS COLOR,
					PR.COLOR_RGB			AS COLOR_RGB,
					OT.OPTION_NAME			AS OPTION_NAME,
					".$select_prev_option_name."
					OT.PRODUCT_QTY			AS PRODUCT_QTY,
					(
						OT.PRODUCT_PRICE / OT.PRODUCT_QTY
					)						AS PRODUCT_PRICE
				FROM
					".$order_table." OT
					LEFT JOIN SHOP_PRODUCT PR ON
					OT.PRODUCT_IDX = PR.IDX
				WHERE
					OT.ORDER_IDX = ".$order_idx." AND
					OT.PRODUCT_CODE NOT REGEXP 'DLVXXX|VOUXXX' AND
					OT.PARENT_IDX = 0 AND
					OT.PRODUCT_QTY > 0
				ORDER BY
					OT.IDX ASC
			";
		} else if ($select_type == "SUM") {
			$select_order_table_sql = "
				SELECT
					OT.IDX					AS ORDER_PRODUCT_IDX,
					OT.ORDER_PRODUCT_CODE	AS ORDER_PRODUCT_CODE,
					OT.ORDER_STATUS			AS ORDER_STATUS,
					OT.PRODUCT_TYPE			AS PRODUCT_TYPE,
					(
						SELECT
							S_PI.IMG_LOCATION
						FROM
							PRODUCT_IMG S_PI
						WHERE
							S_PI.PRODUCT_IDX = PR.IDX AND
							S_PI.IMG_TYPE = 'P' AND
							S_PI.IMG_SIZE = 'S'
						ORDER BY
							S_PI.IDX ASC
						LIMIT
							0,1
					)						AS IMG_LOCATION,
					OT.PRODUCT_NAME			AS PRODUCT_NAME,
					PR.COLOR				AS COLOR,
					PR.COLOR_RGB			AS COLOR_RGB,
					OT.OPTION_NAME			AS OPTION_NAME,
					".$select_prev_option_name."
					SUM(OT.PRODUCT_QTY)		AS PRODUCT_QTY,
					SUM(OT.PRODUCT_PRICE)	AS PRODUCT_PRICE
				FROM
					".$order_table." OT
					LEFT JOIN SHOP_PRODUCT PR ON
					OT.PRODUCT_IDX = PR.IDX
				WHERE
					OT.ORDER_IDX = ".$order_idx." AND
					OT.PRODUCT_CODE NOT REGEXP 'DLVXXX|VOUXXX' AND
					OT.PARENT_IDX = 0 AND
					OT.PRODUCT_QTY > 0
				GROUP BY
					OT.ORDER_PRODUCT_CODE
				ORDER BY
					OT.IDX ASC
			";
		}
		
		$db->query($select_order_table_sql);
		
		foreach($db->fetch() as $table_data) {
			$order_product_idx = $table_data['ORDER_PRODUCT_IDX'];
			$product_type = $table_data['PRODUCT_TYPE'];
			$product_qty = $table_data['PRODUCT_QTY'];
			$product_price = $table_data['PRODUCT_PRICE'];
			
			$set_product_info = array();
			if (!empty($order_product_idx) && $product_type == "S") {
				$set_product_info = getSetProductInfo($db,$order_product_idx);
			}
			
			if ($order_status == "PRD") {
				$product_price = ($product_price / $product_qty);
				
				$exchange_cnt = $db->count("TMP_ORDER_PRODUCT_EXCHANGE","ORDER_PRODUCT_CODE = '".$table_data['ORDER_PRODUCT_CODE']."'");
				$refund_cnt = $db->count("TMP_ORDER_PRODUCT_REFUND","ORDER_PRODUCT_CODE = '".$table_data['ORDER_PRODUCT_CODE']."'");
				
				$product_qty = ($product_qty - $exchange_cnt - $refund_cnt);
				$product_price = ($product_price * $product_qty);
			}
			
			if ($product_qty > 0) {
				if ($select_type == "IND") {
					if ($product_qty > 1) {
						for ($i=0; $i<$product_qty; $i++) {
							$order_table_info[] = array(
								'order_product_idx'		=>$order_product_idx,
								'order_product_code'	=>$table_data['ORDER_PRODUCT_CODE'],
								'order_status'			=>$table_data['ORDER_STATUS'],
								'txt_order_status'		=>setTxtOrderStatus($table_data['ORDER_STATUS']),
								'product_type'			=>$product_type,
								'img_location'			=>$table_data['IMG_LOCATION'],
								'product_name'			=>$table_data['PRODUCT_NAME'],
								'color'					=>$table_data['COLOR'],
								'color_rgb'				=>$table_data['COLOR_RGB'],
								'option_name'			=>$table_data['OPTION_NAME'],
								'prev_option_name'		=>$table_data['PREV_OPTION_NAME'],
								'product_qty'			=>1,
								'product_price'			=>number_format($product_price),
								
								'set_product_info'		=>$set_product_info
							);
						}
					} else {
						$order_table_info[] = array(
							'order_product_idx'		=>$order_product_idx,
							'order_product_code'	=>$table_data['ORDER_PRODUCT_CODE'],
							'order_status'			=>$table_data['ORDER_STATUS'],
							'txt_order_status'		=>setTxtOrderStatus($table_data['ORDER_STATUS']),
							'product_type'			=>$product_type,
							'img_location'			=>$table_data['IMG_LOCATION'],
							'product_name'			=>$table_data['PRODUCT_NAME'],
							'color'					=>$table_data['COLOR'],
							'color_rgb'				=>$table_data['COLOR_RGB'],
							'option_name'			=>$table_data['OPTION_NAME'],
							'prev_option_name'		=>$table_data['PREV_OPTION_NAME'],
							'product_qty'			=>$product_qty,
							'product_price'			=>number_format($product_price),
							
							'set_product_info'		=>$set_product_info
						);
					}
				} else if ($select_type == "SUM") {
					if ($product_qty > 0) {
						$order_table_info[] = array(
							'order_product_idx'		=>$order_product_idx,
							'order_product_code'	=>$table_data['ORDER_PRODUCT_CODE'],
							'order_status'			=>$table_data['ORDER_STATUS'],
							'txt_order_status'		=>setTxtOrderStatus($table_data['ORDER_STATUS']),
							'img_location'			=>$table_data['IMG_LOCATION'],
							'product_type'			=>$product_type,
							'product_name'			=>$table_data['PRODUCT_NAME'],
							'color'					=>$table_data['COLOR'],
							'color_rgb'				=>$table_data['COLOR_RGB'],
							'option_name'			=>$table_data['OPTION_NAME'],
							'prev_option_name'		=>$table_data['PREV_OPTION_NAME'],
							'product_qty'			=>$product_qty,
							'product_price'			=>number_format($table_data['PRODUCT_PRICE']),
							
							'set_product_info'		=>$set_product_info
						);
					}
				}
			}
		}
	}
	
	return $order_table_info;
}

function getSetProductInfo($db,$order_product_idx) {
	$set_product_info = array();
	
	$select_set_product_sql = "
		SELECT
			OP.PARENT_IDX					AS PARENT_IDX,
			(
				SELECT
					S_PI.IMG_LOCATION
				FROM
					PRODUCT_IMG S_PI
				WHERE
					S_PI.PRODUCT_IDX = OP.PRODUCT_IDX AND
					S_PI.IMG_TYPE = 'P' AND
					S_PI.IMG_SIZE = 'S'
				ORDER BY
					S_PI.IDX ASC
				LIMIT
					0,1
			)								AS IMG_LOCATION,
			OP.PRODUCT_NAME					AS PRODUCT_NAME,
			PR.COLOR						AS COLOR,
			PR.COLOR_RGB					AS COLOR_RGB,
			OP.OPTION_NAME					AS OPTION_NAME
		FROM
			ORDER_PRODUCT OP
			LEFT JOIN SHOP_PRODUCT PR ON
			OP.PRODUCT_IDX = PR.IDX
		WHERE
			OP.PARENT_IDX = ".$order_product_idx."
	";
	
	$db->query($select_set_product_sql);
	
	foreach($db->fetch() as $set_data) {
		$set_product_info[] = array(
			'parent_idx'		=>$set_data['PARENT_IDX'],
			'img_location'		=>$set_data['IMG_LOCATION'],
			'product_name'		=>$set_data['PRODUCT_NAME'],
			'color'				=>$set_data['COLOR'],
			'color_rgb'			=>$set_data['COLOR_RGB'],
			'option_name'		=>$set_data['OPTION_NAME']
		);
	}
	
	return $set_product_info;
}

//취소/교환/반품 하려는 주문의 정보 취득
function getOrderInfoPriceByIdx($db,$order_idx) {
	$order_info = array();
	
	//1. 상품주문금액,택배비,할인금액,적립금사용금액 조회
	$select_order_info_sql = "
		SELECT
			OI.IDX						AS ORDER_IDX,
			OI.COUNTRY					AS COUNTRY,
			OI.ORDER_CODE				AS ORDER_CODE,
			OI.PRICE_PRODUCT			AS PRICE_PRODUCT,
			OI.PRICE_DELIVERY			AS PRICE_DELIVERY,
			OI.PRICE_DISCOUNT			AS PRICE_DISCOUNT,
			OI.PRICE_MILEAGE_POINT		AS PRICE_MILEAGE_POINT,
			OI.PRICE_TOTAL				AS PRICE_TOTAL,
			OI.PG_PAYMENT_KEY			AS PG_PAYMENT_KEY
		FROM
			ORDER_INFO OI
		WHERE
			OI.IDX = ".$order_idx."
	";
	
	$db->query($select_order_info_sql);
	
	foreach($db->fetch() as $info_data) {
		$order_info = array(
			'order_idx'				=>$info_data['ORDER_IDX'],
			'country'				=>$info_data['COUNTRY'],
			'order_code'			=>$info_data['ORDER_CODE'],
			'price_product'			=>$info_data['PRICE_PRODUCT'],
			'price_delivery'		=>$info_data['PRICE_DELIVERY'],
			'price_discount'		=>$info_data['PRICE_DISCOUNT'],
			'price_mileage_point'	=>$info_data['PRICE_MILEAGE_POINT'],
			'price_total'			=>$info_data['PRICE_TOTAL'],
			'pg_payment_key'		=>$info_data['PG_PAYMENT_KEY']
		);
	}
	
	return $order_info;
}

function getOrderInfoByOrderProductCode($db,$order_product_code) {
	$order_info = array();
	
	//1. 상품주문금액,택배비,할인금액,적립금사용금액 조회
	$select_order_info_sql = "
		SELECT
			OI.IDX						AS ORDER_IDX,
			OI.ORDER_CODE				AS ORDER_CODE
		FROM
			ORDER_INFO OI
		WHERE
			OI.IDX = (
				SELECT
					S_TOP.ORDER_IDX
				FROM
					TMP_ORDER_PRODUCT S_TOP
				WHERE
					S_TOP.ORDER_PRODUCT_CODE = '".$order_product_code."'
				ORDER BY
					S_TOP.IDX DESC
				LIMIT
					0,1
			)
	";
	
	$db->query($select_order_info_sql);
	
	foreach($db->fetch() as $info_data) {
		$order_info = array(
			'order_idx'				=>$info_data['ORDER_IDX'],
			'order_code'			=>$info_data['ORDER_CODE']
		);
	}
	
	return $order_info;
}

//주문상품 취소/교환/반품 정보 취득
function getOrderTableTotalRefundPrice($db,$order_status,$order_idx) {
	$order_table = null;
	switch ($order_status) {
		case "OCC" :
			$order_table = "ORDER_PRODUCT_CANCEL";
			break;
		
		case "ORF" :
			$order_table = "ORDER_PRODUCT_REFUND";
			break;
	}
	
	$total_refund_price = 0;
	if ($order_table != null) {
		$select_order_table_sql = "
			SELECT
				IFNULL(
					SUM(OT.REFUND_PRICE),0
				)		AS REFUND_PRICE
			FROM
				".$order_table." OT
			WHERE
				OT.ORDER_IDX = ".$order_idx."
		";
		
		$db->query($select_order_table_sql);
		
		foreach($db->fetch() as $table_data) {
			$total_refund_price = $table_data['REFUND_PRICE'];
		}
	}
	
	return $total_refund_price;
}

//취소/교환/반품시 주문상 정보 갱신
function putOrderInfoByStatus($db,$order_idx,$refund_price_info,$member_id) {
	$update_order_info_sql = "
		UPDATE
			ORDER_INFO
		SET
			PRICE_PRODUCT = PRICE_PRODUCT - ".$refund_price_info['product_price'].",
			PRICE_DELIVERY = PRICE_DELIVERY + ".$refund_price_info['delivery_price'].",
			PRICE_MILEAGE_POINT = PRICE_MILEAGE_POINT -  ".$refund_price_info['mileage_price'].",
			PRICE_DISCOUNT = PRICE_DISCOUNT - ".$refund_price_info['discount_price'].",
			PRICE_TOTAL = PRICE_TOTAL - ".$refund_price_info['refund_price'].",
			UPDATE_DATE = NOW(),
			UPDATER = '".$member_id."'
		WHERE
			IDX = ".$order_idx."
	";
	
	$db->query($update_order_info_sql);
}

//취소/교환/반품시 주문상품 정보 갱신
function putOrderProductByOrderProductInfo($db,$order_product_idx,$product_qty) {
	$update_order_product_sql = "
		UPDATE
			ORDER_PRODUCT
		SET
			PRODUCT_PRICE = ((PRODUCT_PRICE / PRODUCT_QTY) * ".$product_qty."),
			PRODUCT_QTY = PRODUCT_QTY - ".$product_qty."
		WHERE
			IDX = ".$order_product_idx."
	";
	
	$db->query($update_order_product_sql);
}

//PG사 추가 배송비 결제처리
function orderPgDeliveryPrice($db,$order_product_code,$payment_key,$delivery_price) {
	$curl = curl_init();

	curl_setopt_array($curl, [
		CURLOPT_URL => "https://api.tosspayments.com/v1/payments/confirm",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => "
			{
				\"orderId\":\"".$order_product_code."\",
				\"paymentKey\":\"".$payment_key."\",
				\"amount\":".$delivery_price."
			}
		",
		CURLOPT_HTTPHEADER => [
			"Authorization: Basic dGVzdF9za19ONU9XUmFwZEE4ZFkyMTc1N2piM28xekVxWktMOg==",
			"Content-Type: application/json"
		],
	]);
	
	$response = curl_exec($curl);
	$err = curl_error($curl);
	
	if (!$err) {
		$result = json_decode($response);
		
		$pg_payment_key = null;
		if (isset($result->paymentKey)) {
			$pg_payment_key = $result->paymentKey;
		}
		 
		$pg_mid = null;
		if (isset($result->mId)) {
			$pg_mid = $result->mId;
		}
		
		$pg_payment = null;
		if (isset($result->method)) {
			$pg_payment = $result->method;
		}

		$pg_status = null;
		if (isset($result->status)) {
			$pg_status = $result->status;
		}
		
		$pg_price = null;
		if (isset($result->totalAmount)) {
			$pg_price = $result->totalAmount;
		}

		$pg_currency = null;
		if (isset($result->currency)) {
			$pg_currency = $result->currency;
		}

		$pg_receipt_url = null;
		if (isset($result->receipt)) {
			$receipt = $result->receipt;
			if ($receipt != null) {
				$pg_receipt_url = $receipt->url;
			}
		}
		
		$insert_order_product_delivery_price_sql = "
			INSERT INTO
				ORDER_PRODUCT
			(
				ORDER_IDX,
				ORDER_CODE,
				ORDER_PRODUCT_CODE,
				ORDER_STATUS,
				
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
				
				PG_MID,
				PG_PAYMENT,
				PG_PAYMENT_KEY,
				PG_STATUS,
				PG_DATE,
				PG_PRICE,
				PG_CURRENCY,
				PG_RECEIPT_URL,
				
				CREATE_DATE,
				CREATER,
				UPDATE_DATE,
				UPDATER
			)
			SELECT
				TOP.ORDER_IDX				AS ORDER_IDX,
				TOP.ORDER_CODE				AS ORDER_CODE,
				TOP.ORDER_PRODUCT_CODE		AS ORDER_PRODUCT_CODE,
				TOP.ORDER_STATUS			AS ORDER_STATUS,
				
				TOP.PRODUCT_IDX				AS PRODUCT_IDX,
				TOP.PRODUCT_TYPE			AS PRODUCT_TYPE,
				TOP.REORDER_CNT				AS PRODUCT_CNT,
				TOP.PREORDER_FLG			AS PREORDER_FLG,
				TOP.PRODUCT_CODE			AS PRODUCT_CODE,
				TOP.PRODUCT_NAME			AS PRODUCT_NAME,
				
				TOP.OPTION_IDX				AS OPTION_IDX,
				TOP.BARCODE					AS BARCODE,
				TOP.OPTION_NAME				AS OPTION_NAME,
				
				TOP.PRODUCT_QTY				AS PRODUCT_QTY,
				TOP.PRODUCT_PRICE			AS PRODUCT_PRICE,
				
				'".$pg_mid."'				AS PG_MID,
				'".$pg_payment."'			AS PG_PAYMENT,
				'".$pg_payment_key."'		AS PG_PAYMENT_KEY,
				'".$pg_status."'			AS PG_STATUS,
				'".$pg_date."'				AS PG_DATE,
				'".$pg_price."'				AS PG_PRICE,
				'".$pg_currency."'			AS PG_CURRENCY,
				'".$pg_receipt_url."'		AS PG_RECEIPT_URL,
				
				TOP.CREATE_DATE				AS CREATE_DATE,
				TOP.CREATER					AS CREATER,
				TOP.UPDATE_DATE				AS UPDATE_DATE,
				TOP.UPDATER					AS UPDATER	
			FROM
				TMP_ORDER_PRODUCT TOP
			WHERE
				ORDER_PRODUCT_CODE = '".$order_product_code."'
		";
		
		$db->query($insert_order_product_delivery_price_sql);
	} else {
		$json_result['code'] = 301;
		$json_result['msg'] = "주문상품 반품배송비 결제처리에 실패했습니다. 반품하려는 상품을 다시 선택해주세요.";
	}
}

?>