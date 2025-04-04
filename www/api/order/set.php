<?php
/*
 +=============================================================================
 | 
 | 결제하기 화면 - PG사 결제처리용 임시 주문정보 등록
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.12.12
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

include_once $_CONFIG['PATH']['API'].'_legacy/common.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (isset($_SERVER['HTTP_COUNTRY']) && isset($_SESSION['MEMBER_IDX'])) {
	if (isset($basket_idx) && strlen($basket_idx) > 0) {
		$basket_idx = explode(",",$basket_idx);

		$db->begin_transaction();
		
		try {
			/* 1. 결제하려는 쇼핑백 상품의 예외처리 (쇼핑몰 예외처리 조건 수정) */
			$cnt_basket = $db->count(
				"BASKET_INFO",
				"
					IDX IN (".implode(',',array_fill(0,count($basket_idx),'?')).") AND
					MEMBER_IDX = ? AND
					DEL_FLG = FALSE
				",
				array_merge($basket_idx,array($_SESSION['MEMBER_IDX']))
			);
			
			if ($cnt_basket > 0) {
				/* 2. 임시 주문정보 삭제처리 - /api/_legacy/common.php */
				deleteOrder_tmp($db,$_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX']);

				/* 3. 로그인 한 회원의 등급별 적립/할인율 조회 - /api/_legacy/common.php */
				$discount_per = 0;

				$member_per = checkMember_percentage($db);
				if (sizeof($member_per) > 0 && isset($member_per['discount_per'])) {
					$discount_per = $member_per['discount_per'];
				}

				/* 4. 결제하려는 쇼핑백 상품의 재고 체크처리 */
				$check_stock = checkOrder_stock($db,$basket_idx);
				if ($check_stock != true) {
					$json_result['code'] = 303;
					$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_F_ERR_0116',array());
					
					echo json_encode($json_result);
					exit;
				}

				/* 5-1. 바우처 사용 시 보유 바우처 체크 처리 */
				$voucher_info = null;
				if (isset($voucher_idx) && intval($voucher_idx) > 0) {
					$check_voucher = checkOrder_voucher($db,$voucher_idx);
					if ($check_voucher != true) {
						$json_result['code'] = 302;
						$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_F_ERR_0117',array());
						
						echo json_encode($json_result);
						exit;
					}
					
					/* 5-2. 결제 시 사용 한 바우처 정보 조회 처리 */
					$voucher_info = getOrder_voucher($db,$voucher_idx);
				}

				/* 6. 적립금 사용 시 회원 적립금 체크처리 */
				if (isset($price_mileage_point) && intval($price_mileage_point) > 0) {
					$check_mileage = checkOrder_mileage($db,$price_mileage_point);
					if ($check_mileage != true) {
						$json_result['code'] = 301;
						$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_F_ERR_0118',array());
						
						echo json_encode($json_result);
						exit;
					}
				}

				/* 7. 쇼핑백 주문상품 조회 처리 */
				$select_basket_product_sql = "
					SELECT
						BI.IDX				AS BASKET_IDX,
						BI.PRODUCT_IDX		AS PRODUCT_IDX,
						PR.PRODUCT_TYPE		AS PRODUCT_TYPE,
						PR.PRODUCT_CODE		AS PRODUCT_CODE,
						
						PR.PRODUCT_NAME		AS PRODUCT_NAME,
						BI.OPTION_IDX		AS OPTION_IDX,
						BI.BARCODE			AS BARCODE,
						BI.OPTION_NAME		AS OPTION_NAME,
						
						BI.PRODUCT_QTY		AS PRODUCT_QTY,
						PR.SALES_PRICE_KR	AS SALES_PRICE_KR,
						PR.SALES_PRICE_EN	AS SALES_PRICE_EN,
						
						PR.DISCOUNT_FLG		AS DISCOUNT_FLG,
						IFNULL(
							J_PD.DISCOUNT_PER,0
						)					AS DISCOUNT_PER
					FROM
						BASKET_INFO BI
						
						LEFT JOIN SHOP_PRODUCT PR ON
						BI.PRODUCT_IDX = PR.IDX
						
						LEFT JOIN (
							SELECT
								S_PD.PRODUCT_IDX	AS PRODUCT_IDX,
								S_PD.DISCOUNT_PER	AS DISCOUNT_PER
							FROM
								PRODUCT_DISCOUNT S_PD
							WHERE
								S_PD.LEVEL_IDX = ?
						) AS J_PD ON
						PR.IDX = J_PD.PRODUCT_IDX
					WHERE
						BI.IDX IN (".implode(',',array_fill(0,count($basket_idx),'?')).") AND
						BI.DEL_FLG = FALSE
					ORDER BY
						BI.IDX DESC
				";

				$db->query($select_basket_product_sql,array_merge(array($_SESSION['LEVEL_IDX']),$basket_idx));

				$basket_product = array();

				$price_product	= 0;
				$price_member	= 0;
				$price_discount	= 0;
				$price_delivery	= 0;

				foreach($db->fetch() as $data) {
					$sales_price = $data['SALES_PRICE_'.$_SERVER['HTTP_COUNTRY']] * $data['PRODUCT_QTY'];

					$member_price = 0;
					if ($data['DISCOUNT_FLG'] == true && $data['DISCOUNT_PER'] > 0) {
						/* 회원 할인가격 설정 - 상품의 개별 할인율 적용 */
						$member_price = $sales_price * ($data['DISCOUNT_PER'] / 100);
					} else {
						/* 회원 할인가격 설정 - 상품의 개별 할인율 미적용 */
						if ($discount_per > 0) {
							$member_price = $sales_price * ($discount_per / 100);
						} else {
							$member_price = 0;
						}
					}

					/* 영문몰 금액단위 설정 */
					if ($_SERVER['HTTP_COUNTRY'] == "EN") {
						$sales_price	= round($sales_price,1);
						$member_price	= round($member_price,1);
					}

					$basket_product[] = array(
						'basket_idx'		=>$data['BASKET_IDX'],
						'product_idx'		=>$data['PRODUCT_IDX'],
						'product_code'		=>$data['PRODUCT_CODE'],
						'product_type'		=>$data['PRODUCT_TYPE'],
						'product_name'		=>$data['PRODUCT_NAME'],
						'option_idx'		=>$data['OPTION_IDX'],
						'barcode'			=>$data['BARCODE'],
						'option_name'		=>$data['OPTION_NAME'],
			
						'product_qty'		=>$data['PRODUCT_QTY'],
						'sales_price'		=>$sales_price,
						'member_price'		=>$member_price
					);
					
					$price_product	+= $sales_price;
					$price_member	+= $member_price;
				}

				/* 적립금 예외처리 */
				/* 쇼핑백 결제상품의 총 금액이 한국몰/영문몰의 기준금액 (80,000 KRW / 300 USD) 미만일 경우 적립금 사용금액 미사용 처리 */
				if (($_SERVER['HTTP_COUNTRY'] == "KR"  && $price_product < 80000) || $_SERVER['HTTP_COUNTRY'] == "EN"  && $price_product < 300) {
					$price_mileage_point = 0;
				}

				/* 8. 바우처 할인금액 계산 */
				if ($voucher_info != null) {
					$sale_type	= $voucher_info['sale_type'];
					$sale_price	= $voucher_info['sale_price'];
					
					if ($sale_type == "PRC") {
						/* 바우처 할인금액 - 고정금액 */
						$price_discount = $sale_price;
					} else if ($sale_type = "PER") {
						/* 바우처 할인금액 - 결제상품 총 금액의 퍼센테이지 */
						$price_discount = ($price_product * ($sale_price / 100));
					}
				}

				/* 9. 주문 배송지 정보 체크처리 */
				$check_to = checkOrder_to($db,$order_to_idx);
				if ($check_to != true) {
					$json_result['code'] = 404;
					$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_F_ERR_0040',array());
					
					echo json_encode($json_result);
					exit;
				}

				/* 결제 회원 기본 배송지 및 배송금액 계산 처리 - /api/_legacy/common.php */
				$order_to = getOrder_to($db,$_SERVER['HTTP_COUNTRY'],$order_to_idx);
				if ($_SERVER['HTTP_COUNTRY'] == "KR" && $price_product < 80000 ) {
					/* 한국몰 && 상품 판매금액 80,000 KRW 미만 */
					$price_delivery = $order_to['delivery_price'];
				} else if ($_SERVER['HTTP_COUNTRY'] == "EN" && $price_product < 300) {
					/* 영문몰 && 상품 판매금액 300 USD 미만 */
					$price_delivery = $order_to['delivery_price'];
				}

				/* 10. PG사 주문 결제금액 계산 */
				$price_total = $price_product - $price_member - $price_mileage_point + $price_delivery;

				/* 바우처 전액결제 건 예외처리 추가 */
				if ($price_discount > $price_total) {
					$price_discount = $price_total;
					$price_total = 0;
				} else {
					$price_total -= $price_discount;
				}

				/* 11. 주문 코드 생성 */
				$order_code = date("Ymd-His").substr(microtime(),2,1);

				/* 12. 주문 타이틀 생성 */
				$order_title = setOrder_title($basket_product);

				/* 13. [임시 주문 정보] 테이블 등록처리 */

				/* 13-1. 영문몰 금액단위 변환 처리 */
				if ($_SERVER['HTTP_COUNTRY'] == "EN") {
					$price_product			= round($price_product,1);
					$price_member			= round($price_member,1);
					$price_discount			= round($price_discount,1);
					$price_mileage_point	= round($price_mileage_point,1);
					$price_delivery			= round($price_delivery,1);
					$price_total			= round($price_total,1);
				}

				/* 13-2. 주문 메모 설정 */
				if ($delivery_message != null) {
					$memo = $delivery_message;
				} else {
					$memo = setOrder_memo($db,$order_memo);
				}

				/* 13-3. PARAM 설정::임시 주문 정보 테이블 */
				$param_info = array(
					'order_code'			=>$order_code,
					'order_title'			=>$order_title,
					
					'price_product'			=>$price_product,
					'price_member'			=>$price_member,
					'price_discount'		=>$price_discount,
					'price_mileage_point'	=>$price_mileage_point,
					'price_delivery'		=>$price_delivery,
					'price_total'			=>$price_total,
					
					'order_to'				=>$order_to,
					
					'order_memo'			=>$memo,
					
					'basket_idx'			=>implode(",",$basket_idx)
				);

				$order_idx = addOrder_info($db,$param_info);
				if (isset($order_idx)) {
					/* 14. [임시 주문 상품] 테이블 등록처리 */

					/* 14-1. PARAM 설정::임시 주문 상품 정보 테이블 */
					$param_product = array(
						'order_idx'			=>$order_idx,
						'order_code'		=>$order_code,
						
						'basket_product'	=>$basket_product
					);

					$cnt_product = addOrder_product($db,$param_product);

					/* 14-2. 임시 주문 상품 정보 테이블 - 바우처 정보 등록 처리 */
					if ($price_discount > 0 && $voucher_info != null) {
						$param_voucher = array(
							'order_idx'			=>$order_idx,
							'order_code'		=>$order_code,
							'cnt_product'		=>$cnt_product,
							
							'price_discount'	=>$price_discount,
							'voucher_info'		=>$voucher_info,
							'member_id'			=>$_SESSION['MEMBER_ID']
						);
						
						addOrder_voucher($db,$param_voucher);
						
						$cnt_product++;
					}

					/* 14-3. 임시 주문 상품 정보 테이블 - 배송금액 정보 등록 처리 */
					if ($price_delivery > 0) {
						$param_delivery = array(
							'order_idx'			=>$order_idx,
							'order_code'		=>$order_code,
							'cnt_product'		=>$cnt_product,
							
							'price_delivery'	=>$price_delivery,
							'member_id'			=>$_SESSION['MEMBER_ID']
						);
						
						addOrder_delivery($db,$param_delivery);
					}

					$db->commit();

					$json_result['data'] = array(
						'country'				=>$_SERVER['HTTP_COUNTRY'],
						'order_code'			=>$order_code,
						'order_title'			=>$order_title,
						
						'price_product'			=>$price_product,
						'price_mileage_point'	=>$price_mileage_point,
						'price_discount'		=>$price_discount,
						'price_delivery'		=>$price_delivery,
						'price_total'			=>$price_total,
						
						'member_name'			=>$_SESSION['MEMBER_NAME']
					);
				}
			}
		} catch (mysqli_sql_exception $e) {
			print_r($e);
			
			$db->rollback();
			
			$json_result['code'] = 301;
			$json_result['msg'] = "주문정보 등록처리중 오류가 발생했습니다.";
		}
	} else {
		/* 쇼핑백 상품 예외처리 - 결제하려는 상품이 존재하지 않는 경우 - PARAM:쇼핑백 IDX 미설정  */
		$json_result['code'] = 402;
		$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_F_ERR_0115',array());
		
		echo json_encode($json_result);
		exit;
	}
} else {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0018',array());
	
	echo json_encode($json_result);
	exit;
}

/* 4. 결제하려는 쇼핑백 상품의 재고 체크처리 */
function checkOrder_stock($db,$basket_idx) {
	$check_stock = false;
	
	$select_basket_info_sql = "
		SELECT
			BI.PRODUCT_IDX			AS PRODUCT_IDX,
			BI.OPTION_IDX			AS OPTION_IDX,
			BI.PRODUCT_QTY			AS PRODUCT_QTY,
			
			V_ST.PURCHASEABLE_QTY	AS LIMIT_QTY
		FROM
			BASKET_INFO BI
			
			LEFT JOIN V_STOCK AS V_ST ON
			BI.PRODUCT_IDX = V_ST.PRODUCT_IDX AND
			BI.OPTION_IDX = V_ST.OPTION_IDX
		WHERE
			(
				BI.PRODUCT_TYPE = 'B' AND
				(BI.IDX IN (".implode(',',array_fill(0,count($basket_idx),'?')).")) OR
				(BI.PARENT_IDX IN (".implode(',',array_fill(0,count($basket_idx),'?'))."))
			) AND
			BI.DEL_FLG = FALSE
	";
	
	$db->query($select_basket_info_sql,array_merge($basket_idx,$basket_idx));
	
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

/* 5-1. 바우처 사용 시 보유 바우처 체크 처리 */
function checkOrder_voucher($db,$voucher_idx) {
	$check_voucher = false;
	
	$cnt_voucher = $db->count(
		"VOUCHER_ISSUE",
		"
			IDX			= ? AND
			COUNTRY		= ? AND
			MEMBER_IDX	= ? AND
			(NOW() BETWEEN USABLE_START_DATE AND USABLE_END_DATE) AND
			USED_FLG = FALSE
		",
		array($voucher_idx,$_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX'])
	);
	
	if ($cnt_voucher > 0) {
		$check_voucher = true;
	}
	
	return $check_voucher;
}

/* 5-2. 결제 시 사용 한 바우처 정보 조회 처리 */
function getOrder_voucher($db,$voucher_idx) {
	$voucher_info = null;
	
	$select_voucher_issue_sql = "
		SELECT
			VI.IDX				AS VOUCHER_IDX,
			VM.VOUCHER_NAME		AS VOUCHER_NAME,
			VM.SALE_TYPE		AS SALE_TYPE,
			VM.SALE_PRICE		AS SALE_PRICE
		FROM
			VOUCHER_ISSUE VI
			LEFT JOIN VOUCHER_MST VM ON
			VI.VOUCHER_IDX = VM.IDX
		WHERE
			VI.IDX = ? AND
			VI.COUNTRY = ? AND
			VI.MEMBER_IDX = ?
	";
	
	$db->query($select_voucher_issue_sql,array($voucher_idx,$_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX']));
	
	foreach($db->fetch() as $data) {
		$voucher_info = array(
			'voucher_idx'		=>$data['VOUCHER_IDX'],
			'voucher_name'		=>$data['VOUCHER_NAME'],
			'sale_type'			=>$data['SALE_TYPE'],
			'sale_price'		=>$data['SALE_PRICE']
		);
	}
	
	return $voucher_info;
}

/* 6. 적립금 사용 시 회원 적립금 체크처리 */
function checkOrder_mileage($db,$param_mileage) {
	$check_mileage = false;
	
	$select_member_mileage_sql = "
		SELECT
			IFNULL(
				MI.MILEAGE_BALANCE,0
			)		AS MILEAGE_BALANCE
		FROM
			MILEAGE_INFO MI
		WHERE
			MI.COUNTRY = ? AND
			MI.MEMBER_IDX = ?
		ORDER BY
			MI.IDX DESC
		LIMIT
			0,1
	";
	
	$db->query($select_member_mileage_sql,array($_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX']));

	foreach ($db->fetch() as $data) {
		if ($data['MILEAGE_BALANCE'] > 0 && $data['MILEAGE_BALANCE'] >= $param_mileage) {
			$check_mileage = true;
		}
	}
	
	return $check_mileage;
}

/* 9. 주문 배송지 정보 체크처리 */
function checkOrder_to($db,$order_to_idx) {
	$check_to = false;
	
	$cnt_to = $db->count("ORDER_TO","IDX = ? AND COUNTRY = ? AND MEMBER_IDX = ?",array($order_to_idx,$_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX']));
	if ($cnt_to > 0) {
		$check_to = true;
	}
	
	return $check_to;
}

/* 12. 주문 타이틀 생성 */
function setOrder_title($basket_product) {
	$order_title = null;

	$suffix = array(
		'KR'		=>"외",
		'EN'		=>"and"
	);

	$unit	= array(
		'KR'		=>"건",
		'EN'		=>"items"
	);
	
	if (count($basket_product) > 1) {
		$order_title = $basket_product[0]['product_name']." ".$suffix[$_SERVER['HTTP_COUNTRY']]." ".(count($basket_product) - 1).$unit[$_SERVER['HTTP_COUNTRY']];
	} else {
		$order_title = $basket_product[0]['product_name'];
	}

	return $order_title;
}

/* 13-2. 주문 메모 설정 */
function setOrder_memo($db,$memo_idx) {
	$order_memo = "";

	if ($memo_idx > 0 && $memo_idx != 1 && $memo_idx != 8) {
		$memo = $db->get("ORDER_MEMO","IDX = ?",array($memo_idx));
		if (isset($memo)) {
			$order_memo = $memo[0]['MEMO_TXT_'.$_SERVER['HTTP_COUNTRY']];
		}
	}

	return $order_memo;
}

/* 13. [임시 주문 정보] 테이블 등록처리 */
function addOrder_info($db,$data) {
	$order_to = $data['order_to'];
	
	$db->insert(
		"TMP_ORDER_INFO",
		array(
			'COUNTRY'				=>$_SERVER['HTTP_COUNTRY'],
			'ORDER_CODE'			=>$data['order_code'],
			'ORDER_TITLE'			=>$data['order_title'],
			'ORDER_STATUS'			=>'PCP',
			
			'MEMBER_IDX'			=>$_SESSION['MEMBER_IDX'],
			'MEMBER_ID'				=>$_SESSION['MEMBER_ID'],
			'MEMBER_NAME'			=>$_SESSION['MEMBER_NAME'],
			'MEMBER_MOBILE'			=>$_SESSION['TEL_MOBILE'],
			'MEMBER_LEVEL'			=>$_SESSION['LEVEL_IDX'],
			
			'PRICE_PRODUCT'			=>$data['price_product'],
			'PRICE_MEMBER'			=>$data['price_member'],
			'PRICE_MILEAGE_POINT'	=>$data['price_mileage_point'],
			'PRICE_DISCOUNT'		=>$data['price_discount'],
			'PRICE_DELIVERY'		=>$data['price_delivery'],
			'PRICE_TOTAL'			=>$data['price_total'],
			
			'TO_IDX'				=>$order_to['to_idx'],
			'TO_PLACE'				=>$order_to['to_place'],
			'TO_NAME'				=>$order_to['to_name'],
			'TO_MOBILE'				=>$order_to['to_mobile'],
			'TO_ZIPCODE'			=>$order_to['to_zipcode'],
			'TO_LOT_ADDR'			=>$order_to['to_lot_addr'],
			'TO_ROAD_ADDR'			=>$order_to['to_road_addr'],
			'TO_DETAIL_ADDR'		=>$order_to['to_detail_addr'],
			
			'ORDER_MEMO'			=>$data['order_memo'],
			
			'BASKET_IDX'			=>$data['basket_idx'],
			
			'CREATER'				=>$_SESSION['MEMBER_ID'],
			'UPDATER'				=>$_SESSION['MEMBER_ID']
		)
	);
	
	$order_idx = $db->last_id();
	
	return $order_idx;
}

/* 14. [임시 주문 상품] 테이블 등록처리 */
function addOrder_product($db,$data) {
	$cnt_product = 1;
	
	$basket_product = $data['basket_product'];	
	foreach($basket_product as $product) {
		$db->insert(
			"TMP_ORDER_PRODUCT",
			array(
				'ORDER_IDX'				=>$data['order_idx'],
				'ORDER_CODE'			=>$data['order_code'],
				'ORDER_PRODUCT_CODE'	=>$data['order_code']."-".$cnt_product,
				'ORDER_STATUS'			=>'PCP',
				
				'PRODUCT_IDX'			=>$product['product_idx'],
				'PRODUCT_TYPE'			=>$product['product_type'],
				'PRODUCT_CODE'			=>$product['product_code'],
				'PRODUCT_NAME'			=>$product['product_name'],
				
				'OPTION_IDX'			=>$product['option_idx'],
				'BARCODE'				=>$product['barcode'],
				'OPTION_NAME'			=>$product['option_name'],
				
				'PRODUCT_QTY'			=>$product['product_qty'],
				'PRODUCT_PRICE'			=>$product['sales_price'],
				'MEMBER_PRICE'			=>$product['member_price'],
				
				'CREATER'				=>$_SESSION['MEMBER_ID'],
				'UPDATER'				=>$_SESSION['MEMBER_ID']
			)
		);
		
		$cnt_product++;
		
		if ($product['product_type'] == "S") {
			$parent_idx = $db->last_id();
			
			$select_basket_set_product_sql = "
				INSERT INTO
					TMP_ORDER_PRODUCT
				(
					ORDER_IDX,
					ORDER_CODE,
					ORDER_PRODUCT_CODE,
					
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
					
					CREATER,
					UPDATER
				)
				SELECT
					?						AS ORDER_IDX,
					?						AS ORDER_CODE,
					?						AS ORDER_PRODUCT_CODE,
					
					BI.PRODUCT_IDX			AS PRODUCT_IDX,
					BI.PRODUCT_TYPE			AS PRODUCT_TYPE,
					?						AS PARENT_IDX,
					BI.PRODUCT_CODE			AS PRODUCT_CODE,
					BI.PRODUCT_NAME			AS PRODUCT_NAME,
					
					BI.OPTION_IDX			AS OPTION_IDX,
					BI.BARCODE				AS BARCODE,
					BI.OPTION_NAME			AS OPTION_NAME,
					
					?						AS PRODUCT_QTY,
					(
						? * PR.SALES_PRICE_".$_SERVER['HTTP_COUNTRY']."
					)						AS PRODUCT_PRICE,
					
					BI.CREATER				AS CREATER,
					BI.UPDATER				AS UPDATER
				FROM
					BASKET_INFO BI
					
					LEFT JOIN SHOP_PRODUCT PR ON
					BI.PRODUCT_IDX = PR.IDX
				WHERE
					PARENT_IDX = ?
				ORDER BY
					BI.IDX DESC
			";
			
			$db->query(
				$select_basket_set_product_sql,
				array(
					$data['order_idx'],
					$data['order_code'],
					$data['order_code']."-".$cnt_product,
					$parent_idx,
					$product['product_qty'],
					$product['product_qty'],
					$product['basket_idx']
				)
			);
			
			$cnt_product++;
		}
	}
	
	return $cnt_product;
}

/* 14-2. 임시 주문 상품 정보 테이블 - 바우처 정보 등록 처리 */
function addOrder_voucher($db,$data) {
	$voucher_info = $data['voucher_info'];
	
	$db->insert(
		"TMP_ORDER_PRODUCT",
		array(
			'ORDER_IDX'				=>$data['order_idx'],
			'ORDER_CODE'			=>$data['order_code'],
			'ORDER_PRODUCT_CODE'	=>$data['order_code']."-".$data['cnt_product'],
			
			'PRODUCT_IDX'			=>$voucher_info['voucher_idx'],
			'PRODUCT_TYPE'			=>'V',
			'PRODUCT_CODE'			=>'VOU-P-XXXXXXXXXX',
			'PRODUCT_NAME'			=>$voucher_info['voucher_name'],
			
			'OPTION_IDX'			=>0,
			'BARCODE'				=>'VOU-P-XXXXXXXXXX',
			'OPTION_NAME'			=>$voucher_info['voucher_name'],
			
			'PRODUCT_QTY'			=>1,
			'PRODUCT_PRICE'			=>$data['price_discount'],
			
			'CREATER'				=>$data['member_id'],
			'UPDATER'				=>$data['member_id']
		)
	);
}

/* 14-3. 임시 주문 상품 정보 테이블 - 배송금액 정보 등록 처리 */
function addOrder_delivery($db,$data) {
	$db->insert(
		"TMP_ORDER_PRODUCT",
		array(
			'ORDER_IDX'				=>$data['order_idx'],
			'ORDER_CODE'			=>$data['order_code'],
			'ORDER_PRODUCT_CODE'	=>$data['order_code']."-".$data['cnt_product'],
			
			'PRODUCT_IDX'			=>0,
			'PRODUCT_TYPE'			=>'D',
			'PRODUCT_CODE'			=>'DLV-P-XXXXXXXXX',
			'PRODUCT_NAME'			=>"주문 배송비",
			
			'OPTION_IDX'			=>0,
			'BARCODE'				=>'DLV-P-XXXXXXXXX',
			'OPTION_NAME'			=>"주문 배송비",
			
			'PRODUCT_QTY'			=>1,
			'PRODUCT_PRICE'			=>$data['price_delivery'],
			
			'CREATER'				=>$data['member_id'],
			'UPDATER'				=>$data['member_id']
		)
	);
}

?>