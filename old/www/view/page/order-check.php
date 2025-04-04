<?php

include_once(dir_f_api."/send/send-common.php");
include_once(dir_f_api."/send/send-mail.php");
include_once(dir_f_api."/send/send-kakao.php");

include_once("/var/www/www/api/common.php");
include_once("/var/www/www/api/common/check.php");

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

$member_idx = null;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$member_id = null;
if (isset($_SESSION['MEMBER_ID'])) {
	$member_id = $_SESSION['MEMBER_ID'];
}

$order_code_MLG = null;
if (isset($_GET['order_code'])) {
	$order_code_MLG = $_GET['order_code'];
}

$order_code_PYM = null;
if (isset($_GET['orderId'])) {
	$order_code_PYM = $_GET['orderId'];
}

$payment_key = null;
if (isset($_GET['paymentKey'])) {
	$payment_key = $_GET['paymentKey'];
}

$amount = null;
if (isset($_GET['amount'])) {
	$amount = $_GET['amount'];
}

$code = null;
if(isset($_GET['code'])){
	$code = $_GET['code'];
}

$new_order = null;

/* PG사 결제 취소 예외처리 */
if ($code == "PAY_PROCESS_CANCELED") {
	echo "
		<script>
			location.href='/order/basket/list';
		</script>
	";
	
	exit();
}

/* 주문 정보 등록 */

if ($order_code_MLG != null) {
	/* 결제 진행 전 임시 주문정보 체크처리 */
	$check_tmp = checkTmpOrder($db,$order_code_MLG,$member_idx);
	if ($check_tmp == true) {
		/* 적립금 구매정보 등록처리 (PG사 결제금액 0원) */
		
		/* 1. [주문 정보][주문 상품 정보] 테이블 등록 전 체크처리 */
		$check_result = checkOrderQty($db,$order_code_MLG);
		
		if ($check_result == true) {
			/* 2. 적립금 구매 주문 - [주문 정보] 테이블 등록처리 */
			$order_idx = addOrderInfo_MLG($db,$order_code_MLG);
			
			if (!empty($order_idx)) {
				/* 3. [주문 상품] 테이블 등록처리 */
				addOrderProduct($db,$order_idx,$order_code_MLG);
				
				/* 4-1. [쇼핑백] 테이블 주문 상품 삭제처리 */
				delBasketInfo($db,$order_code_MLG,$member_id);
				
				/* 4-2. [바우처 발급] 테이블 사용 처리 */
				delVoucherInfo($db,$order_code_MLG,$country,$member_idx,$member_id);
				
				/* 4-3. [적립금] 테이블 적립금 갱신 처리 */
				putMileageInfo($db,$order_code_MLG);
				
				/* 4-4. [임시 주문 정보],[임시 주문 상품] 테이블 삭제처리 */
				deleteTmpOrder($db,$order_code_MLG);
				
				/* ========== NAVER CLOUD PLATFORM::신규 주문내역 확인 메일 발송 ========== */
				/* PARAM::MAIL */
				$param_mail = array(
					'country'		=>$country,
					'mail_type'		=>"M",
					'mail_code'		=>"MAIL_CODE_0004",
					
					'param_member'	=>$member_idx,
					'param_admin'	=>null
				);

				/* PARAM::MAIL DATA */
				$param_data_mail = array(
					'member_name'	=>$_SESSION['MEMBER_NAME'],
					'member_id'		=>$_SESSION['MEMBER_ID']
				);

				/* 신규 주문내역 확인 메일 발송 */
				callSEND_mail($db,$param_mail,$param_data);

				/* ========== NAVER CLOUD PLATFORM::신규 주문내역 확인 알림톡 발송 ========== */
				/* PARAM::KAKAO */
				$param_kakao = array(
					'kakao_code'		=>"KAKAO_CODE_0001",
					'member_idx'		=>$_SESSION['MEMBER_IDX']
				);

				/* PARAM::DATA */
				$param_data_kakao = array(
					'data_type'			=>"ORDER",
					'order_idx'			=>$order_idx
				);

				/* PARAM::KAKAO DATA */
				$data_kakao = getDATA_kakao($db,$param_data_kakao);

				/* 신규 주문내역 확인 알림톡 발송 */
				callSEND_kakao($db,$param_kakao,$data_kakao);
				
				echo "
					<script>
						location.href='/order/complete?order_idx=".$order_idx."';
					</script>
				";
			}
		} else {
			/* 적립금 구매 - 수량 부족 초기화 */
			initOrderInfo($db,$country,$member_id,$order_code_MLG,"MSG_F_ERR_0116");
		}
	} else {
		/* 적립금 구매 - 임시 주문정보 삭제 기 초기화 */
		initOrderInfo($db,$country,$member_id,$order_code_MLG,"MSG_F_ERR_0124");
	}
} else if ($order_code_PYM != null) {
	/* PG사 결제 주문 정보 등록처리 */
	
	/* 결제 진행 전 임시 주문정보 체크처리 */
	$check_tmp = checkTmpOrder($db,$order_code_PYM,$member_idx);
	if ($check_tmp == true) {
		/* PG사 결제 구매정보 등록처리 */
		
		/* 1. PG사 결제처리 */
		$pg_info = setPgPayment($db,$order_code_PYM,$payment_key,$amount);
		if ($pg_info != null) {
			/* 2. [주문 정보][주문 상품 정보] 테이블 등록 전 체크처리 */
			$check_result = checkOrderQty($db,$order_code_PYM);
			if ($check_result == true) {
				//3. [주문 정보] 등록처리
				$order_idx = addOrderInfo_PYM($db,$order_code_PYM,$pg_info);
				
				if (!empty($order_idx)) {
					/* 4. [주문 상품 정보] 테이블 등록처리 */
					addOrderProduct($db,$order_idx,$order_code_PYM);
					
					/* 5-1. [쇼핑백] 테이블 주문 상품 삭제처리 */
					delBasketInfo($db,$order_code_PYM,$member_id);
					
					/* 5-2. [바우처 발급] 테이블 사용 처리 */
					delVoucherInfo($db,$order_code_PYM,$country,$member_idx,$member_id);
					
					/* 5-3. [적립금] 테이블 적립금 갱신 처리 */
					putMileageInfo($db,$order_code_PYM);
					
					/* 5-4. 주문정보 스크립트 태그 추가 */
					$new_order = array(
						'order_code'		=>$order_code_PYM,
						'suppliedAmount'	=>$pg_info['suppliedAmount'],
						'pg_currency'		=>$pg_info['pg_currency'],
						'vat'				=>$pg_info['vat']
					);
					
					setNewOrderInfo($db,$country,$order_code_PYM,$new_order);
					
					/* 5-5. [임시 주문 정보],[임시 주문 상품 정보] 테이블 삭제처리 */
					deleteTmpOrder($db,$order_code_PYM);
					
					$db->commit();
					
					/* ========== NAVER CLOUD PLATFORM::신규 주문내역 확인 메일 발송 ========== */
					/* PARAM::MAIL */
					$param_mail = array(
						'country'		=>$country,
						'mail_type'		=>"M",
						'mail_code'		=>"MAIL_CODE_0004",
						
						'param_member'	=>$member_idx,
						'param_admin'	=>null
					);

					/* PARAM::MAIL DATA */
					$param_data_mail = array(
						'member_name'	=>$_SESSION['MEMBER_NAME'],
						'member_id'		=>$_SESSION['MEMBER_ID']
					);

					/* 신규 주문내역 확인 메일 발송 */
					callSEND_mail($db,$param_mail,$param_data);
					
					if ($country == "KR") {
						/* ========== NAVER CLOUD PLATFORM::신규 주문내역 확인 알림톡 발송 ========== */
						/* PARAM::KAKAO */
						$param_kakao = array(
							'kakao_code'		=>"KAKAO_CODE_0001",
							'member_idx'		=>$_SESSION['MEMBER_IDX']
						);

						/* PARAM::DATA */
						$param_data_kakao = array(
							'data_type'			=>"ORDER",
							'order_idx'			=>$order_idx
						);

						/* PARAM::KAKAO DATA */
						$data_kakao = getDATA_kakao($db,$param_data_kakao);

						/* 신규 주문내역 확인 알림톡 발송 */
						callSEND_kakao($db,$param_kakao,$data_kakao);
					}
					
					echo "
						<script>
							location.href='/order/complete?order_idx=".$order_idx."';
						</script>
					";
				}
			} else {
				/* 재고 부족으로 인한 결제 실패 시 PG사 결제 취소처리 */
				setPgCancel($payment_key,$amount);
				
				/* PG사 결제 구매 - 수량 부족 초기화 */
				initOrderInfo($db,$country,$member_id,$order_code_PYM,"MSG_F_ERR_0116");
			}
		} else {
			/* PG사 결제 구매 - PG사 결제 실패 */
			initOrderInfo($db,$country,$member_id,$order_code_PYM,"MSG_F_ERR_0121");
		}
	} else {
		/* 적립금 구매 - 임시 주문정보 삭제 기 초기화 */
		initOrderInfo($db,$country,$member_id,$order_code_MLG,"MSG_F_ERR_0124");
	}
} else {
	/* 주문코드 PARAM 전달 실패 */
	initOrderInfo($db,$country,$member_id,$order_code_PYM,"MSG_F_ERR_0122");
}

function setScriptNotiModal($db,$country,$message_code) {
	$msg = getMsgToMsgCode($db,$country,$message_code,array());
	
	$script = "
		notiModal('".$msg."');
	";
	
	return $script;
}

function checkOrderQty($db,$order_code) {
	$check_result = false;
	
	$select_tmp_order_product_sql = "
		SELECT
			OP.IDX				AS PARENT_IDX,
			OP.PRODUCT_TYPE		AS PRODUCT_TYPE,
			OP.PRODUCT_IDX		AS PRODUCT_IDX,
			OP.OPTION_IDX		AS OPTION_IDX,
			OP.PRODUCT_QTY		AS PRODUCT_QTY
		FROM
			TMP_ORDER_PRODUCT OP
		WHERE
			OP.ORDER_CODE = '".$order_code."' AND
			OP.PRODUCT_TYPE NOT IN ('V','D','M')
	";
	
	$db->query($select_tmp_order_product_sql);
	
	$err_cnt = 0;
	
	foreach($db->fetch() as $data) {
		$product_type = $data['PRODUCT_TYPE'];
		
		$product_idx = $data['PRODUCT_IDX'];
		$option_idx = $data['OPTION_IDX'];
		$product_qty = $data['PRODUCT_QTY'];
		
		if ($product_type == "B") {
			$check_limit_result = checkOrderPurchaseableQty($db,$product_idx,$option_idx,$product_qty);
			if ($check_limit_result != true) {
				$err_cnt++;
			}
		} else {
			$parent_idx = $data['PARENT_IDX'];
			
			$select_set_product_sql = "
				SELECT
					OP.PRODUCT_IDX		AS PRODUCT_IDX,
					OP.OPTION_IDX		AS OPTION_IDX,
					OP.PRODUCT_QTY		AS PRODUCT_QTY
				FROM
					ORDER_PRODUCT OP
				WHERE
					OP.PARENT_IDX = ".$parent_idx."
			";
			
			$db->query($select_set_product_sql);
			
			foreach($db->fetch() as $data_set) {
				$check_limit_result = checkOrderPurchaseableQty($db,$product_idx,$option_idx,$product_qty);
				if ($check_limit_result != true) {
					$err_cnt++;
				}
			}
		}
	}
	
	if ($err_cnt == 0) {
		$check_result = true;
	}
	
	return $check_result;
}

function checkOrderPurchaseableQty($db,$product_idx,$option_idx,$param_qty) {
	$check_result = false;
	
	$where = " 1=1 ";
	
	if ($product_idx > 0) {
		$where .= " AND (V_ST.PRODUCT_IDX = ".$product_idx.") ";
	}
	
	if ($option_idx > 0) {
		$where .= " AND (V_ST.OPTION_IDX = ".$option_idx.") ";
	}
	
	$select_product_stock_sql = "
		SELECT
			SUM(
				V_ST.PURCHASEABLE_QTY
			)					AS PURCHASEABLE_QTY
		FROM
			V_STOCK V_ST
		WHERE
			".$where."
	";
	
	$db->query($select_product_stock_sql);
	
	foreach($db->fetch() as $data) {
		$purchaseable_qty = $data['PURCHASEABLE_QTY'];
		
		if ($purchaseable_qty > 0 && ($purchaseable_qty - $param_qty) >= 0) {
			if ($param_qty <= $purchaseable_qty) {
				$check_result = true;
			}
		}
	}
	
	return $check_result;
}

/* [쇼핑백] 테이블 주문 상품 삭제처리 */
function delBasketInfo($db,$order_code,$member_id) {
	$select_tmp_order_info_sql = "
		SELECT
			TI.BASKET_IDX		AS BASKET_IDX
		FROM
			TMP_ORDER_INFO TI
		WHERE
			TI.ORDER_CODE = '".$order_code."'
	";
	
	$db->query($select_tmp_order_info_sql);
	
	foreach($db->fetch() as $tmp_order_data) {
		$basket_idx = $tmp_order_data['BASKET_IDX'];
		
		$delete_basket_sql = "
			UPDATE
				BASKET_INFO
			SET
				DEL_FLG = TRUE,
				UPDATE_DATE = NOW(),
				UPDATER = '".$member_id."'
			WHERE
				IDX IN (".$basket_idx.") OR
				PARENT_IDX IN (".$basket_idx.")
		";
		
		$db->query($delete_basket_sql);
	}
}

/* [바우처 발급] 테이블 사용 처리 */
function delVoucherInfo($db,$order_code,$country,$member_idx,$member_id) {
	$cnt_VOU = $db->count("TMP_ORDER_PRODUCT","ORDER_CODE = '".$order_code."' AND PRODUCT_TYPE = 'V'");
	if ($cnt_VOU > 0) {
		$update_voucher_issue_sql = "
			UPDATE
				VOUCHER_ISSUE
			SET
				USED_FLG = TRUE,
				UPDATE_DATE = NOW(),
				UPDATER = '".$member_id."'
			WHERE
				IDX = (
					SELECT
						TP.PRODUCT_IDX
					FROM
						TMP_ORDER_PRODUCT TP
					WHERE
						TP.ORDER_CODE = '".$order_code."' AND
						TP.PRODUCT_TYPE = 'V'
				) AND
				COUNTRY = '".$country."' AND
				MEMBER_IDX = ".$member_idx."
		";
		
		$db->query($update_voucher_issue_sql);
	}
}

function checkTmpOrder($db,$order_code,$member_idx,) {
	$check_result = false;
	
	$cnt_info = $db->count("TMP_ORDER_INFO","ORDER_CODE = '".$order_code."' AND MEMBER_IDX = ".$member_idx);
	$cnt_product = $db->count("TMP_ORDER_PRODUCT","ORDER_CODE = '".$order_code."'");
	
	if ($cnt_info > 0 && $cnt_product > 0) {
		$check_result = true;
	}
	
	return $check_result;
}

/* [적립금] 테이블 적립금 갱신 처리 */
function putMileageInfo($db,$order_code) {
	$order_point = getOrderPoint($db,$order_code);
	
	//주문시 사용 한 적립금
	$point_mileage = $order_point['point_mileage'];
	
	//주문시 사용 한 예치금
	$point_charge = $order_point['point_charge'];
	
	//주문 상품별 적립비율 취득 처리
	$mileage_info = getMileagePercent($db,$order_code);
	
	//적립포인트 사용 처리
	if ($point_mileage > 0) {
		addMileageInfo_DEC($db,$order_point);
	}
	
	//주문 상품별 적립포인트 추가 처리
	addMileageInfo_INC($db,$order_point,$mileage_info);
}

/* [임시 주문 정보],[임시 주문 상품] 테이블 삭제처리 */
function deleteTmpOrder($db,$order_code) {
	$db->query("DELETE FROM TMP_ORDER_INFO WHERE ORDER_CODE = '".$order_code."'");
	$db->query("DELETE FROM TMP_ORDER_PRODUCT WHERE ORDER_CODE = '".$order_code."'");
}

/* 적립금 구매 주문 - [주문 정보] 테이블 등록처리 */
function addOrderInfo_MLG($db,$order_code) {
	/* [주문 정보][주문 상품 정보] 테이블 등록 전 체크처리 */
	$check_result = checkOrderQty($db,$order_code);
	
	$insert_order_info_sql = "
		INSERT INTO
			ORDER_INFO
		(
			IDX,
			COUNTRY,
			ORDER_CODE,
			ORDER_TITLE,
			ORDER_STATUS,
			
			MEMBER_IDX,
			MEMBER_ID,
			MEMBER_NAME,
			MEMBER_MOBILE,
			MEMBER_LEVEL,
			
			PRICE_PRODUCT,
			PRICE_MILEAGE_POINT,
			PRICE_CHARGE_POINT,
			PRICE_DISCOUNT,
			PRICE_DELIVERY,
			PRICE_TOTAL,
			
			PG_MID,
			PG_PAYMENT,
			PG_STATUS,
			PG_DATE,
			PG_PRICE,
			PG_CURRENCY,
			
			TO_PLACE,
			TO_NAME,
			TO_MOBILE,
			TO_ZIPCODE,
			TO_LOT_ADDR,
			TO_ROAD_ADDR,
			TO_DETAIL_ADDR,
			
			ORDER_MEMO,
			
			CREATER,
			UPDATER
		)
		SELECT
			TI.IDX						AS IDX,
			TI.COUNTRY					AS COUNTRY,
			TI.ORDER_CODE				AS ORDER_CODE,
			TI.ORDER_TITLE				AS ORDER_TITLE,
			'PCP'						AS ORDER_STATUS,
			
			TI.MEMBER_IDX				AS MEMBER_IDX,
			TI.MEMBER_ID				AS MEMBER_ID,
			TI.MEMBER_NAME				AS MEMBER_NAME,
			TI.MEMBER_MOBILE			AS MEMBER_MOBILE,
			TI.MEMBER_LEVEL				AS MEMBER_LEVEL,
			
			TI.PRICE_PRODUCT			AS PRICE_PRODUCT,
			TI.PRICE_MILEAGE_POINT		AS PRICE_MILEAGE_POINT,
			TI.PRICE_CHARGE_POINT		AS PRICE_CHARGE_POINT,
			TI.PRICE_DISCOUNT			AS PRICE_DISCOUNT,
			TI.PRICE_DELIVERY			AS PRICE_DELIVERY,
			TI.PRICE_TOTAL				AS PRICE_TOTAL,
			
			TI.MEMBER_ID				AS PG_MID,
			'적립금'						AS PG_PAYMENT,
			'DONE'						AS PG_STATUS,
			NOW()						AS PG_DATE,
			TI.PRICE_MILEAGE_POINT		AS PG_PRICE,
			'MLG'						AS PG_CURRENCY,
			
			TI.TO_PLACE					AS TO_PLCAE,
			TI.TO_NAME					AS TO_NAME,
			TI.TO_MOBILE				AS TO_MOBILE,
			TI.TO_ZIPCODE				AS TO_ZIPCODE,
			TI.TO_LOT_ADDR				AS TO_LOT_ADDR,
			TI.TO_ROAD_ADDR				AS TO_ROAD_ADDR,
			TI.TO_DETAIL_ADDR			AS TO_DETAIL_ADDR,
			
			TI.ORDER_MEMO				AS ORDER_MEMO,
			
			TI.CREATER					AS CREATER,
			TI.UPDATER					AS UPDATER
		FROM
			TMP_ORDER_INFO TI
		WHERE
			TI.ORDER_CODE = '".$order_code."'
	";
	
	$db->query($insert_order_info_sql);
	
	$order_idx = $db->last_id();
	
	return $order_idx;
}

/* PG사 결제처리 */
function setPgPayment($db,$order_code,$payment_key,$amount) {
	$pg_info = null;
	
	$pg_mid = null;
	$pg_payment = null;
	$pg_payment_key = null;
	$pg_issue_code = "NULL";
	$pg_card_number = "NULL";
	$pg_status = null;
	$pg_date = null;
	$pg_price = null;
	$pg_currency = null;
	$pg_receipt_url = "NULL";
	$pg_remain_price = 0;
	$suppliedAmount = null;
	$vat = null;
	
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
				\"orderId\":\"".$order_code."\",
				\"paymentKey\":\"".$payment_key."\",
				\"amount\":".$amount."
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
		$result = json_decode($response,true);
		
		if (isset($result['status'])) {
			$pg_status = $result['status'];
		}
		
		if ($pg_status == "DONE") {
			if (isset($result['mId'])) {
				$pg_mid = $result['mId'];
			}
			
			if (isset($result['method'])) {
				$pg_payment = $result['method'];
			}

			if (isset($result['paymentKey'])) {
				$pg_payment_key = $result['paymentKey'];
			}
			
			if (isset($result['card'])) {
				$card = $result['card'];
				
				if (isset($card['issuerCode'])) {
					$pg_issue_code = "'".$card['issuerCode']."'";
				}
				
				if (isset($card['number'])) {
					$pg_card_number = "'".$card['number']."'";
				}
			}
			
			if (isset($result['approvedAt'])) {
				$pg_date = $result['approvedAt'];
			}
			
			if (isset($result['totalAmount'])) {
				$pg_price = $result['totalAmount'];
			}
			
			if (isset($result['currency'])) {
				$pg_currency = $result['currency'];
			}

			if (isset($result['receipt'])) {
				$receipt = $result['receipt'];
				
				if (isset($receipt['url']) != null) {
					$pg_receipt_url = "'".$receipt['url']."'";
				}
			}
			
			if (isset($result['balanceAmount'])) {
				$pg_remain_price = $result['balanceAmount'];
			}
			
			if (isset($result['suppliedAmount'])) {
				$suppliedAmount = $result['suppliedAmount'];
			}
			
			if (isset($result['vat'])) {
				$vat = $result['vat'];
			}
			
			$pg_info = array(
				'pg_mid'			=>$pg_mid,
				'pg_payment'		=>$pg_payment,
				'pg_payment_key'	=>$pg_payment_key,
				'pg_issue_code'		=>$pg_issue_code,
				'pg_card_number'	=>$pg_card_number,
				'pg_status'			=>$pg_status,
				'pg_date'			=>$pg_date,
				'pg_price'			=>$pg_price,
				'pg_currency'		=>$pg_currency,
				'pg_receipt_url'	=>$pg_receipt_url,
				'pg_remain_price'	=>$pg_remain_price,
				
				'suppliedAmount'	=>$suppliedAmount,
				'vat'				=>$vat
			);
		}
		
		curl_close($curl);
	}
	
	return $pg_info;
}

/* PG사 결제 주문 - [주문 정보] 테이블 등록처리 */
function addOrderInfo_PYM($db,$order_code,$pg_info) {
	/* [주문 정보][주문 상품 정보] 테이블 등록 전 체크처리 */
	$check_result = checkOrderQty($db,$order_code);
	
	$insert_order_info_sql = "
		INSERT INTO
			ORDER_INFO
		(
			COUNTRY,
			ORDER_CODE,
			ORDER_TITLE,
			ORDER_STATUS,
			
			MEMBER_IDX,
			MEMBER_ID,
			MEMBER_NAME,
			MEMBER_MOBILE,
			MEMBER_LEVEL,
			
			PRICE_PRODUCT,
			PRICE_MILEAGE_POINT,
			PRICE_CHARGE_POINT,
			PRICE_DISCOUNT,
			PRICE_DELIVERY,
			PRICE_TOTAL,
			
			PG_MID,
			PG_PAYMENT,
			PG_PAYMENT_KEY,
			PG_ISSUE_CODE,
			PG_CARD_NUMBER,
			PG_STATUS,
			PG_DATE,
			PG_PRICE,
			PG_CURRENCY,
			PG_RECEIPT_URL,
			PG_REMAIN_PRICE,
			
			TO_PLACE,
			TO_NAME,
			TO_MOBILE,
			TO_ZIPCODE,
			TO_LOT_ADDR,
			TO_ROAD_ADDR,
			TO_DETAIL_ADDR,
			
			ORDER_MEMO,
			
			CREATER,
			UPDATER
		)
		SELECT
			TI.COUNTRY							AS COUNTRY,
			TI.ORDER_CODE						AS ORDER_CODE,
			TI.ORDER_TITLE						AS ORDER_TITLE,
			'PCP'								AS ORDER_STATUS,
			
			TI.MEMBER_IDX						AS MEMBER_IDX,
			TI.MEMBER_ID						AS MEMBER_ID,
			TI.MEMBER_NAME						AS MEMBER_NAME,
			TI.MEMBER_MOBILE					AS MEMBER_MOBILE,
			TI.MEMBER_LEVEL						AS MEMBER_LEVEL,
			
			TI.PRICE_PRODUCT					AS PRICE_PRODUCT,
			TI.PRICE_MILEAGE_POINT				AS PRICE_MILEAGE_POINT,
			TI.PRICE_CHARGE_POINT				AS PRICE_CHARGE_POINT,
			TI.PRICE_DISCOUNT					AS PRICE_DISCOUNT,
			TI.PRICE_DELIVERY					AS PRICE_DELIVERY,
			TI.PRICE_TOTAL						AS PRICE_TOTAL,
			
			'".$pg_info['pg_mid']."'			AS PG_MID,
			'".$pg_info['pg_payment']."'		AS PG_PAYMENT,
			'".$pg_info['pg_payment_key']."'	AS PG_PAYMENT_KEY,
			".$pg_info['pg_issue_code']."		AS PG_ISSUE_CODE,
			".$pg_info['pg_card_number']."		AS PG_CARD_NUMBER,
			'".$pg_info['pg_status']."'			AS PG_STATUS,
			'".$pg_info['pg_date']."'			AS PG_DATE,
			'".$pg_info['pg_price']."'			AS PG_PRICE,
			'".$pg_info['pg_currency']."'		AS PG_CURRENCY,
			".$pg_info['pg_receipt_url']."		AS PG_RECEIPT_URL,
			".$pg_info['pg_remain_price']."		AS PG_REMAIN_PRICE,
			
			TI.TO_PLACE							AS TO_PLCAE,
			TI.TO_NAME							AS TO_NAME,
			TI.TO_MOBILE						AS TO_MOBILE,
			TI.TO_ZIPCODE						AS TO_ZIPCODE,
			TI.TO_LOT_ADDR						AS TO_LOT_ADDR,
			TI.TO_ROAD_ADDR						AS TO_ROAD_ADDR,
			TI.TO_DETAIL_ADDR					AS TO_DETAIL_ADDR,
			
			TI.ORDER_MEMO						AS ORDER_MEMO,
			
			TI.CREATER							AS CREATER,
			TI.UPDATER							AS UPDATER
		FROM
			TMP_ORDER_INFO TI
		WHERE
			TI.ORDER_CODE = '".$order_code."'
	";
	
	$db->query($insert_order_info_sql);
	
	$order_idx = $db->last_id();
	
	return $order_idx;
}

/* [주문 상품 정보] 테이블 등록처리 */
function addOrderProduct($db,$order_idx,$order_code) {
	/* [주문 정보][주문 상품 정보] 테이블 등록 전 체크처리 */
	$check_result = checkOrderQty($db,$order_code);
	
	$select_tmp_order_product_sql = "
		SELECT
			TP.IDX					AS PARENT_IDX,
			TP.ORDER_PRODUCT_CODE	AS ORDER_PRODUCT_CODE,
			TP.PRODUCT_TYPE			AS PRODUCT_TYPE
		FROM
			TMP_ORDER_PRODUCT TP
		WHERE
			TP.ORDER_CODE = '".$order_code."' AND
			TP.PARENT_IDX = 0
		ORDER BY
			TP.ORDER_PRODUCT_CODE
	";
	
	$db->query($select_tmp_order_product_sql);
	
	foreach($db->fetch() as $tmp_data) {
		$order_product_code = $tmp_data['ORDER_PRODUCT_CODE'];
		
		$insert_order_product_sql = "
			INSERT INTO
				ORDER_PRODUCT
			(
				ORDER_IDX,
				ORDER_CODE,
				ORDER_PRODUCT_CODE,
				ORDER_STATUS,
				
				PRODUCT_IDX,
				PRODUCT_TYPE,
				PARENT_IDX,
				REORDER_CNT,
				PRODUCT_CODE,
				PRODUCT_NAME,
				
				OPTION_IDX,
				BARCODE,
				OPTION_NAME,
				
				PRODUCT_QTY,
				PRODUCT_PRICE,
				
				CREATER,
				UPDATER
			)
			SELECT
				".$order_idx."			AS ORDER_IDX,
				TP.ORDER_CODE			AS ORDER_CODE,
				TP.ORDER_PRODUCT_CODE	AS ORDER_PRODUCT_CODE,
				TP.ORDER_STATUS			AS ORDER_STATUS,
				
				TP.PRODUCT_IDX			AS PRODUCT_IDX,
				TP.PRODUCT_TYPE			AS PRODUCT_TYPE,
				TP.PARENT_IDX			AS PARENT_IDX,
				TP.REORDER_CNT			AS REORDER_CNT,
				TP.PRODUCT_CODE			AS PRODUCT_CODE,
				TP.PRODUCT_NAME			AS PRODUCT_NAME,
				
				TP.OPTION_IDX			AS OPTION_IDX,
				TP.BARCODE				AS BARCODE,
				TP.OPTION_NAME			AS OPTION_NAME,
				
				TP.PRODUCT_QTY			AS PRODUCT_QTY,
				TP.PRODUCT_PRICE		AS PRODUCT_PRICE,
				
				TP.CREATER				AS CREATER,
				TP.UPDATER				AS UPDATER
			FROM
				TMP_ORDER_PRODUCT TP
			WHERE
				ORDER_PRODUCT_CODE = '".$order_product_code."'
		";
		
		$db->query($insert_order_product_sql);
		
		$order_product_idx = $db->last_id();
		
		$product_type = $tmp_data['PRODUCT_TYPE'];
		if (!empty($order_product_idx) && $product_type == "S") {
			$parent_idx = $tmp_data['PARENT_IDX'];
			addSetProduct($db,$order_idx,$order_code,$order_product_idx,$parent_idx);
		}
	}
}

/* 세트 구성상품 [주문 상품 정보] 테이블 등록처리 */
function addSetProduct($db,$order_idx,$order_code,$order_product_idx,$parent_idx) {
	$insert_set_product_sql = "
		INSERT INTO
			ORDER_PRODUCT
		(
			ORDER_IDX,
			ORDER_CODE,
			ORDER_PRODUCT_CODE,
			ORDER_STATUS,
			
			PRODUCT_IDX,
			PRODUCT_TYPE,
			PARENT_IDX,
			REORDER_CNT,
			PRODUCT_CODE,
			PRODUCT_NAME,
			
			OPTION_IDX,
			BARCODE,
			OPTION_NAME,
			
			PRODUCT_QTY,
			PRODUCT_PRICE,
			
			CREATER,
			UPDATER
		)
		SELECT
			".$order_idx."			AS ORDER_IDX,
			TP.ORDER_CODE			AS ORDER_CODE,
			TP.ORDER_PRODUCT_CODE	AS ORDER_PRODUCT_CODE,
			TP.ORDER_STATUS			AS ORDER_STATUS,
			
			TP.PRODUCT_IDX			AS PRODUCT_IDX,
			TP.PRODUCT_TYPE			AS PRODUCT_TYPE,
			".$order_product_idx."	AS PARENT_IDX,
			TP.REORDER_CNT			AS REORDER_CNT,
			TP.PRODUCT_CODE			AS PRODUCT_CODE,
			TP.PRODUCT_NAME			AS PRODUCT_NAME,
			
			TP.OPTION_IDX			AS OPTION_IDX,
			TP.BARCODE				AS BARCODE,
			TP.OPTION_NAME			AS OPTION_NAME,
			
			TP.PRODUCT_QTY			AS PRODUCT_QTY,
			TP.PRODUCT_PRICE		AS PRODUCT_PRICE,
			
			TP.CREATER				AS CREATER,
			TP.UPDATER				AS UPDATER
		FROM
			TMP_ORDER_PRODUCT TP
		WHERE
			TP.ORDER_CODE = '".$order_code."' AND
			TP.PARENT_IDX = ".$parent_idx."
		ORDER BY
			TP.ORDER_PRODUCT_CODE ASC
	";
	
	$db->query($insert_set_product_sql);
}

function getOrderPoint($db,$order_code) {
	$order_point = array();
	
	$select_order_point_sql = "
		SELECT
			TI.COUNTRY					AS COUNTRY,
			TI.ORDER_CODE				AS ORDER_CODE,
			TI.MEMBER_IDX				AS MEMBER_IDX,
			TI.MEMBER_ID				AS MEMBER_ID,
			
			TI.PRICE_MILEAGE_POINT		AS PRICE_MILEAGE_POINT,
			TI.PRICE_CHARGE_POINT		AS PRICE_CHARGE_POINT
		FROM
			TMP_ORDER_INFO TI
		WHERE
			TI.ORDER_CODE = '".$order_code."'
	";
	
	$db->query($select_order_point_sql);
	
	foreach($db->fetch() as $point_data) {
		$order_point = array(
			'country'			=>$point_data['COUNTRY'],
			'order_code'		=>$point_data['ORDER_CODE'],
			'member_idx'		=>$point_data['MEMBER_IDX'],
			'member_id'			=>$point_data['MEMBER_ID'],
			
			'point_mileage'		=>intval($point_data['PRICE_MILEAGE_POINT']),
			'point_charge'		=>intval($point_data['PRICE_CHARGE_POINT'])
		);
	}
	
	return $order_point;
}

/* 적립비율 조회처리 */
function getMileagePercent($db,$order_code) {
	$mileage_info = array();
	
	$select_order_product_sql = "
		SELECT
			TP.ORDER_PRODUCT_CODE	AS ORDER_PRODUCT_CODE,
			TP.PRODUCT_PRICE		AS PRODUCT_PRICE,
			PR.MILEAGE_FLG			AS MILEAGE_FLG,
			
			PM.MILEAGE_PER			AS PRODUCT_MILEAGE,
			IFNULL(
				LV.MILEAGE_PER,0
			)						AS MEMBER_MILEAGE
		FROM
			TMP_ORDER_PRODUCT TP
			LEFT JOIN TMP_ORDER_INFO TI ON
			TP.ORDER_CODE = TI.ORDER_CODE
			LEFT JOIN MEMBER_LEVEL LV ON
			TI.MEMBER_LEVEL = LV.IDX
			LEFT JOIN SHOP_PRODUCT PR ON
			TP.PRODUCT_IDX = PR.IDX
			LEFT JOIN PRODUCT_MILEAGE PM ON
			TP.PRODUCT_IDX = PM.PRODUCT_IDX AND
			TI.MEMBER_LEVEL = PM.LEVEL_IDX
		WHERE
			TP.ORDER_CODE = '".$order_code."' AND
			TP.PRODUCT_TYPE NOT IN ('V','D')
	";
	
	$db->query($select_order_product_sql);
	
	foreach($db->fetch() as $product_data) {
		$mileage_info[] = array(
			'order_product_code'	=>$product_data['ORDER_PRODUCT_CODE'],
			'product_price'			=>$product_data['PRODUCT_PRICE'],
			'mileage_flg'			=>$product_data['MILEAGE_FLG'],
			
			'product_mileage'		=>$product_data['PRODUCT_MILEAGE'],
			'member_mileage'		=>$product_data['MEMBER_MILEAGE']
		);
	}
	
	return $mileage_info;
}

/* 적립금 차감정보 등록처리 */
function addMileageInfo_DEC($db,$order_point) {
	$insert_mileage_info_dec_sql = "
		INSERT INTO
			MILEAGE_INFO
		(
			COUNTRY,
			MEMBER_IDX,
			ID,
			MILEAGE_CODE,
			MILEAGE_UNUSABLE,
			MILEAGE_USABLE_INC,
			MILEAGE_USABLE_DEC,
			MILEAGE_BALANCE,
			ORDER_CODE,
			ORDER_PRODUCT_CODE,
			MILEAGE_USABLE_DATE_INFO,
			MILEAGE_USABLE_DATE,
			CREATER,
			UPDATER
		)
		SELECT
			'".$order_point['country']."'			AS COUNTRY,
			".$order_point['member_idx']."			AS MEMBER_IDX,
			'".$order_point['member_id']."'			AS ID,
			'PDC'									AS MILEAGE_CODE,
			0										AS MILEAGE_UNUSABLE,
			0										AS MILEAGE_USABLE_INC,
			".$order_point['point_mileage']."		AS MILEAGE_USABLE_DEC,
			(
				SELECT
					IFNULL(
						S_MI.MILEAGE_BALANCE,0
					)
				FROM
					MILEAGE_INFO S_MI
				WHERE
					S_MI.COUNTRY = '".$order_point['country']."' AND
					S_MI.MEMBER_IDX = ".$order_point['member_idx']."
				ORDER BY
					S_MI.IDX DESC
				LIMIT
					0,1
			) - ".$order_point['point_mileage']."	AS MILEAGE_BALANCE,
			'".$order_point['order_code']."'		AS ORDER_CODE,
			NULL									AS ORDER_PRODUCT_CODE,
			NULL									AS MILEAGE_USABLE_DATE_INFO,
			NULL									AS MILEAGE_USABLE_DATE,
			'".$order_point['member_id']."'			AS CREATER,
			'".$order_point['member_id']."'			AS UPDATER
		FROM
			DUAL
	";
	
	$db->query($insert_mileage_info_dec_sql);
}

/* 적립금 증가정보 등록처리 */
function addMileageInfo_INC($db,$order_point,$mileage_info) {
	for ($i=0; $i<count($mileage_info); $i++) {
		$data = $mileage_info[$i];
		
		$mileage_flg = $data['mileage_flg'];
		
		if ($mileage_flg == true) {
			$product_price = $data['product_price'];
			
			$product_mileage = $data['product_mileage'];
			$member_mileage = $data['member_mileage'];
			
			$mileage_per = 0;
			if (!empty($product_mileage)) {
				$mileage_per = $product_mileage;
			} else {
				$mileage_per = $member_mileage;
			}
			
			$mileage_unusable = $product_price * ($mileage_per / 100);
			
			$insert_mileage_info_inc_sql = "
				INSERT INTO
					MILEAGE_INFO
				(
					COUNTRY,
					MEMBER_IDX,
					ID,
					MILEAGE_CODE,
					MILEAGE_UNUSABLE,
					MILEAGE_USABLE_INC,
					MILEAGE_USABLE_DEC,
					MILEAGE_BALANCE,
					ORDER_CODE,
					ORDER_PRODUCT_CODE,
					MILEAGE_USABLE_DATE_INFO,
					MILEAGE_USABLE_DATE,
					CREATER,
					UPDATER
				)
				SELECT
					'".$order_point['country']."'		AS COUNTRY,
					".$order_point['member_idx']."		AS MEMBER_IDX,
					'".$order_point['member_id']."'		AS ID,
					'PIN'								AS MILEAGE_CODE,
					".$mileage_unusable."				AS MILEAGE_UNUSABLE,
					0									AS MILEAGE_USABLE_INC,
					0									AS MILEAGE_USABLE_DEC,
					(
						SELECT
							IFNULL(
								S_MI.MILEAGE_BALANCE,0
							)
						FROM
							MILEAGE_INFO S_MI
						WHERE
							S_MI.COUNTRY = '".$order_point['country']."' AND
							S_MI.MEMBER_IDX = ".$order_point['member_idx']."
						ORDER BY
							S_MI.IDX DESC
						LIMIT
							0,1
					)									AS MILEAGE_BALANCE,
					'".$order_point['order_code']."'	AS ORDER_CODE,
					'".$data['order_product_code']."'	AS ORDER_PRODUCT_CODE,
					'7d'								AS MILEAGE_USABLE_DATE_INFO,
					DATE_ADD(NOW(), INTERVAL 7 DAY)		AS MILEAGE_USABLE_DATE,
					'".$order_point['member_id']."'		AS CREATER,
					'".$order_point['member_id']."'		AS UPDATER
				FROM
					DUAL
			";
			
			$db->query($insert_mileage_info_inc_sql);
		}
	}
}

function setNewOrderInfo($db,$country,$order_code_PYM,$new_order) {
	$select_new_order_product_sql = "
		SELECT
			PR.PRODUCT_CODE					AS PRODUCT_CODE,
			PR.PRODUCT_NAME					AS PRODUCT_NAME,
			OP.OPTION_NAME					AS OPTION_NAME,
			PR.SALES_PRICE_".$country."		AS SALES_PRICE,
			OM.BRAND						AS BRAND,
			OP.PRODUCT_QTY					AS PRODUCT_QTY
		FROM
			ORDER_PRODUCT OP
			LEFT JOIN SHOP_PRODUCT PR ON
			OP.PRODUCT_IDX = PR.IDX
			LEFT JOIN ORDERSHEET_MST OM ON
			PR.ORDERSHEET_IDX = OM.IDX
		WHERE
			OP.ORDER_CODE = '".$order_code."' AND
			OP.PRODUCT_TYPE NOT IN ('V','D')
	";
		
	$db->query($select_new_order_product_sql);
	
	$str_product_div = '';
	foreach($db->fetch() as $new_data){
		$str_product_div .= "
			products.push(
				{
					'item_id		: '".$new_data['PRODUCT_CODE']."',
					'item_name'		: '".$new_data['PRODUCT_NAME']."',
					'item_variant'	: '".$new_data['OPTION_NAME']."',
					'price'			: ".$new_data['SALES_PRICE'].",
					'item_quantity'	: ".$new_data['PRODUCT_QTY'].",
					'item_brand'	: '".$new_data['BRAND']."',
					'item_category'	: ''
				} 
			);
		";
	}
		
	echo "
		<script>
			var products	= [];
			var order_id	= '".$new_order['order_code']."';
			var revenue		= ".$new_order['suppliedAmount'].";
			var shipping	= 0;
			var tax			= ".$new_order['vat'].";
			
			//주문상품 반복문 시작.
			".$str_product_div."
			
			dataLayer.push({
				'event' : 'purchase',
				'ecommerce': {
					'purchase': {
						'items': products,
						'transaction_id': order_id,
						'value': revenue,
						'shipping': shipping,
						'tax': tax,
						'currency': '".$new_order['pg_currency']."'
					}
				}
			});
		</script>
	";
}

/* 쇼핑백 결제정보 초기화 */
function initBasketInfo($db,$order_code,$member_id) {
	$select_tmp_order_info_sql = "
		SELECT
			TI.BASKET_IDX		AS BASKET_IDX
		FROM
			TMP_ORDER_INFO TI
		WHERE
			TI.ORDER_CODE = '".$order_code."'
	";
	
	$db->query($select_tmp_order_info_sql);
	
	foreach($db->fetch() as $tmp_order_data) {
		$basket_idx = $tmp_order_data['BASKET_IDX'];
		
		$init_basket_info_sql = "
			UPDATE
				BASKET_INFO
			SET
				DEL_FLG = FALSE,
				UPDATE_DATE = NOW(),
				UPDATER = '".$member_id."'
			WHERE
				COUNTRY = '".$country."' AND
				IDX IN (".$basket_idx.") OR
				PARENT_IDX IN (".$basket_idx.")
		";
		
		$db->query($init_basket_info_sql);
	}
}

/* 바우처 결제정보 초기화 */
function initVoucherInfo($db,$order_code,$member_id) {
	$update_voucher_issue_sql = "
		UPDATE
			VOUCHER_ISSUE
		SET
			USED_FLG = FALSE,
			UPDATE_DATE = NOW(),
			UPDATER = '".$member_id."'
		WHERE
			IDX = (
				SELECT
					TP.PRODUCT_IDX
				FROM
					TMP_ORDER_PRODUCT TP
				WHERE
					TP.ORDER_CODE = '".$order_code."' AND
					TP.PRODUCT_TYPE = 'V'
			)
	";
	
	$db->query($update_voucher_issue_sql);
}

/* 주문 결제정보 초기화 처리 */
function initOrderInfo($db,$country,$member_id,$order_code,$message_code) {
	/* 쇼핑백 결제정보 초기화 */
	initBasketInfo($db,$order_code,$member_id);	
	
	/* 바우처 결제정보 초기화 */
	$cnt_VOU = $db->count("TMP_ORDER_PRODUCT","ORDER_CODE = '".$order_code."' AND PRODUCT_TYPE = 'V'");
	if ($cnt_VOU > 0) {
		initVoucherInfo($db,$order_code,$member_id);
	}
	
	/* [임시 주문 정보],[임시 주문 상품 정보] 테이블 초기화 */
	deleteTmpOrder($db,$order_code);
	
	$db->commit();
	
	$script = setScriptNotiModal($db,$country,$message_code,array());
	
	echo "
		<script>
			document.addEventListener('DOMContentLoaded', function () {
				".$script."
				
				clickBtnClose();
			});
			
			function clickBtnClose() {
				let close_btn = document.querySelector('.close-btn');
				close_btn.addEventListener('click',function() {
					location.href = '/order/basket/list';
				});
			}
			
		</script>
	";
}

/* PG사 결제 취소처리 */
function setPgCancel($payment_key,$amount) {
	$curl = curl_init();

	curl_setopt_array($curl, [
		CURLOPT_URL => "https://api.tosspayments.com/v1/payments/".$payment_key."/cancel",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => "{\"cancelReason\":\"주문취소\",\"cancelAmount\":".$amount."}",
		CURLOPT_HTTPHEADER => [
			"Authorization: Basic dGVzdF9za19ONU9XUmFwZEE4ZFkyMTc1N2piM28xekVxWktMOg==",
			"Content-Type: application/json"
		],
	]);

	$response = curl_exec($curl);
	$err = curl_error($curl);
	
	curl_close($curl);
}

?>

<div style="height:100vh;"></div>