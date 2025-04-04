<?php
/*
 +=============================================================================
 | 
 | 마이페이지_주문조회화면 - PG사 결제 관련 공통함수
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

//[주문 정보 테이블] PG사 결제정보 취득
function getOrderPgInfo($db,$order_code) {
	$order_pg_info = array();
	
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
			
			OI.PG_PAYMENT_KEY			AS PG_PAYMENT_KEY,
			OI.PG_REMAIN_PRICE			AS PG_REMAIN_PRICE
		FROM
			ORDER_INFO OI
		WHERE
			OI.ORDER_CODE = '".$order_code."'
	";
	
	$db->query($select_order_info_sql);
	
	foreach($db->fetch() as $info_data) {
		$order_pg_info = array(
			'order_idx'				=>$info_data['ORDER_IDX'],
			'country'				=>$info_data['COUNTRY'],
			'order_code'			=>$info_data['ORDER_CODE'],
			
			'price_product'			=>$info_data['PRICE_PRODUCT'],
			'price_delivery'		=>$info_data['PRICE_DELIVERY'],
			'price_discount'		=>$info_data['PRICE_DISCOUNT'],
			'price_mileage_point'	=>$info_data['PRICE_MILEAGE_POINT'],
			'price_total'			=>$info_data['PRICE_TOTAL'],
			
			'pg_payment_key'		=>$info_data['PG_PAYMENT_KEY'],
			'pg_remain_price'		=>$info_data['PG_REMAIN_PRICE']
		);
	}
	
	return $order_pg_info;
}

//주문 상태별 [주문 상태별 테이블] 가격 합계 취득
function getOrderPriceInfo($db,$order_status,$order_code) {
	$order_price = array();
	
	$order_table = getOrderTable($order_status,false);
	
	$column_price_cancel = "";
	$column_price_delivery = "";
	$column_price_total = "";
	
	if ($order_status != "PRD") {
		$column_price_cancel = "
			SUM(OT.PRICE_CANCEL)		AS PRICE_CANCEL,
		";
		
		$column_price_total = "
			0							AS PRICE_TOTAL
		";
	} else {
		$column_price_cancel = "
			0							AS PRICE_CANCEL,
		";
		
		$column_price_total = "
			SUM(OT.PRICE_TOTAL)			AS PRICE_TOTAL
		";
	}
	
	if ($order_status == "OEX" || $order_status == "ORF") {
		$column_price_delivery = "
			SUM(OT.PRICE_DELIVERY)		AS PRICE_DELIVERY,
		";
	} else {
		$column_price_delivery = "
			0							AS PRICE_DELIVERY,
		";
	}
	
	$select_order_price_sql = "
		SELECT
			SUM(OT.PRICE_PRODUCT)		AS PRICE_PRODUCT,
			".$column_price_cancel."
			SUM(OT.PRICE_DISCOUNT)		AS PRICE_DISCOUNT,
			SUM(OT.PRICE_MILEAGE_POINT)	AS PRICE_MILEAGE_POINT,
			SUM(OT.PRICE_CHARGE_POINT)	AS PRICE_CHARGE_POINT,
			".$column_price_delivery."
			".$column_price_total."
		FROM
			".$order_table['info']." OT
		WHERE
			OT.ORDER_CODE = '".$order_code."'
	";
	
	$db->query($select_order_price_sql);
	
	foreach($db->fetch() as $price_data) {
		$order_price = array(
			'price_product'			=>number_format($price_data['PRICE_PRODUCT']),
			'price_cancel'			=>number_format($price_data['PRICE_CANCEL']),
			'price_discount'		=>number_format($price_data['PRICE_DISCOUNT']),
			'price_mileage_point'	=>number_format($price_data['PRICE_MILEAGE_POINT']),
			'price_charge_point'	=>number_format($price_data['PRICE_CHARGE_POINT']),
			'price_delivery'		=>number_format($price_data['PRICE_DELIVERY']),
			'price_total'			=>number_format($price_data['PRICE_TOTAL'])
		);
	}
	
	return $order_price;
}

//주문 상태별 [임시 주문 상태별 상품 테이블] 가격 합계 취득
function getTmpOrderPriceInfo($db,$order_update_code) {
	$order_price = array();
	
	$order_table = getOrderTable($order_code,false);
	
	$column_price_cancel = "";
	if ($order_status != "PRD") {
		$column_price_cancel = "
			SUM(OT.PRICE_CANCEL)		AS PRICE_CANCEL
		";
	}
	
	$select_order_price_sql = "
		SELECT
			SUM(OT.PRICE_PRODUCT)		AS PRICE_PRODUCT,
			".$column_price_cancel."
			SUM(OT.PRICE_DISCOUNT)		AS PRICE_DISCOUNT,
			SUM(OT.PRICE_MILEAGE_POINT)	AS PRICE_MILEAGE_POINT,
			SUM(OT.PRICE_CHARGE_POINT)	AS PRICE_CHARGE_POINT,
			SUM(OT.PRICE_DELIVERY)		AS PRICE_DELIVERY,
			SUM(OT.PRICE_TOTAL)			AS PRICE_TOTAL
		FROM
			".$order_table['info']."
		WHERE
			OT.ORDER_CODE = '".$order_code."'
	";
	
	$db->query($select_order_price_sql);
	
	foreach($db->fetch() as $price_data) {
		$order_price = array(
			'price_product'			=>$price_data['PRICE_PRODUCT'],
			'price_cancel'			=>$price_data['PRICE_CANCEL'],
			'price_discount'		=>$price_data['PRICE_DISCOUNT'],
			'price_mileage_point'	=>$price_data['PRICE_MIELAGE_POINT'],
			'price_charge_point'	=>$price_data['PRICE_CHARGE_POINT'],
			'price_delivery'		=>$price_data['PRICE_DELIVERY'],
			'price_total'			=>$price_data['PRICE_TOTAL']
		);
	}
	
	return $order_price;
}

//[주문 취소 테이블],[주문 반품 테이블] 총 결제취소 비용 취득
function getTotalCancelPrice($db,$order_status,$order_code) {
	$total_cancel_price = 0;
	
	$order_table = getOrderTable($order_status,false);
	
	$select_total_cancel_price_sql = "
		SELECT
			IFNULL(
				SUM(OT.PRICE_CANCEL),0
			)		AS PRICE_CANCEL
		FROM
			".$order_table['info']." OT
		WHERE
			OT.ORDER_CODE = '".$order_code."'
	";
	
	$db->query($select_total_cancel_price_sql);
	
	foreach($db->fetch() as $table_data) {
		$total_cancel_price = $table_data['PRICE_CANCEL'];
	}
	
	return $total_cancel_price;
}

//[주문취소],[주문교환 접수],[주문반품 접수] 이후 [주문 상품 테이블] 갱신처리
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

//[주문교환 접수][주문반품 접수] 주문 추가 배송비 결제처리
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


// 주문 적립금 조회 처리
function getMileageInfo($db,$mileage_code,$order_code) {
	$mileage_info = null;
	
	$select_mileage_info_sql = "
		SELECT
			MI.IDX						AS MILEAGE_IDX,
			MI.MILEAGE_UNUSABLE			AS MILEAGE_UNUSABLE,
			MI.MILEAGE_USABLE_INC		AS MILEAGE_USABLE_INC,
			MI.MILEAGE_USABLE_DEC		AS MILEAGE_USABLE_DEC,
			MI.MILEAGE_BALANCE			AS MILEAGE_BALANCE,
			
			MI.ORDER_CODE				AS ORDER_CODE,
			MI.ORDER_PRODUCT_CODE		AS ORDER_PRODUCT_CODE
		FROM
			MILEAGE_INFO MI
		WHERE
			MI.MILEAGE_CODE = '".$mileage_code."' AND
			MI.ORDER_CODE = '".$order_code."'
	";
	
	$db->query($select_mileage_info_sql);
	
	foreach($db->fetch() as $mileage_data) {
		$mileage_info = array(
			'mileage_idx'			=>$mileage_data['MILEAGE_IDX'],
			'mileage_unusable'		=>$mileage_data['MILEAGE_UNUSABLE'],
			'mileage_usable_inc'	=>$mileage_data['MILEAGE_USABLE_INC'],
			'mileage_usable_dec'	=>$mileage_data['MILEAGE_USABLE_DEC'],
			'mileage_balance'		=>$mileage_data['MILEAGE_BALANCE'],
			
			'order_code'			=>$mileage_data['ORDER_CODE'],
			'order_product_code'	=>$mileage_data['ORDER_PRODUCT_CODE']
		);
	}
	
	return $mileage_info;
}

// 주문 적립금 삭제 처리
function delMileageInfo($db,$mileage_code,$order_code) {
	$delete_mileage_info_sql = "
		UPDATE
			MILEAGE_INFO
		SET
			DEL_FLG = TRUE
		WHERE
			MILEAGE_CODE = '".$mileage_code."' AND
			ORDER_CODE = '".$order_code."'
	";
	
	$db->query($delete_mileage_info_sql);
}

// 주문시 사용했던 적립금 환급처리
function initMileageInfo($db,$mileage_idx,$mileage_code,$mileage_price,$session_id) {
	$code_type = substr($mileage_code,1,2);
	
	$calc_type = "";
	if ($code_type == "IN") {
		$calc_type = "+";
	} else if ($code_type == "DC") {
		$calc_type = "-";
	}
	
	$insert_mileage_info_sql = "
		INSERT INTO
			MILEAGE_INFO
		(
			COUNTRY,
			MEMBER_IDX,
			ID,
			MILEAGE_CODE,
			MILEAGE_BALANCE,
			ORDER_CODE,
			ORDER_PRODUCT_CODE,
			MANAGER,
			CREATER,
			UPDATER
		)
		SELECT
			MI.COUNTRY				AS COUNTRY,
			MI.MEMBER_IDX			AS MEMBER_IDX,
			MI.ID					AS ID,
			'".$mileage_code."'		AS MILEAGE_CODE,
			(
				(
					SELECT
						S_MI.MILEAGE_BALANCE
					FROM
						MILEAGE_INFO S_MI
					WHERE
						S_MI.MEMBER_IDX = MI.MEMBER_IDX
					ORDER BY
						S_MI.IDX DESC
					LIMIT
						0,1
				) ".$calc_type." ".$mileage_price."
			)						AS MILEAGE_BALANCE,
			MI.ORDER_CODE			AS ORDER_CODE,
			MI.ORDER_PRODUCT_CODE	AS ORDER_PRODUCT_CODE,
			'".$session_id."'		AS MANAGER,
			'".$session_id."'		AS CREATER,
			'".$session_id."'		AS UPDATER
		FROM
			MILEAGE_INFO MI
		WHERE
			IDX = ".$mileage_idx."
	";
	
	$db->query($insert_mileage_info_sql);
}

// 주문상태 갱신 적립금 추가 처리
function addMileageInfo($db,$order_status,$mileage_code,$mileage_price,$order_product_code,$session_id) {
	$order_table = getOrderTable($order_status);
	
	$insert_mileage_info_sql = "
		INSERT INTO
			MILEAGE_INFO
		(
			COUNTRY,
			MEMBER_IDX,
			ID,
			MILEAGE_CODE,
			MILEAGE_USABLE_INC,
			MILEAGE_BALANCE,
			ORDER_CODE,
			ORDER_PRODUCT_CODE,
			MANAGER,
			CREATER,
			UPDATER
		)
		SELECT
			OI.COUNTRY				AS COUNTRY,
			OI.MEMBER_IDX			AS MEMBER_IDX,
			OI.MEMBER_ID			AS MEMBER_ID,
			'".$mileage_code."'		AS MILEAGE_CODE,
			".$mileage_price."		AS MILEAGE_USABLE_INC,
			(
				(
					SELECT
						S_MI.MILEAGE_BALANCE
					FROM
						MILEAGE_INFO S_MI
					WHERE
						S_MI.COUNTRY = OI.COUNTRY AND
						S_MI.MEMBER_IDX = OI.MEMBER_IDX
					ORDER BY
						S_MI.IDX DESC
					LIMIT
						0,1
				) + ".$mileage_price."
			)						AS MILEAGE_BALANCE,
			OP.ORDER_CODE			AS ORDER_CODE,
			OP.ORDER_PRODUCT_CODE	AS ORDER_PRODUCT_CODE,
			'".$session_id."'		AS MANAGER,
			'".$session_id."'		AS CREATER,
			'".$session_id."'		AS UPDATER
		FROM
			".$order_table['product']." OP
			LEFT JOIN ".$order_table['info']." OI ON
			OP.ORDER_CODE = OI.ORDER_CODE
		WHERE
			OP.ORDER_PRODUCT_CODE = '".$order_product_code."'
	";
	
	$db->query($insert_mileage_info_sql);
}

// PG사 결제 취소처리
function orderPgCancel($pg_payment_key,$refund_price) {
	$pg_cancel_info = null;
	
	$curl = curl_init();

	curl_setopt_array($curl, [
		CURLOPT_URL => "https://api.tosspayments.com/v1/payments/".$pg_payment_key."/cancel",
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
		$pg_result = json_decode($response,true);
		
		$pg_cancel_date = null;
		$pg_cancel_price = null;
		$pg_cancel_key = null;
		
		$pg_remain_price = $pg_result['balanceAmount'];
		
		$cancels = $pg_result['cancels'];
		if ($cancels != null && count($cancels) > 0) {
			$cacel = $cancels[count($cancels) - 1];
			
			$pg_cancel_date		= $cacel['canceledAt'];
			$pg_cancel_price	= $cacel['cancelAmount'];
			$pg_cancel_key		= $cacel['transactionKey'];
		}
		
		$pg_cancel_info = array(
			'pg_cancel_date'	=>$pg_cancel_date,
			'pg_cancel_price'	=>$pg_cancel_price,
			'pg_cancel_key'		=>$pg_cancel_key,
			'pg_remain_price'	=>$pg_remain_price
		);
	}
	
	return $pg_cancel_info;
}


?>