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

include_once("/var/www/www/api/common.php");
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

$price_mileage = 0;
if (isset($_POST['price_mileage'])) {
	$price_mileage = $_POST['price_mileage'];
}

if ($member_idx > 0 && isset($order_code)) {
	try {
		//[주문 정보 테이블] 결제정보 취득
		$order_pg_info = getOrderPgInfo($db,$order_code);
			
		//[주문 취소 테이블] 누적 환불금액 계산
		$order_cancel_price = getOrderCancelPrice($db,$order_code);
		//[주문취소] 하려는 주문상품의 총 금액 계산
		$param_cancel_price = getParamCancelPrice($db,$order_pg_info,$param_order_product,$order_code,$price_mileage);
		
		$pg_remain_price		= $order_pg_info['pg_remain_price'];	//PG사 환불가능 총 잔여금액
		$price_discount			= $order_pg_info['price_discount'] - $order_cancel_price['price_discount'];				//환불가능 총 잔여 바우처 할인금액
		
		//PG사 결제 취소 금액,배송비 계산처리
		$delivery_price = 0;		//주문 추가 배송비
		$delivery_return = 0;		//주문 반환 배송비
		$refund_price = 0;
		
		//[주문취소] 추가 배송비 발생이력 체크
		$cnt_OCC = $db->count("ORDER_PRODUCT_CANCEL","ORDER_CODE = '".$order_code."' AND DELIVERY_PRICE > 0");
		
		//[전체취소],[부분취소] 체크
		$check_result = checkTotalCancel($db,$order_code,$order_pg_info['price_product'],$param_cancel_price['price_product']);
		if ($check_result == true) {
			//[전체취소] - PG사 결제 취소 금액:환불 가능 금액
			$refund_price = $order_pg_info['pg_remain_price'];
			$delivery_return = ($param_cancel_price['price_mileage'] + $param_cancel_price['price_discount'] + $order_pg_info['pg_remain_price']) - $param_cancel_price['price_product'];
			
			if ($order_pg_info['price_discount'] > 0) {
				putVoucherInfo($db,$order_code);
			}
		} else {
			//[부분취소] - PG사 결제 취소 금액:PARAM 결제취소금액
			$refund_price = $param_cancel_price['price_cancel'];
			
			if ($order_pg_info['country'] == "KR") {
				//한국몰 결제금액 기준 배송비 계산
				//[주문취소] 후 환불가능금액이 8만원 미만이 되는 경우
				if (($order_pg_info['pg_remain_price'] + ($param_cancel_price['remain_mileage'] - $param_cancel_price['price_mileage']) + $param_cancel_price['price_discount']) - $param_cancel_price['price_cancel'] < 80000) {
					//[주문취소] 추가 배송비 발생이력이 없는 경우
					if ($cnt_OCC == 0) {
						//$delivery_price = $order_pg_info['price_delivery'];
						$delivery_price = 2500;
					}
				}
			} else {
				//[주문취소] 추가 배송비 발생이력이 없는 경우
				if ($cnt_OCC == 0) {
					//영문|중문몰 배송비 계산
					$delivery_price = $order_pg_info['price_delivery'];
				}
			}
		}
		
		$param_cancel_price['price_delivery'] = $delivery_price;
		$param_cancel_price['delivery_return'] = $delivery_return;
		$param_cancel_price['price_cancel'] = ($refund_price - $delivery_price);
		$param_cancel_price['price_refund'] = $refund_price;
		
		//환불가능 잔여 결제금액 체크
		$check_result = checkPgRemainPrice($pg_remain_price,$param_cancel_price['price_cancel']);
		if ($check_result['result'] != true) {
			$json_result['code'] = $check_result['code'];
			$json_result['msg'] = $check_result['msg'];
			
			echo json_encode($json_result);
			exit;
		}
		
		//환불가능 잔여 바우처 할인금액 체크
		$check_rsult = checkPgDiscount($price_discount,$param_cancel_price['price_discount']);
		if ($check_result['result'] != true) {
			$json_result['code'] = $check_result['code'];
			$json_result['msg'] = $check_result['msg'];
			
			echo json_encode($json_result);
			exit;
		}
		
		//환불가능 잔여 적립금 할인금액 체크
		$check_rsult = checkPgMileagePoint($price_mileage,$param_cancel_price['price_mileage']);
		if ($check_result['result'] != true) {
			$json_result['code'] = $check_result['code'];
			$json_result['msg'] = $check_result['msg'];
			
			echo json_encode($json_result);
			exit;
		}
		
		$pg_cancel_info = null;
		//[주문취소] 하려는 주문상품의 잔여 결제금액이 존재하는 경우
		if ($param_cancel_price['price_cancel'] > 0) {
			//PG사 환불처리
			$pg_cancel_info = orderPgCancel($order_pg_info['pg_payment_key'],$param_cancel_price['price_cancel']);
		}
		
		//[주문 취소 테이블],[주문 취소 상품 테이블] 등록처리
		//주문 갱신코드 생성
		$cnt_OCC = $db->count("ORDER_CANCEL","ORDER_CODE = '".$order_code."'");
		$order_update_code = $order_code."-C-".($cnt_OCC + 1);
		
		$product_info = getProductInfo($db,$order_pg_info,$param_order_product,$price_mileage,$delivery_price);
		
		//[주문 취소 상품 테이블] 등록 PARAM
		$param_product_cancel = array(
			'order_update_code'		=>$order_update_code,
			
			'product_info'			=>$product_info,
			
			'depth1_idx'			=>$depth1_idx,
			'depth2_idx'			=>$depth2_idx,
			'reason_memo'			=>$reason_memo,
			
			'member_id'				=>$member_id
		);
		
		//[주문 취소 상품] 테이블 등록처리
		addProductCancel($db,$param_product_cancel);
		
		//[주문 취소 테이블] 등록 PARAM
		$param_order_cancel = array(
			'order_code'			=>$order_code,
			'order_update_code'		=>$order_update_code,
			
			'param_cancel_price'	=>$param_cancel_price,
			'pg_cancel_info'		=>$pg_cancel_info,
			'price_mileage'			=>$price_mileage,
			
			'member_id'				=>$member_id
		);
		
		//[주문 취소 테이블] 등록처리
		$order_idx = addOrderCancel($db,$param_order_cancel);
		if (!empty($order_idx)) {
			//[주문 취소 상품 테이블] 갱신처리
			putProductCancel($db,$order_idx,$order_update_code,$member_id);
			
			//[주문 상품 테이블] 갱신처리
			putOrderProduct($db,$param_order_product,$member_id);
			
			//[주문 정보 테이블] 갱신처리
			if ($pg_cancel_info != null) {
				putPgRemainPrice($db,$order_code,$pg_cancel_info['pg_remain_price']);
			}
			
			if ($price_mileage > 0) {
				addCancelMileageInfo($db,$order_update_code,$price_mileage,$member_id);
			}
			
			if ($price_mileage > 0) {
				$cnt_order_product = $db->count("ORDER_PRODUCT","ORDER_CODE = '".$order_code."'");
				$order_product_code = $order_code."-".($cnt_order_product + 1);
				
				//주문 취소 적립금 등록처리
				if ($price_mileage > 0) {
					$param_mileage_price = array(
						'order_idx'				=>$order_idx,
						'order_code'			=>$order_code,
						'order_product_code'	=>$order_product_code,
						'order_update_code'		=>$order_update_code,
						'mileage_price'			=>$price_mileage,
						'depth1_idx'			=>$depth1_idx,
						'depth2_idx'			=>$depth2_idx,
						'reason_memo'			=>$reason_memo,
						'member_id'				=>$member_id
					);
					
					addProductCancelMileagePrice($db,$param_mileage_price);
				}
			}
			
			if ($delivery_price > 0 || $delivery_return > 0) {
				$cnt_order_product = $db->count("ORDER_PRODUCT","ORDER_CODE = '".$order_code."'");
				$order_product_code = $order_code."-".($cnt_order_product + 1);
				
				//주문 추가 배송비 등록처리
				if ($delivery_price > 0) {
					$param_delivery_price = array(
						'order_idx'				=>$order_idx,
						'order_code'			=>$order_code,
						'order_product_code'	=>$order_product_code,
						'order_update_code'		=>$order_update_code,
						'delivery_price'		=>$delivery_price,
						'depth1_idx'			=>$depth1_idx,
						'depth2_idx'			=>$depth2_idx,
						'reason_memo'			=>$reason_memo,
						'member_id'				=>$member_id
					);
					
					addProductCancelDeliveryPrice($db,$param_delivery_price);
				}
				
				//주문 반환 배송비 등록처리
				if ($delivery_return > 0) {
					$cnt_order_product = $db->count("ORDER_PRODUCT","ORDER_CODE = '".$order_code."'");
					$order_product_code = $order_code."-".($cnt_order_product + 1);
					
					$param_delivery_return = array(
						'order_idx'				=>$order_idx,
						'order_code'			=>$order_code,
						'order_product_code'	=>$order_product_code,
						'order_update_code'		=>$order_update_code,
						'delivery_return'		=>$delivery_return,
						'depth1_idx'			=>$depth1_idx,
						'depth2_idx'			=>$depth2_idx,
						'reason_memo'			=>$reason_memo,
						'member_id'				=>$member_id
					);
					
					addProductCancelDeliveryReturn($db,$param_delivery_return);
					$cnt_order_product++;
				}
			}
			
			//[주문 로그 테이블][주문 상품 로그 테이블] 등록처리
			addOrderLog($db,"OCC",$order_update_code,$member_id);
		} else {
			$json_result['code'] = 301;
			$json_result['msg'] = "주문 취소정보 등록처리중 오류가 발생했습니다.";
			
			echo json_encode($json_result);
			exit;
		}
	} catch (mysqli_sql_exception $exception) {
		$db->rollback();
		print_r($exception);
		
		$json_result['code'] = 302;
		$json_result['msg'] = '주문 취소처리중 오류가 발생했습니다.';
		
		echo json_encode($json_result);
		exit;
	}
} else {
	$json_result['code'] = 301;
	$json_result['msg'] = "부적절한 주문 정보가 선택되었습니다. 취소/환불 하려는 주문을 다시 선택해주세요.";
	
	echo json_encode($json_result);
	exit;
}

function getOrderCancelPrice($db,$order_code) {
	$order_cancel_price = array();
	
	$select_order_cancel_sql = "
		SELECT
			IFNULL(
				SUM(OC.PRICE_PRODUCT),0
			)		AS PRICE_PRODUCT,
			IFNULL(
				SUM(OC.PRICE_CANCEL),0
			)		AS PRICE_CANCEL,
			IFNULL(
				SUM(OC.PRICE_DISCOUNT),0
			)		AS PRICE_DISCOUNT,
			IFNULL(
				SUM(OC.PRICE_MILEAGE_POINT),0
			)		AS PRICE_MILEAGE_POINT
		FROM
			ORDER_CANCEL OC
		WHERE
			ORDER_CODE = '".$order_code."'
	";

	$db->query($select_order_cancel_sql);

	foreach($db->fetch() as $cancel_data) {
		$order_cancel_price = array(
			'price_product'			=>$cancel_data['PRICE_PRODUCT'],
			'price_cancel'			=>$cancel_data['PRICE_CANCEL'],
			'price_discount'		=>$cancel_data['PRICE_DISCOUNT'],
			'price_mileage_point'	=>$cancel_data['PRICE_MILEAGE_POINT']
		);
	}
	
	return $order_cancel_price;
}

function getParamCancelPrice($db,$order_pg_info,$param_order_product,$order_code,$price_mileage) {
	$param_cancel_price = null;
	
	$remain_mileage = 0;
	
	$select_remain_mileage_sql = "
		SELECT
			(
				SELECT
					OI.PRICE_MILEAGE_POINT AS PRICE_MILEAGE
				FROM
					ORDER_INFO OI
				WHERE
					OI.ORDER_CODE = '".$order_code."'
			)			AS INFO_MILEAGE,
			(
				SELECT
					IFNULL(
						SUM(OC.PRICE_MILEAGE_POINT),0
					)		AS PRICE_MILEAGE
				FROM
					ORDER_CANCEL OC
				WHERE
					OC.ORDER_CODE = '".$order_code."'
			)			AS CANCEL_MILEAGE
		FROM
			DUAL
	";
	
	$db->query($select_remain_mileage_sql);
	
	foreach($db->fetch() as $data_mileage) {
		$remain_mileage = $data_mileage['INFO_MILEAGE'] - $data_mileage['CANCEL_MILEAGE'];
	}
	
	$price_product = 0;
	$price_discount = 0;
	$price_cancel = 0;
	$price_refund = 0;
	
	for ($i=0; $i<count($param_order_product); $i++) {
		$data = $param_order_product[$i];
		
		$select_order_product_sql = "
			SELECT
				(
					(OP.PRODUCT_PRICE / OP.PRODUCT_QTY) * ".$data['product_qty']."
				)		AS PRODUCT_PRICE
			FROM
				ORDER_PRODUCT OP
			WHERE
				OP.IDX = ".$data['param_idx']."
		";
		
		$db->query($select_order_product_sql);
		
		foreach($db->fetch() as $product_data) {
			$product_price = $product_data['PRODUCT_PRICE'];
			
			$discount_price = 0;
			if ($order_pg_info['price_discount'] > 0) {
				$discount_price	= $product_price / $order_pg_info['price_product'] * $order_pg_info['price_discount'];
				$price_discount += $discount_price;
			}
			
			$price_product		+= $product_price;
			$price_cancel		+= ($product_price - $discount_price);
			$price_refund		+= $product_price;
		}
	}
	
	$param_cancel_price = array(
		'price_product'		=>$price_product,
		'price_discount'	=>$price_discount,
		'price_mileage'		=>$price_mileage,
		'remain_mileage'	=>$remain_mileage,
		'price_cancel'		=>$price_cancel - $price_mileage,
		'price_refund'		=>$price_refund
	);
	
	return $param_cancel_price;
}

function checkPgRemainPrice($pg_remain_price,$param_price) {
	$check_result = array();
	
	if ($pg_remain_price > 0) {
		//PG사 결제취소 잔여금액이 남아있는 경우
		if ($pg_remain_price < $param_price) {
			//[결제취소] 처리하려는 주문상품의 총 환불 금액이 PG사 결제취소 잔여금액보다 큰 경우
			$check_result['result'] = false;
			$check_result['code'] = 301;
			$check_result['msg'] = "잔여 결제금액보다 큰 금액을 환불할 수 없습니다.";
			
			return $check_result;
		}
	} else {
		//PG사 결제취소 잔여금액이 남아있지 않은 경우
		if ($param_price > 0) {
			//[결제취소] 처리하려는 주문상품 중 결제금액을 환불해주려는 경우 예외처리
			$check_result['result'] = false;
			$check_result['code'] = 302;
			$check_result['msg'] = "선택한 주문은 적립금 환불처리만 가능합니다.";
			
			return $check_result;
		}
	}
	
	$check_result['result'] = true;
	
	return $check_result;
}

function checkPgDiscount($price_discount,$param_price) {
	$check_result = array();
	
	if ($price_discount > 0) {
		//환불가능 잔여적립금이 남아있는 경우
		if ($price_discount < $param_price) {
			//[결제취소] 처리하려는 주문상품의 총 환불 적립금이 잔여 적립금보다 큰 경우
			$check_result['result'] = false;
			$check_result['code'] = 303;
			$check_result['msg'] = "잔여 적립금보다 큰 금액을 환불할 수 없습니다";
			
			return $check_result;
		}
	} else {
		//환불가능 잔여적립금이 남아있지 않은 경우
		if ($param_price > 0) {
			//[결제취소] 처리하려는 주문상품 중 적립금을 환불해주려는 경우 예외처리
			$check_result['result'] = false;
			$check_result['code'] = 304;
			$check_result['msg'] = "선택한 주문은 결제금액 환불처리만 가능합니다.";
			
			return $check_result;
		}
	}
	
	$check_result['result'] = true;
	
	return $check_result;
}

function checkPgMileagePoint($price_mileage_point,$param_price) {
	$check_result = array();
	
	if ($price_mileage_point > 0) {
		//환불가능 잔여적립금이 남아있는 경우
		if ($price_mileage_point < $param_price) {
			//[결제취소] 처리하려는 주문상품의 총 환불 적립금이 잔여 적립금보다 큰 경우
			$check_result['result'] = false;
			$check_result['code'] = 303;
			$check_result['msg'] = "잔여 적립금보다 큰 금액을 환불할 수 없습니다";
			
			return $check_result;
		}
	} else {
		//환불가능 잔여적립금이 남아있지 않은 경우
		if ($param_price > 0) {
			//[결제취소] 처리하려는 주문상품 중 적립금을 환불해주려는 경우 예외처리
			$check_result['result'] = false;
			$check_result['code'] = 304;
			$check_result['msg'] = "선택한 주문은 결제금액 환불처리만 가능합니다.";
			
			return $check_result;
		}
	}
	
	$check_result['result'] = true;
	
	return $check_result;
}

function checkTotalCancel($db,$order_code,$price_product,$param_price_product) {
	$check_result = false;
	
	$price_cancel = 0;
	
	$select_total_cancel_price_sql = "
		SELECT
			'OCC'		AS ORDER_STATUS,
			IFNULL(
				SUM(PC.PRODUCT_PRICE),0
			)			AS PRODUCT_PRICE
		FROM
			ORDER_PRODUCT_CANCEL PC
		WHERE
			PC.ORDER_CODE = '".$order_code."' AND
			PC.PRODUCT_TYPE NOT IN ('V','D')
	";
	
	$db->query($select_total_cancel_price_sql);
	
	foreach($db->fetch() as $price_data) {
		$price_cancel = $price_data['PRODUCT_PRICE'];
	}
	
	$remain_price = $price_product - $price_cancel - $param_price_product;
	if ($remain_price == 0) {
		$check_result = true;
	}
	
	return $check_result;
}

function getProductInfo($db,$order_pg_info,$param_order_product,$price_mileage,$delivery_price) {
	$product_info = array();
	
	for ($i=0; $i<count($param_order_product); $i++) {
		$data = $param_order_product[$i];
		
		$select_order_product_sql = "
			SELECT
				OP.ORDER_PRODUCT_CODE		AS ORDER_PRODUCT_CODE,
				".$data['product_qty']."	AS PRODUCT_QTY,
				(
					(OP.PRODUCT_PRICE / OP.PRODUCT_QTY) * ".$data['product_qty']."
				)						AS PRODUCT_PRICE
			FROM
				ORDER_PRODUCT OP
			WHERE
				IDX = ".$data['param_idx']."
		";
		
		$db->query($select_order_product_sql);
		
		foreach($db->fetch() as $param_data) {
			$product_price = $param_data['PRODUCT_PRICE'];
			
			$discount_price = 0;
			if ($order_pg_info['price_discount'] > 0) {
				$discount_price	= $product_price / $order_pg_info['price_product'] * $order_pg_info['price_discount'];
			}
			
			$mileage_price = 0;
			/*
			if ($order_pg_info['price_mileage_point'] > 0) {
				$mileage_price = $price_mileage / count($param_order_product);
			}
			*/
			
			$tmp_delivery_price = 0;
			if ($i == 0) {
				$tmp_delivery_price = $delivery_price;
			}
			
			$cancel_price = $product_price - $discount_price - $mileage_price - $delivery_price;
			$refund_price = $product_price;
			
			$product_info[] = array(
				'order_product_code'	=>$param_data['ORDER_PRODUCT_CODE'],
				
				'product_qty'			=>$param_data['PRODUCT_QTY'],
				'product_price'			=>$product_price,
				'discount_price'		=>$discount_price,
				'mileage_price'			=>$mileage_price,
				'cancel_price'			=>$cancel_price,
				'delivery_price'		=>$delivery_price,
				'refund_price'			=>$refund_price
			);
		}
	}
	
	return $product_info;
}

function addProductCancel($db,$param) {
	$product_info = $param['product_info'];
	
	for ($i=0; $i<count($product_info); $i++) {
		$data = $product_info[$i];
		
		$insert_order_product_cancel_sql = "
			INSERT INTO
				ORDER_PRODUCT_CANCEL
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
				DISCOUNT_PRICE,
				MILEAGE_PRICE,
				DELIVERY_PRICE,
				CANCEL_PRICE,
				REFUND_PRICE,
				
				DEPTH1_IDX,
				DEPTH2_IDX,
				REASON_MEMO,
				
				CREATER,
				UPDATER
			)
			SELECT
				0									AS ORDER_IDX,
				OP.ORDER_CODE						AS ORDER_CODE,
				OP.ORDER_PRODUCT_CODE				AS ORDER_PRODUCT_CODE,
				'".$param['order_update_code']."'	AS ORDER_UPDATE_CODE,
				'OCC'								AS ORDER_STATUS,
				
				OP.PRODUCT_IDX						AS PRODUCT_IDX,
				OP.PRODUCT_TYPE						AS PRODUCT_TYPE,
				OP.PRODUCT_CODE						AS PRODUCT_CODE,
				OP.PRODUCT_NAME						AS PRODUCT_NAME,
				
				OP.OPTION_IDX						AS OPTION_IDX,
				OP.BARCODE							AS BARCODE,
				OP.OPTION_NAME						AS OPTION_NAME,
				
				".$data['product_qty']."			AS PRODUCT_QTY,
				(
					(OP.PRODUCT_PRICE / OP.PRODUCT_QTY) * ".$data['product_qty']."
				)									AS PRODUCT_PRICE,
				".$data['discount_price']."			AS DISCOUNT_PRICE,
				0									AS MILEAGE_PRICE,
				0									AS DELIVERY_PRICE,
				(
					(OP.PRODUCT_PRICE / OP.PRODUCT_QTY) * ".$data['product_qty']."
				)									AS REFUND_PRICE,
				(
					(OP.PRODUCT_PRICE / OP.PRODUCT_QTY) * ".$data['product_qty']."
				)									AS REFUND_PRICE,
				
				".$param['depth1_idx']."			AS DEPTH1_IDX,
				".$param['depth2_idx']."			AS DEPTH2_IDX,
				'".$param['reason_memo']."'			AS REASON_MEMO,

				'".$param['member_id']."'			AS CREATER,
				'".$param['member_id']."'			AS UPDATER
			FROM
				ORDER_PRODUCT OP
			WHERE
				OP.ORDER_PRODUCT_CODE = '".$data['order_product_code']."'
		";
		
		$db->query($insert_order_product_cancel_sql);
	}
}

function addOrderCancel($db,$param) {
	$order_idx = null;
	
	$param_price = $param['param_cancel_price'];
	
	$pg_cancel_date		= "NULL";
	$pg_cancel_price	= "NULL";
	$pg_cancel_key		= "NULL";
	
	$pg_cancel_info = $param['pg_cancel_info'];
	if ($pg_cancel_info != null) {
		$pg_cancel_date		= "'".$pg_cancel_info['pg_cancel_date']."'";
		$pg_cancel_price	= $pg_cancel_info['pg_cancel_price'];
		$pg_cancel_key		= "'".$pg_cancel_info['pg_cancel_key']."'";
	}
	
	$insert_order_cancel_sql = "
		INSERT INTO
			ORDER_CANCEL
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
			PRICE_DISCOUNT,
			PRICE_MILEAGE_POINT,
			PRICE_DELIVERY,
			DELIVERY_RETURN,
			PRICE_CANCEL,
			PRICE_REFUND,
			
			PG_CANCEL_DATE,
  			PG_CANCEL_PRICE,
  			PG_CANCEL_KEY,
			
			CREATER,
			UPDATER
		)
		SELECT
			OI.COUNTRY								AS COUNTRY,
			OI.ORDER_CODE							AS ORDER_CODE,
			'".$param['order_update_code']."'		AS ORDER_UPDATE_CODE,
			OI.ORDER_TITLE							AS ORDER_TITLE,
			'OCC'									AS ORDER_STATUS,

			OI.MEMBER_IDX							AS MEMBER_IDX,
			OI.MEMBER_ID							AS MEMBER_ID,
			OI.MEMBER_NAME							AS MEMBER_NAME,
			OI.MEMBER_MOBILE						AS MEMBER_MOBILE,
			OI.MEMBER_LEVEL							AS MEMBER_LEVEL,
			
			".$param_price['price_product']."		AS PRICE_PRODUCT,
			".$param_price['price_discount']."		AS PRICE_DISCOUNT,
			".$param['price_mileage']."				AS PRICE_MILEAGE_POINT,
			".$param_price['price_delivery']."		AS PRICE_DELIVERY,
			".$param_price['delivery_return']."		AS DELIVERY_RETURN,
			".$param_price['price_cancel']."		AS PRICE_CANCEL,
			".$param_price['price_refund']."		AS PRICE_REFUND,
			
			".$pg_cancel_date."						AS PG_CANCEL_DATE,
  			".$pg_cancel_price."					AS PG_CANCEL_PRICE,
  			".$pg_cancel_key."						AS PG_CANCEL_KEY,
			
			'".$param['member_id']."'				AS CREAETER,
			'".$param['member_id']."'				AS UPDATER
		FROM
			ORDER_INFO OI
		WHERE
			OI.ORDER_CODE = '".$param['order_code']."'
	";
	
	$db->query($insert_order_cancel_sql);
	
	$order_idx = $db->last_id();
	
	return $order_idx;
}

function putProductCancel($db,$order_idx,$order_update_code,$member_id) {
	$db->update(
		"ORDER_PRODUCT_CANCEL",
		array(
			"ORDER_IDX"		=>$order_idx,
			"UPDATE_DATE"	=>NOW(),
			"UPDATER"		=>$member_id,
		),
		"
			ORDER_IDX = 0 AND
			ORDER_UPDATE_CODE = '".$order_update_code."'
		"
	);
}

function putOrderProduct($db,$param_order_product,$member_id) {
	for ($i=0; $i<count($param_order_product); $i++) {
		$data = $param_order_product[$i];
		
		$update_order_product_sql = "
			UPDATE
				ORDER_PRODUCT
			SET
				PRODUCT_PRICE = (PRODUCT_PRICE / PRODUCT_QTY) * (PRODUCT_QTY - ".$data['product_qty']."),
				PRODUCT_QTY = PRODUCT_QTY - ".$data['product_qty'].",
				UPDATE_DATE = NOW(),
				UPDATER = '".$member_id."'
			WHERE
				IDX = ".$data['param_idx']."
		";
		
		$db->query($update_order_product_sql);
	}
}
function putPgRemainPrice($db,$order_code,$pg_remain_price) {
	$update_order_info_sql = "
		UPDATE
			ORDER_INFO
		SET
			PG_REMAIN_PRICE = ".$pg_remain_price."
		WHERE
			ORDER_CODE = '".$order_code."'
	";
	
	$db->query($update_order_info_sql);
}

// 주문상태 갱신 적립금 추가 처리
function addCancelMileageInfo($db,$order_update_code,$price_mileage,$member_id) {
	$insert_mileage_info_sql = "
		INSERT INTO
			MILEAGE_INFO
		(
			COUNTRY,
			MEMBER_IDX,
			MILEAGE_CODE,
			MILEAGE_USABLE_INC,
			MILEAGE_BALANCE,
			ORDER_CODE,
			ORDER_PRODUCT_CODE,
			MILEAGE_USABLE_DATE_INFO,
			MEMO,
			MILEAGE_USABLE_DATE,
			CREATER,
			UPDATER
		)
		SELECT
			OT.COUNTRY				AS COUNTRY,
			OT.MEMBER_IDX			AS MEMBER_IDX,
			'RIN'					AS MILEAGE_CODE,
			".$price_mileage."		AS MILEAGE_USABLE_INC,
			(
				(
					SELECT
						S_MI.MILEAGE_BALANCE
					FROM
						MILEAGE_INFO S_MI
					WHERE
						S_MI.MEMBER_IDX = OT.MEMBER_IDX
					ORDER BY
						S_MI.IDX DESC
					LIMIT
						0,1
				) + ".$price_mileage."
			)						AS MILEAGE_BALANCE,
			OT.ORDER_CODE			AS ORDER_CODE,
			OT.ORDER_UPDATE_CODE	AS ORDER_UPDATE_CODE,
			'TODAY'					AS MILEAGE_USABLE_DATE_INFO,
			'RIN'					AS MEMO,
			NOW()					AS MILEAGE_USABLE_DATE,
			'".$member_id."'		AS CREATER,
			'".$member_id."'		AS UPDATER
		FROM
			ORDER_CANCEL OT
		WHERE
			OT.ORDER_UPDATE_CODE = '".$order_update_code."'
	";
	
	$db->query($insert_mileage_info_sql);
}

// 사용 바우처 복원 처리
function putVoucherInfo($db,$order_code) {
	$update_voucher_issue_sql = "
		UPDATE
			VOUCHER_ISSUE
		SET
			USED_FLG = FALSE
		WHERE
			IDX = (
				SELECT
					S_OP.PRODUCT_IDX
				FROM
					ORDER_PRODUCT S_OP
				WHERE
					S_OP.ORDER_CODE = '".$order_code."' AND
					S_OP.PRODUCT_TYPE = 'V'
			)
	";
	
	$db->query($update_voucher_issue_sql);
}

//[주문 취소 상품 테이블] 적립금 등록처리
function addProductCancelMileagePrice($db,$param) {
	$insert_order_product_mileage_sql = "
		INSERT INTO
			ORDER_PRODUCT_CANCEL
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
			MILEAGE_PRICE,
			
			DEPTH1_IDX,
			DEPTH2_IDX,
			REASON_MEMO,
			
			CREATER,
			UPDATER
		) VALUES (
			".$param['order_idx'].",
			'".$param['order_code']."',
			'".$param['order_product_code']."',
			'".$param['order_update_code']."',
			'OCC',
			
			0,
			'D',
			'MLGXXXXXXXXXXXXX',
			'주문 취소 반환 적립금',
			
			0,
			'MLGXXXXXXXXXXXXX',
			'주문 취소 반환 적립금',
			
			1,
			".$param['mileage_price'].",
			
			".$param['depth1_idx'].",
			".$param['depth2_idx'].",
			'".$param['reason_memo']."',

			'".$param['member_id']."',
			'".$param['member_id']."'
		)
	";
	
	$db->query($insert_order_product_mileage_sql);
}

//[주문 취소 상품 테이블] 추가 배송비 등록처리
function addProductCancelDeliveryPrice($db,$param) {
	$insert_order_delivery_price_sql = "
		INSERT INTO
			ORDER_PRODUCT_CANCEL
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
			DELIVERY_PRICE,
			
			DEPTH1_IDX,
			DEPTH2_IDX,
			REASON_MEMO,
			
			CREATER,
			UPDATER
		) VALUES (
			".$param['order_idx'].",
			'".$param['order_code']."',
			'".$param['order_product_code']."',
			'".$param['order_update_code']."',
			'OCC',
			
			0,
			'D',
			'DLVXXXXXXXXXXXXX',
			'주문 취소 추가 배송비',
			
			0,
			'DLVXXXXXXXXXXXXX',
			'주문 취소 추가 배송비',
			
			1,
			".$param['delivery_price'].",
			
			".$param['depth1_idx'].",
			".$param['depth2_idx'].",
			'".$param['reason_memo']."',

			'".$param['member_id']."',
			'".$param['member_id']."'
		)
	";
	
	$db->query($insert_order_delivery_price_sql);
}

//[주문 취소 상품 테이블] 반환 배송비 등록처리
function addProductCancelDeliveryReturn($db,$param) {
	$insert_order_delivery_return_sql = "
		INSERT INTO
			ORDER_PRODUCT_CANCEL
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
			DELIVERY_RETURN,
			
			DEPTH1_IDX,
			DEPTH2_IDX,
			REASON_MEMO,
			
			CREATER,
			UPDATER
		) VALUES (
			".$param['order_idx'].",
			'".$param['order_code']."',
			'".$param['order_product_code']."',
			'".$param['order_update_code']."',
			'OCC',
			
			0,
			'D',
			'DLVXXXXXXXXXXXXX',
			'주문 취소 반환 배송비',
			
			0,
			'DLVXXXXXXXXXXXXX',
			'주문 취소 반환 배송비',
			
			1,
			".$param['delivery_return'].",
			
			".$param['depth1_idx'].",
			".$param['depth2_idx'].",
			'".$param['reason_memo']."',

			'".$param['member_id']."',
			'".$param['member_id']."'
		)
	";
	
	$db->query($insert_order_delivery_return_sql);
}

?>