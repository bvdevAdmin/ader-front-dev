<?php

include_once $_CONFIG['PATH']['API'].'_legacy/send.php';

/* 주문 번호 - 적립금 결제 */
$order_code_M = null;
if (isset($_GET['order_code'])) {
	$order_code_M = $_GET['order_code'];
}

/* 주문 번호 - PG사 결제 */
$order_code_P = null;
if (isset($_GET['orderId'])) {
	$order_code_P = $_GET['orderId'];
}

/* PG사 결제 Key */
$payment_key = null;
if (isset($_GET['paymentKey'])) {
	$payment_key = $_GET['paymentKey'];
}

/* PG사 결제 금액 */
$amount = null;
if (isset($_GET['amount'])) {
	$amount = $_GET['amount'];
}

$new_order = null;

/* PG사 결제 취소 예외처리 */
if (isset($_GET['code']) && $_GET['code'] == "PAY_PROCESS_CANCELED") {
	echo "
		<script>
			location.href = config.base_url + '/pay';
		</script>
	";

	exit();
}

$db->begin_transaction();

try {
	/* 주문 정보 등록 - PG사 결제 */
	if ($order_code_P != null) {
		/* 1. 결제 진행 전 임시 주문정보 체크처리 */
		$check_tmp = checkOrder_tmp($db,$order_code_P);
		if ($check_tmp == true) {
			
			/* 2. 주문 상품 재고 체크처리 */
			$check_stock = checkOrder_stock($db,$order_code_P);
			if ($check_stock == true) {
				
				$pg_info = setPG_payment($db,$order_code_P,$payment_key,$amount);
				if ($pg_info != null) {
					
					/* 3-2. [주문 정보 테이블] 등록 처리 (적립금 결제) */
					$order_idx = addOrder_info_P($db,$order_code_P,$pg_info);
					if (isset($order_idx)) {
						$mileage_per	= 0;
						$discount_per	= 0;
						
						$member_per = checkMember_percentage($db);
						if (sizeof($member_per) > 0) {
							if (isset($member_per['mileage_per'])) {
								$mileage_per = $member_per['mileage_per'];
							}
							
							if (isset($member_per['discount_per'])) {
								$discount_per = $member_per['discount_per'];
							}
						}
						
						/* 4. [주문 상품 정보] 테이블 등록처리 */
						addOrder_product($db,$order_idx,$order_code_P);
						
						/* 5-1. [쇼핑백 테이블] 삭제 처리 */
						putOrder_basket($db,"T",$order_code_P);
						
						/* 5-2. [바우처 지급 정보 테이블] 갱신 처리 */
						putOrder_voucher($db,"T",$order_code_P);
						
						/* 5-3. [적립금 테이블] 사용 적립금 차감 처리 */
						$cnt_mileage = $db->count("TMP_ORDER_INFO","ORDER_CODE = ? AND PRICE_MILEAGE_POINT > 0",array($order_code_P));
						if ($cnt_mileage > 0) {
							addMileage_DEC($db,$order_code_P);
						}
						
						/* 5-4. [적립금 테이블] 주문 적립금 증가 처리 */
						addMileage_INC($db,$order_code_P,$mileage_per);
						
						/* 5-4. [임시 주문 정보],[임시 주문 상품] 테이블 삭제처리 */
						$db->delete(
							"TMP_ORDER_INFO",
							"ORDER_CODE = ?",
							array($order_code_P)
						);
						
						$db->delete(
							"TMP_ORDER_PRODUCT",
							"ORDER_CODE = ?",
							array($order_code_P)
						);
						
						$send_param = getSEND_param($db,$order_code_P);
						
						/* 자동메일 발송설정 체크처리 */
						/* 쇼핑몰 코드 PARAM 수정 - $_SERVER['HTTP_COUNTRY'] => "EN" */
						$mail_setting = checkMAIL_setting($db,"EN","MAIL_CODE_0004");
						if ($mail_setting['mail_flg'] == true && ($mail_setting['template_id'] != null && $mail_setting['template_id'] != "00000")) {
							/* MAIL::PARAM */
							$param_mail = array(
								'user_email'		=>$_SESSION['MEMBER_ID'],
								'user_name'			=>$_SESSION['MEMBER_NAME'],
								'tel_mobile'		=>$_SESSION['TEL_MOBILE'],
								'template_id'		=>$mail_setting['template_id']
							);
							
							/* MAIL::DATA */
							/*
							$mail_data = array(
								'member_id'			=>$_SESSION['MEMBER_ID'],
								'member_name'		=>$_SESSION['MEMBER_NAME'],
								
								'order_code'		=>$send_param['order_code'],
								'order_title'		=>$send_param['order_title'],
								'price_total'		=>$send_param['price_total'],
								'create_date'		=>$send_param['create_date']
							);
							*/
							
							/* (공통) NCP - 메일 발송 */
							callSEND_mail($db,$param_mail,array());
						}
						
						$db->commit();

						echo "
							<script>
								location.href = config.base_url + '/pay-ok?order_idx=".$order_idx."';
							</script>
						";
					}
				} else {
					/* PG사 결제 구매 - PG사 결제 실패 */
					initOrder($db,$order_code_P,"MSG_F_ERR_0121");
				}
			} else {
				/* 임시 주문정보 초기화 처리 */
				initOrder($db,$order_code_P,"MSG_F_ERR_0116");
			}
		} else {
			/* 임시 주문정보 초기화 처리 */
			initOrder($db,$order_code_P,"MSG_F_ERR_0124");
		}
	}

	/* 주문 정보 등록 - 적립금 결제 */
	if ($order_code_M != null) {
		/* 1. 임시 주문정보 체크처리 */
		$check_tmp = checkOrder_tmp($db,$order_code_M);
		if ($check_tmp == true) {
			/* 2. 주문 상품 재고 체크처리 */
			$check_stock = checkOrder_stock($db,$order_code_M);
			if ($check_stock == true) {
				/* 3-1. [주문 정보 테이블] 등록 처리 (적립금 결제) */
				$order_idx = addOrder_info_M($db,$order_code_M);
				if (isset($order_idx)) {
					$mileage_per	= 0;
					$discount_per	= 0;
					
					$member_per = checkMember_percentage($db);
					if (sizeof($member_per) > 0) {
						if (isset($member_per['mileage_per'])) {
							$mileage_per = $member_per['mileage_per'];
						}
						
						if (isset($member_per['discount_per'])) {
							$discount_per = $member_per['discount_per'];
						}
					}
					
					/* 4. [주문 상품 테이블] 등록 처리 */
					addOrder_product($db,$order_idx,$order_code_M);
					
					checkVoucher($db,$order_code_M);

					/* 5-1. [쇼핑백 테이블] 삭제 처리 */
					putOrder_basket($db,"T",$order_code_M);
					
					/* 5-2. [바우처 지급 정보 테이블] 갱신 처리 */
					putOrder_voucher($db,"T",$order_code_M);
					
					/* 5-3. [적립금 테이블] 사용 적립금 차감 처리 */
					$cnt_mileage = $db->count("TMP_ORDER_INFO","ORDER_CODE = ? AND PRICE_MILEAGE_POINT > 0",array($order_code_M));
					if ($cnt_mileage > 0) {
						addMileage_DEC($db,$order_code_M);
					}
					
					/* 5-4. [적립금 테이블] 주문 적립금 증가 처리 */
					addMileage_INC($db,$order_code_M,$mileage_per);
					
					/* 5-4. [임시 주문 정보],[임시 주문 상품] 테이블 삭제처리 */
					$db->delete(
						"TMP_ORDER_INFO",
						"ORDER_CODE = ?",
						array($order_code_M)
					);
					
					$db->delete(
						"TMP_ORDER_PRODUCT",
						"ORDER_CODE = ?",
						array($order_code_M)
					);
					
					$send_param = getSEND_param($db,$order_code_M);
					
					/* 자동메일 발송설정 체크처리 */
					/* 쇼핑몰 코드 PARAM 수정 - $_SERVER['HTTP_COUNTRY'] => "EN" */
					$mail_setting = checkMAIL_setting($db,"EN","MAIL_CODE_0004");
					if ($mail_setting['mail_flg'] == true && ($mail_setting['template_id'] != null && $mail_setting['template_id'] != "00000")) {
						/* PARAM::MAIL */
						$param_mail = array(
							'user_email'		=>$_SESSION['MEMBER_ID'],
							'user_name'			=>$_SESSION['MEMBER_NAME'],
							'tel_mobile'		=>$_SESSION['TEL_MOBILE'],
							'template_id'		=>$mail_setting['template_id']
						);
						
						/* PARAM::DATA */
						/*
						$mail_data = array(
							'member_id'			=>$_SESSION['MEMBER_ID'],
							'member_name'		=>$_SESSION['MEMBER_NAME'],
							
							'order_code'		=>$send_param['order_code'],
							'order_title'		=>$send_param['order_title'],
							'price_total'		=>$send_param['price_total'],
							'create_date'		=>$send_param['create_date']
						);
						*/
						
						/* (공통) NCP - 메일 발송 */
						callSEND_mail($db,$param_mail,array());
					}
					
					$db->commit();
					
					echo "
						<script>
							location.href = config.base_url + '/pay-ok?order_idx=".$order_idx."';
						</script>
					";
				}
			} else {
				/* 임시 주문정보 초기화 처리 */
				initOrder($db,$order_code_M,"MSG_F_ERR_0116");
			}
		} else {
			/* 임시 주문정보 초기화 처리 */
			initOrder($db,$order_code_M,"MSG_F_ERR_0124");
		}
	}
} catch (mysqli_sql_exception $e) {
	$db->rollback();
	
	print_r($e);
	
	$json_result['code'] = 301;
	$json_result['msg'] = '주문 결제처리중 오류가 발생했습니다.';
	
	echo json_encode($json_result);
	exit;
}

/* 임시 주문정보 초기화 처리 */
function initOrder($db,$order_code,$message_code) {
	/* 쇼핑백 결제정보 초기화 */
	putOrder_basket($db,"F",$order_code);
	
	/* 바우처 결제정보 초기화 */
	putOrder_voucher($db,"F",$order_code);
	
	/* [적립금 정보 테이블] 삭제 처리 */
	$db->delete(
		"MILEAGE_INFO",
		"ORDER_CODE = ?",
		array($order_code)
	);
	
	/* [임시 주문 정보],[임시 주문 상품] 테이블 초기화 */
	$db->delete(
		"TMP_ORDER_PRODUCT",
		"ORDER_CODE = ?",
		array($order_code)
	);
	
	$db->delete(
		"TMP_ORDER_INFO",
		"ORDER_CODE = ?",
		array($order_code)
	);

	$db->commit();
	
	/* 쇼핑몰 코드 PARAM 수정 - $_SERVER['HTTP_COUNTRY'] => "EN" */
	$msg = getMsgToMsgCode($db,"EN",$message_code,array());
	
	echo "
		<script>
			alert(
				'".$msg."',
				function() {
					location.href = config.base_url;
				}
			);
		</script>
	";
}

function checkVoucher($db,$order_code_M) {
	$cnt_discount = $db->count("ORDER_INFO","ORDER_CODE = ? AND PRICE_DISCOUNT > 0",array($order_code_M));
	if ($cnt_discount > 0) {
		$update_voucher_issue_sql = "
			UPDATE
				VOUCHER_ISSUE
			SET
				USED_FLG	= TRUE,
				UPDATE_DATE	= NOW(),
				UPDATER		= ?
			WHERE
				IDX = (
					SELECT
						S_OP.PRODUCT_IDX
					FROM
						ORDER_PRODUCT S_OP
					WHERE
						S_OP.ORDER_CODE = ? AND
						S_OP.PRODUCT_TYPE = 'V'
				)
		";

		$db->query($update_voucher_issue_sql,array($_SESSION['MEMBER_ID'],$order_code_M));
	}
}

function putOrder_basket($db,$action_type,$order_code) {
	$select_tmp_order_info_sql = "
		SELECT
			TI.BASKET_IDX		AS BASKET_IDX
		FROM
			TMP_ORDER_INFO TI
		WHERE
			TI.ORDER_CODE = ?
	";

	$db->query($select_tmp_order_info_sql,array($order_code));

	foreach($db->fetch() as $data) {
		$basket_idx = explode(",",$data['BASKET_IDX']);
		
		$tmp_flg = 0;
		if ($action_type == "T") {
			$tmp_flg = 1;
		}
		
		$db->update(
			"BASKET_INFO",
			array(
				'DEL_FLG'		=>$tmp_flg,
				'UPDATE_DATE'	=>NOW(),
				'UPDATER'		=>$_SESSION['MEMBER_ID']
			),
			"
				IDX IN (".implode(',',array_fill(0,count($basket_idx),'?')).") OR
				PARENT_IDX IN (".implode(',',array_fill(0,count($basket_idx),'?')).")
			",
			array_merge($basket_idx,$basket_idx)
		);
	}
}

function putOrder_voucher($db,$action_type,$order_code) {
	$cnt_voucher = $db->count("TMP_ORDER_PRODUCT","ORDER_CODE = ? AND PRODUCT_TYPE = 'V'",array($order_code));
	if ($cnt_voucher > 0) {
		$tmp_flg = 0;
		if ($action_type == "T") {
			$tmp_flg = 1;
		}
		$db->update(
			"VOUCHER_ISSUE",
			array(
				'USED_FLG'		=>$tmp_flg,
				'UPDATE_DATE'	=>NOW(),
				'UPDATER'		=>$_SESSION['MEMBER_ID']
			),
			"
				IDX = (
					SELECT
						TP.PRODUCT_IDX
					FROM
						TMP_ORDER_PRODUCT TP
					WHERE
						TP.ORDER_CODE = ? AND
						TP.PRODUCT_TYPE = 'V'
				)
			",
			array($order_code)
		);
	}
}

/* 1. 결제 진행 전 임시 주문정보 체크처리 */
function checkOrder_tmp($db,$order_code) {
	$check_tmp = false;

	/* 쇼핑몰 코드 PARAM 수정 - $_SERVER['HTTP_COUNTRY'] => "EN" */
	$cnt_info		= $db->count("TMP_ORDER_INFO","ORDER_CODE = ? AND COUNTRY = ? AND MEMBER_IDX = ?",array($order_code,"EN",$_SESSION['MEMBER_IDX']));
	$cnt_product	= $db->count("TMP_ORDER_PRODUCT","ORDER_CODE = ?",array($order_code));
	
	if ($cnt_info > 0 && $cnt_product > 0) {
		$check_tmp = true;
	}
	
	return $check_tmp;
}

/* 2. 주문 상품 재고 체크처리 */
function checkOrder_stock($db,$order_code) {
	$check_stock = false;

	$select_tmp_order_product_sql = "
		SELECT
			OP.PRODUCT_IDX			AS PRODUCT_IDX,
			OP.OPTION_IDX			AS OPTION_IDX,
			OP.PRODUCT_QTY			AS PRODUCT_QTY,
			
			V_ST.PURCHASEABLE_QTY	AS LIMIT_QTY
		FROM
			TMP_ORDER_PRODUCT OP
			
			LEFT JOIN V_STOCK AS V_ST ON
			OP.PRODUCT_IDX = V_ST.PRODUCT_IDX AND
			OP.OPTION_IDX = V_ST.OPTION_IDX
		WHERE
			OP.ORDER_CODE = ? AND
			OP.PRODUCT_TYPE NOT REGEXP 'S|V|D|M'
	";
	
	$db->query($select_tmp_order_product_sql,array($order_code));

	$cnt_soldout = 0;
	
	foreach($db->fetch() as $data) {
		if ($data['PRODUCT_QTY'] > $data['LIMIT_QTY']) {
			$cnt_soldout++;
		}
	}
	
	if ($cnt_soldout == 0) {
		$check_stock = true;
	}
	
	return $check_stock;
}

/* 3-1. [주문 정보 테이블] 등록 처리 (적립금 결제) */
function addOrder_info_M($db,$order_code) {
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
			PG_STATUS,
			PG_DATE,
			PG_PRICE,
			PG_CURRENCY,
			
			PG_REMAIN_PRICE,
			REMAIN_MILEAGE,
			REMAIN_DISCOUNT,
			REMAIN_DELIVERY,
			
			TO_IDX,
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
			CASE
				WHEN
					TI.PRICE_MILEAGE_POINT > 0
					THEN '적립금'
				WHEN
					TI.PRICE_DISCOUNT > 0
					THEN '바우처'
			END							AS PG_PAYMENT,
			'DONE'						AS PG_STATUS,
			NOW()						AS PG_DATE,
			TI.PRICE_MILEAGE_POINT		AS PG_PRICE,
			CASE
				WHEN
					TI.PRICE_MILEAGE_POINT > 0
					THEN 'MLG'
				WHEN
					TI.PRICE_DISCOUNT > 0
					THEN 'VOU'
			END							AS PG_PAYMENT,
			
			0							AS PG_REMAIN_PRICE,
			TI.PRICE_MILEAGE_POINT		AS REMAIN_MILEAGE,
			TI.PRICE_DISCOUNT			AS REMAIN_DISCOUNT,
			TI.PRICE_DELIVERY			AS REMAIN_DELIVERY,
			
			TI.TO_IDX					AS TO_IDX,
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
			TI.ORDER_CODE = ?
	";

	$db->query($insert_order_info_sql,$order_code);

	$order_idx = $db->last_id();

	return $order_idx;
}

/* 3-2. [주문 정보 테이블] 등록처리 (PG사 결제) */
function addOrder_info_P($db,$order_code,$pg) {
	$select_order_info_sql = "
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
			TI.PRICE_MEMBER						AS PRICE_MEMBER,
			TI.PRICE_MILEAGE_POINT				AS PRICE_MILEAGE,
			TI.PRICE_DISCOUNT					AS PRICE_DISCOUNT,
			TI.PRICE_DELIVERY					AS PRICE_DELIVERY,
			TI.PRICE_TOTAL						AS PRICE_TOTAL,
			
			TI.TO_IDX							AS TO_IDX,
			TI.TO_PLACE							AS TO_PLACE,
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
			TI.ORDER_CODE = ?
	";
	
	$db->query($select_order_info_sql,array($order_code));
	
	foreach($db->fetch() as $data) {
		$db->insert(
			"ORDER_INFO",
			array(
				'COUNTRY'				=>$data['COUNTRY'],
				'ORDER_CODE'			=>$data['ORDER_CODE'],
				'ORDER_TITLE'			=>$data['ORDER_TITLE'],

				'MEMBER_IDX'			=>$data['MEMBER_IDX'],
				'MEMBER_ID'				=>$data['MEMBER_ID'],
				'MEMBER_NAME'			=>$data['MEMBER_NAME'],
				'MEMBER_MOBILE'			=>$data['MEMBER_MOBILE'],
				'MEMBER_LEVEL'			=>$data['MEMBER_LEVEL'],

				'PRICE_PRODUCT'			=>$data['PRICE_PRODUCT'],
				'PRICE_MEMBER'			=>$data['PRICE_MEMBER'],
				'PRICE_MILEAGE_POINT'	=>$data['PRICE_MILEAGE'],
				'PRICE_DISCOUNT'		=>$data['PRICE_DISCOUNT'],
				'PRICE_DELIVERY'		=>$data['PRICE_DELIVERY'],
				'PRICE_TOTAL'			=>$data['PRICE_TOTAL'],

				'PG_MID'				=>$pg['pg_mid'],
				'PG_PAYMENT'			=>$pg['pg_payment'],
				'PG_PAYMENT_KEY'		=>$pg['pg_payment_key'],
				'PG_ISSUE_CODE'			=>$pg['pg_issue_code'],
				'PG_CARD_NUMBER'		=>$pg['pg_card_number'],
				'PG_STATUS'				=>$pg['pg_status'],
				'PG_DATE'				=>$pg['pg_date'],
				'PG_PRICE'				=>$pg['pg_price'],
				'PG_CURRENCY'			=>$pg['pg_currency'],
				'PG_RECEIPT_URL'		=>$pg['pg_receipt_url'],
				
				'PG_REMAIN_PRICE'		=>$pg['pg_price'],
				'REMAIN_MILEAGE'		=>$data['PRICE_MILEAGE'],
				'REMAIN_DISCOUNT'		=>$data['PRICE_DISCOUNT'],
				'REMAIN_DELIVERY'		=>$data['PRICE_DELIVERY'],
				
				'TO_IDX'				=>$data['TO_IDX'],
				'TO_PLACE'				=>$data['TO_PLACE'],
				'TO_NAME'				=>$data['TO_NAME'],
				'TO_MOBILE'				=>$data['TO_MOBILE'],
				'TO_ZIPCODE'			=>$data['TO_ZIPCODE'],
				'TO_LOT_ADDR'			=>$data['TO_LOT_ADDR'],
				'TO_ROAD_ADDR'			=>$data['TO_ROAD_ADDR'],
				'TO_DETAIL_ADDR'		=>$data['TO_DETAIL_ADDR'],

				'ORDER_MEMO'			=>$data['ORDER_MEMO'],

				'CREATER'				=>$data['CREATER'],
				'UPDATER'				=>$data['UPDATER']
			)
		);
	}
	
	$order_idx = $db->last_id();

	return $order_idx;
}

/* 4. [주문 상품 테이블] 등록 처리 */
function addOrder_product($db,$order_idx,$order_code) {
	$select_tmp_order_product_sql = "
		SELECT
			TP.IDX					AS TP_IDX,
			TP.ORDER_CODE			AS ORDER_CODE,
			TP.ORDER_PRODUCT_CODE	AS ORDER_PRODUCT_CODE,
			
			TP.PRODUCT_IDX			AS PRODUCT_IDX,
			TP.PRODUCT_TYPE			AS PRODUCT_TYPE,
			TP.PRODUCT_CODE			AS PRODUCT_CODE,
			TP.PRODUCT_NAME			AS PRODUCT_NAME,
			
			TP.OPTION_IDX			AS OPTION_IDX,
			TP.BARCODE				AS BARCODE,
			TP.OPTION_NAME			AS OPTION_NAME,
			
			TP.PRODUCT_QTY			AS PRODUCT_QTY,
			TP.PRODUCT_PRICE		AS PRODUCT_PRICE,
			TP.MEMBER_PRICE			AS MEMBER_PRICE,
			
			TP.CREATER				AS CREATER,
			TP.UPDATER				AS UPDATER
		FROM
			TMP_ORDER_PRODUCT TP
		WHERE
			TP.ORDER_CODE = ? AND
			TP.PARENT_IDX = 0
		ORDER BY
			TP.IDX ASC
	";
	
	$db->query($select_tmp_order_product_sql,array($order_code));
	
	foreach($db->fetch() as $data) {
		$db->insert(
			"ORDER_PRODUCT",
			array(
				'ORDER_IDX'				=>$order_idx,
				'ORDER_CODE'			=>$data['ORDER_CODE'],
				'ORDER_PRODUCT_CODE'	=>$data['ORDER_PRODUCT_CODE'],

				'PRODUCT_IDX'			=>$data['PRODUCT_IDX'],
				'PRODUCT_TYPE'			=>$data['PRODUCT_TYPE'],
				'PRODUCT_CODE'			=>$data['PRODUCT_CODE'],
				'PRODUCT_NAME'			=>$data['PRODUCT_NAME'],

				'OPTION_IDX'			=>$data['OPTION_IDX'],
				'BARCODE'				=>$data['BARCODE'],
				'OPTION_NAME'			=>$data['OPTION_NAME'],

				'PRODUCT_QTY'			=>$data['PRODUCT_QTY'],
				'PRODUCT_PRICE'			=>$data['PRODUCT_PRICE'],
				'MEMBER_PRICE'			=>$data['MEMBER_PRICE'],
				
				'REMAIN_QTY'			=>$data['PRODUCT_QTY'],
				'REMAIN_PRICE'			=>$data['PRODUCT_PRICE'] - $data['MEMBER_PRICE'],
				
				'CREATER'				=>$data['CREATER'],
				'UPDATER'				=>$data['UPDATER']
			)
		);
		
		if ($data['PRODUCT_TYPE'] == "S") {
			$parent_idx = $db->last_id();
			if (isset($parent_idx)) {
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
						PRODUCT_CODE,
						PRODUCT_NAME,
						
						OPTION_IDX,
						BARCODE,
						OPTION_NAME,
						
						PRODUCT_QTY,
						PRODUCT_PRICE,
						
						REMAIN_QTY,
						REMAIN_PRICE,
						
						CREATER,
						UPDATER
					)
					SELECT
						?						AS ORDER_IDX,
						TP.ORDER_CODE			AS ORDER_CODE,
						TP.ORDER_PRODUCT_CODE	AS ORDER_PRODUCT_CODE,
						TP.ORDER_STATUS			AS ORDER_STATUS,
						
						TP.PRODUCT_IDX			AS PRODUCT_IDX,
						TP.PRODUCT_TYPE			AS PRODUCT_TYPE,
						?						AS PARENT_IDX,
						TP.PRODUCT_CODE			AS PRODUCT_CODE,
						TP.PRODUCT_NAME			AS PRODUCT_NAME,
						
						TP.OPTION_IDX			AS OPTION_IDX,
						TP.BARCODE				AS BARCODE,
						TP.OPTION_NAME			AS OPTION_NAME,
						
						TP.PRODUCT_QTY			AS PRODUCT_QTY,
						TP.PRODUCT_PRICE		AS PRODUCT_PRICE,
						
						TP.PRODUCT_QTY			AS REMAIN_QTY,
						TP.PRODUCT_PRICE		AS REMAIN_PRICE,
						
						TP.CREATER				AS CREATER,
						TP.UPDATER				AS UPDATER
					FROM
						TMP_ORDER_PRODUCT TP
					WHERE
						TP.PARENT_IDX = ?
				";
				
				$db->query($insert_order_product_sql,array($order_idx,$parent_idx,$data['TP_IDX']));
			}
		}
	}
}

/* 5-3. [적립금 테이블] 사용 적립금 차감 처리 */
function addMileage_DEC($db,$order_code) {
	$insert_mileage_info_dec_sql = "
		INSERT INTO
			MILEAGE_INFO
		(
			COUNTRY,
			MEMBER_IDX,
			ID,
			MEMBER_LEVEL,
			
			MILEAGE_CODE,
			MILEAGE_UNUSABLE,
			MILEAGE_USABLE_INC,
			MILEAGE_USABLE_DEC,
			MILEAGE_BALANCE,
			ORDER_CODE,
			ORDER_PRODUCT_CODE,
			DATE_CODE,
			MILEAGE_USABLE_DATE,
			
			CREATER,
			UPDATER
		)
		SELECT
			OI.COUNTRY				AS COUNTRY,
			OI.MEMBER_IDX			AS MEMBER_IDX,
			OI.MEMBER_ID			AS ID,
			OI.MEMBER_LEVEL			AS MEMBER_LEVEL,
			'PDC'					AS MILEAGE_CODE,
			0						AS MILEAGE_UNUSABLE,
			0						AS MILEAGE_USABLE_INC,
			OI.PRICE_MILEAGE_POINT	AS MILEAGE_USABLE_DEC,
			(
				SELECT
					IFNULL(
						(S_MI.MILEAGE_BALANCE - OI.PRICE_MILEAGE_POINT)
						,0
					)
				FROM
					MILEAGE_INFO S_MI
				WHERE
					S_MI.COUNTRY = OI.COUNTRY AND
					S_MI.MEMBER_IDX = OI.MEMBER_IDX
				ORDER BY
					S_MI.IDX DESC
				LIMIT
					0,1
			)						AS MILEAGE_BALANCE,
			OI.ORDER_CODE			AS ORDER_CODE,
			NULL					AS ORDER_PRODUCT_CODE,
			NULL					AS DATE_CODE,
			NULL					AS MILEAGE_USABLE_DATE,
			OI.MEMBER_ID			AS CREATER,
			OI.MEMBER_ID			AS UPDATER
		FROM
			TMP_ORDER_INFO OI
		WHERE
			ORDER_CODE = ?
	";

	$db->query($insert_mileage_info_dec_sql,$order_code);
}

/* 5-4. [적립금 테이블] 주문 적립금 증가 처리 */
function addMileage_INC($db,$order_code,$mileage_per) {
	$select_tmp_order_product_sql = "
		SELECT
			TP.ORDER_PRODUCT_CODE		AS ORDER_PRODUCT_CODE,
			
			TP.PRODUCT_QTY				AS PRODUCT_QTY,
			PR.SALES_PRICE_KR			AS SALES_PRICE_KR,
			PR.SALES_PRICE_EN			AS SALES_PRICE_EN,
			
			PR.MILEAGE_FLG				AS MILEAGE_FLG,
			IFNULL(
				J_PM.MILEAGE_PER,0
			)							AS MILEAGE_PER
		FROM
			TMP_ORDER_PRODUCT TP
			
			LEFT JOIN SHOP_PRODUCT PR ON
			TP.PRODUCT_IDX = PR.IDX
			
			LEFT JOIN (
				SELECT
					S_PM.PRODUCT_IDX	AS PRODUCT_IDX,
					S_PM.MILEAGE_PER	AS MILEAGE_PER
				FROM
					PRODUCT_MILEAGE S_PM
				WHERE
					LEVEL_IDX = ?
			) AS J_PM ON
			TP.PRODUCT_IDX = J_PM.PRODUCT_IDX
		WHERE
			TP.ORDER_CODE = ? AND
			TP.PARENT_IDX = 0
	";
	
	$db->query($select_tmp_order_product_sql,array($_SESSION['LEVEL_IDX'],$order_code));
	
	foreach($db->fetch() as $data) {
		$mileage_UNU = 0;
		
		$sales_price = $data['SALES_PRICE_EN'] * $data['PRODUCT_QTY'];
		if ($data['MILEAGE_FLG'] == true && $data['MILEAGE_PER'] > 0) {
			$mileage_UNU = $sales_price * ($data['MILEAGE_PER'] / 100);
		} else {
			if ($mileage_per > 0) {
				$mileage_UNU = $sales_price * ($mileage_per / 100);
			}
		}
		
		if ($mileage_UNU > 0) {
			$insert_mileage_info_inc_sql = "
				INSERT INTO
					MILEAGE_INFO
				(
					COUNTRY,
					MEMBER_IDX,
					ID,
					MEMBER_LEVEL,

					MILEAGE_CODE,
					MILEAGE_UNUSABLE,
					MILEAGE_USABLE_INC,
					MILEAGE_USABLE_DEC,
					MILEAGE_BALANCE,
					ORDER_CODE,
					ORDER_PRODUCT_CODE,
					DATE_CODE,
					MILEAGE_USABLE_DATE,
					CREATER,
					UPDATER
				)
				SELECT
					TI.COUNTRY				AS COUNTRY,
					TI.MEMBER_IDX			AS MEMBER_IDX,
					TI.MEMBER_ID			AS ID,
					TI.MEMBER_LEVEL			AS MEMBER_LEVEL,
					
					'PIN'					AS MILEAGE_CODE,
					?						AS MILEAGE_UNUSABLE,
					0						AS MILEAGE_USABLE_INC,
					0						AS MILEAGE_USABLE_DEC,
					IFNULL(
						(
							SELECT
								S_MI.MILEAGE_BALANCE
							FROM
								MILEAGE_INFO S_MI
							WHERE
								S_MI.COUNTRY	= TI.COUNTRY AND
								S_MI.MEMBER_IDX	= TI.MEMBER_IDX
							ORDER BY
								S_MI.IDX DESC
							LIMIT
								0,1
						),0
					)						AS MILEAGE_BALANCE,
					TP.ORDER_CODE			AS ORDER_CODE,
					TP.ORDER_PRODUCT_CODE	AS ORDER_PRODUCT_CODE,
					'7D'					AS DATE_CODE,
					NULL					AS MILEAGE_USABLE_DATE,
					TI.CREATER				AS CREATER,
					TI.UPDATER				AS UPDATER
				FROM
					TMP_ORDER_PRODUCT TP
					
					LEFT JOIN TMP_ORDER_INFO TI ON
					TP.ORDER_IDX = TI.IDX
				WHERE
					TP.ORDER_PRODUCT_CODE = ? AND
					TP.PRODUCT_TYPE NOT REGEXP 'V|D'
			";
			
			$db->query($insert_mileage_info_inc_sql,array($mileage_UNU,$data['ORDER_PRODUCT_CODE']));
		}
	}
}

function setPG_payment($db,$order_code,$key,$amount) {
	$pg_info = null;

	$pg_mid				= null;
	$pg_payment			= null;
	$pg_payment_key		= null;
	$pg_issue_code		= null;
	$pg_card_number		= null;
	$pg_status			= null;
	$pg_date			= null;
	$pg_price			= null;
	$pg_currency		= null;
	$pg_receipt_url		= null;
	$pg_remain_price	= 0;
	
	$suppliedAmount		= null;
	$vat				= null;

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
				\"paymentKey\":\"".$key."\",
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

		if ($pg_status == "DONE" && $result['balanceAmount'] == $amount) {
			if (isset($result['mId'])) {
				$pg_mid = $result['mId'];
			}

			if (isset($result['method'])) {
				$pg_payment = $result['method'];
			}

			if (isset($result['paymentKey'])) {
				$pg_payment_key = $result['paymentKey'];
			}

			if (isset($result['card']['issuerCode'])) {
				$pg_issue_code = $result['card']['issuerCode'];
			}
			
			if (isset($result['card']['number'])) {
				$pg_card_number = $result['card']['number'];
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

			if (isset($result['receipt']['url'])) {
				$pg_receipt_url = $result['receipt']['url'];
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

function getSEND_param($db,$order_code) {
	$send_param = array();
	
	$select_send_param_sql = "
		SELECT
			OI.ORDER_CODE		AS ORDER_CODE,
			OI.ORDER_TITLE		AS ORDER_TITLE,
			OI.PRICE_TOTAL		AS PRICE_TOTAL,
			DATE_FORMAT(
				OI.CREATE_DATE,
				'%Y.%m.%d %H:%i'
			)					AS CREATE_DATE
		FROM
			ORDER_INFO OI
		WHERE
			OI.ORDER_CODE = ?
	";
	
	$db->query($select_send_param_sql,array($order_code));
	
	foreach($db->fetch() as $data) {
		$send_param = array(
			'order_code'		=>$data['ORDER_CODE'],
			'order_title'		=>$data['ORDER_TITLE'],
			'price_total'		=>$data['PRICE_TOTAL'],
			'create_date'		=>$data['CREATE_DATE']
		);
	}
	
	return $send_param;
}

?>

<div style="height:100vh;"></div>