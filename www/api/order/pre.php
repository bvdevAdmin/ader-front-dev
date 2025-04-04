<?php 
/*
 +=============================================================================
 | 
 | 결제정보 입력화면 - 결제 상품 및 주문자 정보 조회
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.12.12
 | 최종 수정    : 양한빈
 | 최종 수정일	: 2024.06.26
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

include_once $_CONFIG['PATH']['API'].'_legacy/common.php';

if (isset($_SERVER['HTTP_COUNTRY']) && isset($_SESSION['MEMBER_IDX'])) {
	if ($_SERVER['HTTP_COUNTRY'] == "KR" && $_SESSION['AUTH_FLG'] != true) {
		$json_result['code'] = 402;
		$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0028',array());

		echo json_encode($json_result);
		exit;
	}

	if (isset($basket_idx) && is_array($basket_idx) && count($basket_idx) > 0) {
		/* 1. 결제하려는 쇼핑백 상품의 예외처리 */
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
			/* 한국몰/영문몰 에만 진열되는 상품 예외처리 */

			/* 2. 임시 주문정보 삭제처리 - /api/_legacy/common.php */
			deleteOrder_tmp($db,$_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX']);
			
			/* 3. 로그인 한 회원의 등급별 적립/할인율 조회 - /api/_legacy/common.php */
			$discount_per = 0;

			$member_per = checkMember_percentage($db);
			if (sizeof($member_per) > 0 && isset($member_per['discount_per'])) {
				$discount_per = $member_per['discount_per'];
			}
			
			/* 4. 결제하려는 쇼핑백 상품 정보 조회 전 체크처리 */
			$check_result = checkOrder_basket($db,$basket_idx);
			if (sizeof($check_result) > 0) {
				$json_result['code'] = $check_result['code'];
				$json_result['msg'] = $check_result['msg'];
				
				echo json_encode($json_result);
				exit;
			}
			
			/* 5. 결제하려는 쇼핑백 상품 정보 조회처리 */
			$order_product = getOrder_product($db,$basket_idx,$discount_per);
			
			$product_idx = array();
			$total_price	= 0;
			$total_discount	= 0;
			$price_delivery = 0;
			
			if (count($order_product) > 0) {
				foreach($order_product as $product) {
					array_push($product_idx,$product['product_idx']);
					$total_price	+= $product['sales_price'];
					$total_discount	+= $product['member_price'];
				}
			}
			
			/* 6. 결제회원 정보 설정 처리 */
			$order_member = array(
				'member_name'		=>$_SESSION['MEMBER_NAME'],
				'member_mobile'		=>$_SESSION['TEL_MOBILE'],
				'member_email'		=>$_SESSION['MEMBER_ID']
			);
			
			/* 결제회원 기본 배송지 및 배송금액 계산 처리 - /api/_legacy/common.php */
			$order_to = getOrder_to($db,$_SERVER['HTTP_COUNTRY'],null);
			if ($order_to != null) {
				if ($_SERVER['HTTP_COUNTRY'] == "KR" && $total_price < 80000 ) {
					/* 한국몰 && 상품 판매금액 80,000 KRW 미만 */
					$price_delivery = $order_to['delivery_price'];
				} else if ($_SERVER['HTTP_COUNTRY'] == "EN" && $total_price < 300) {
					/* 영문몰 && 상품 판매금액 300 USD 미만 */
					$price_delivery = $order_to['delivery_price'];
				}
			}
			
			/* 8. 결제 회원 보유 바우처 정보 조회 처리 */
			$order_voucher = getOrder_voucher($db,$product_idx,$total_price);
			
			$cnt_voucher	= 0;	/* 현재 보유중인 바우처 수량 */
			$cnt_usable		= 0;	/* 사용 가능한 바우처 수량 */
			
			if (count($order_voucher) > 0) {
				$cnt_voucher = count($order_voucher);
				foreach($order_voucher as $voucher) {
					if ($voucher['usable'] == true) {
						$cnt_usable++;
					}
				}
			}
			
			/* 9. 선택 가능 한 기본 주문 메모 조회처리 */
			$order_memo = getOrder_memo($db);
			
			$json_result['data'] = array(
				'order_product'			=>$order_product,
				'order_member'			=>$order_member,
				'order_to'				=>$order_to,
				'price_delivery'		=>$price_delivery,
				'total_price'			=>$total_price,
				'total_discount'		=>$total_discount,
				
				'cnt_voucher'			=>$cnt_voucher,
				'cnt_usable'			=>$cnt_usable,
				
				'order_voucher'			=>$order_voucher,
				'order_memo'			=>$order_memo
			);
		} else {
			/* 쇼핑백 상품 예외처리 - 결제하려는 상품이 존재하지 않는 경우 */
			$json_result['code'] = 402;
			$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_F_ERR_0115',array());
			
			echo json_encode($json_result);
			exit;
		}
	} else {
		/* 쇼핑백 상품 예외처리 - 결제하려는 상품이 존재하지 않는 경우 - PARAM:쇼핑백 IDX 미설정  */
		$json_result['code'] = 402;
		$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_F_ERR_0115',array());
		
		echo json_encode($json_result);
		exit;
	}
} else {
	/* 회원 예외처리 - 회원 세션정보 미설정 (비로그인) */
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0018',array());
	
	echo json_encode($json_result);
	exit;
}

/* 4. 결제하려는 쇼핑백 상품 정보 조회 전 체크처리 */
function checkOrder_basket($db,$basket_idx) {
	$check_result = array();
	
	$bind_basket = array(
		$_SERVER['HTTP_COUNTRY'],
		$_SESSION['MEMBER_IDX'],
		$_SERVER['HTTP_COUNTRY'],
		$_SESSION['MEMBER_IDX']
	);

	$bind_basket = array_merge($bind_basket,$basket_idx,$basket_idx);
	
	$select_basket_info_sql = "
		SELECT
			BI.IDX						AS BASKET_IDX,
			
			BI.PRODUCT_QTY				AS BASKET_QTY,
			IFNULL(J_OP.PRODUCT_QTY,0)	AS O_PRODUCT_QTY,
			IFNULL(J_OO.PRODUCT_QTY,0)	AS O_OPTION_QTY,
			
			PR.LIMIT_MEMBER				AS LIMIT_MEMBER,
			
			PR.LIMIT_ID_FLG				AS LIMIT_ID_FLG,
			
			PR.LIMIT_QTY_FLG        	AS P_LIMIT_FLG,
			IFNULL(
				PR.LIMIT_PRODUCT_QTY,0
			)							AS P_LIMIT_QTY,
			PR.SOLD_OUT_FLG				AS SOLD_OUT_FLG,
			
			PO.LIMIT_QTY_FLG			AS O_LIMIT_FLG,
			PO.LIMIT_OPTION_QTY			AS O_LIMIT_QTY,
			PO.SALE_FLG					AS O_SALE_FLG,
			
			V_ST.PURCHASEABLE_QTY		AS PURCHASE_QTY,
			V_ST.REMAIN_WCC_QTY			AS REMAIN_WCC_QTY
		FROM
			BASKET_INFO BI
			
			LEFT JOIN SHOP_PRODUCT PR ON
			BI.PRODUCT_IDX = PR.IDX
			
			LEFT JOIN PRODUCT_OPTION PO ON
			BI.PRODUCT_IDX = PO.PRODUCT_IDX AND
			BI.OPTION_IDX = PO.OPTION_IDX
			
			LEFT JOIN V_STOCK V_ST ON
			BI.PRODUCT_IDX = V_ST.PRODUCT_IDX AND
			BI.OPTION_IDX = V_ST.OPTION_IDX
			
			LEFT JOIN (
				SELECT
					S_OP.PRODUCT_IDX		AS PRODUCT_IDX,
					SUM(S_OP.REMAIN_QTY)	AS PRODUCT_QTY
				FROM
					ORDER_PRODUCT S_OP
					LEFT JOIN ORDER_INFO S_OI ON
					S_OP.ORDER_IDX = S_OI.IDX
				WHERE
					S_OI.COUNTRY = ? AND
					S_OI.MEMBER_IDX = ?
				GROUP BY
					S_OP.PRODUCT_IDX
			) AS J_OP ON
			BI.PRODUCT_IDX = J_OP.PRODUCT_IDX
			
			LEFT JOIN (
				SELECT
					S_OP.OPTION_IDX			AS OPTION_IDX,
					SUM(S_OP.REMAIN_QTY)	AS PRODUCT_QTY
				FROM
					ORDER_PRODUCT S_OP
					LEFT JOIN ORDER_INFO S_OI ON
					S_OP.ORDER_IDX = S_OI.IDX
				WHERE
					S_OI.COUNTRY = ? AND
					S_OI.MEMBER_IDX = ?
				GROUP BY
					S_OP.OPTION_IDX
			) AS J_OO ON
			BI.OPTION_IDX = J_OO.OPTION_IDX
		WHERE
			(PR.PRODUCT_TYPE = 'B' AND BI.IDX IN (".implode(',',array_fill(0,count($basket_idx),'?')).")) OR
			(PR.PRODUCT_TYPE = 'B' AND BI.PARENT_IDX IN (".implode(',',array_fill(0,count($basket_idx),'?'))."))
	";
	
	$db->query($select_basket_info_sql,$bind_basket);
	
	foreach($db->fetch() as $data) {
		/* 4-1. 결제 상품 구매 등급 제한 체크 */
		$check_level = false;
		if ($data['LIMIT_MEMBER'] != null && strlen($data['LIMIT_MEMBER']) > 0) {
			$limit_member = explode(",",$data['LIMIT_MEMBER']);
			
			if (in_array("0",$limit_member) || in_array($_SESSION['LEVEL_IDX'],$limit_member)) {
				$check_level = true;
			}
		}
		
		if ($check_level != true) {
			$check_result['code'] = 403;
			$check_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0098',array());
			
			return $check_result;
		}
		
		/* 4-2. 결제 상품 ID당 구매제한 체크 */
		$check_id = false;
		if ($data['LIMIT_ID_FLG'] == true) {
			if ($data['O_PRODUCT_QTY'] == 0) {
				$check_id = true;
			}
		} else {
			$check_id = true;
		}
		
		if ($check_id != true) {
			$check_result['code'] = 405;
			$check_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0004',array());
			
			return $check_result;
		}
		
		/* 4-3. 상품 구매 제한 수량 체크 */
		$check_p_qty = false;
		if ($data['P_LIMIT_FLG'] == true) {
			if ((intval($data['O_PRODUCT_QTY']) + intval($data['BASKET_QTY'])) <= $data['P_LIMIT_QTY']) {
				$check_p_qty = true;
			}
		} else {
			$check_p_qty = true;
		}
		
		if ($check_p_qty != true) {
			$check_result['code'] = 406;
			$check_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_F_ERR_0116',array());
			
			return $check_result;
		}
		
		/* 4-4. 옵션 구매 제한 수량/판매여부 체크 */
		$check_o_qty = false;
		if ($data['O_LIMIT_FLG'] == true) {
			if ((intval($data['O_OPTION_QTY']) + intval($data['BASKET_QTY'])) <= $data['O_LIMIT_QTY']) {
				$check_o_qty = true;
			}
		} else {
			$check_o_qty = true;
		}
		
		if ($check_o_qty != true) {
			$check_result['code'] = 407;
			$check_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_F_ERR_0127',array());
			
			return $check_result;
		}
		
		/* 4-5. 옵션 판매여부 체크 */
		$check_o_flg = false;
		if ($data['O_SALE_FLG'] == true) {
			$check_o_flg = true;
		}
		
		if ($check_o_flg != true) {
			$check_result['code'] = 408;
			$check_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_F_ERR_0127',array());

			return $check_result;
		}
		
		/* 4-6. 결제 상품 잔여재고 체크 */
		$check_stock = false;
		if ($data['BASKET_QTY'] <= $data['PURCHASE_QTY'] && $data['BASKET_QTY'] <= $data['REMAIN_WCC_QTY']) {
			$check_stock = true;
		}
		
		if ($check_stock != true) {
			$check_result['code'] = 409;
			$check_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_F_ERR_0116',array());
			
			return $check_result;
		}

		if ($data['SOLD_OUT_FLG'] == true) {
			$check_result['code'] = 410;
			$check_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_F_ERR_0116',array());
			
			return $check_result;
		}
	}
	
	return $check_result;
}

/* 5. 결제하려는 쇼핑백 상품 정보 조회처리 */
function getOrder_product($db,$basket_idx,$discount_per) {
	$order_product = array();
	
	$bind_basket = array($_SESSION['LEVEL_IDX'],$_SESSION['MEMBER_IDX']);
	$bind_basket = array_merge($bind_basket,$basket_idx);
	
	$select_basket_product_sql = "
		SELECT
			BI.IDX				AS BASKET_IDX,
			BI.PRODUCT_TYPE		AS PRODUCT_TYPE,
			BI.PRODUCT_IDX		AS PRODUCT_IDX,
			J_PI.IMG_LOCATION	AS IMG_LOCATION,
			BI.PRODUCT_NAME		AS PRODUCT_NAME,
			PR.COLOR			AS COLOR,
			PR.COLOR_RGB		AS COLOR_RGB,
			BI.OPTION_NAME		AS OPTION_NAME,
			PR.REFUND_FLG		AS REFUND_FLG,
			
			BI.PRODUCT_QTY		AS PRODUCT_QTY,
			
			PR.PRICE_KR			AS PRICE_KR,
			PR.DISCOUNT_KR		AS DISCOUNT_KR,
			PR.SALES_PRICE_KR	AS SALES_PRICE_KR,
			
			PR.PRICE_EN			AS PRICE_EN,
			PR.DISCOUNT_EN		AS DISCOUNT_EN,
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
			
			LEFT JOIN (
				SELECT
					S_PI.PRODUCT_IDX		AS PRODUCT_IDX,
					S_PI.IMG_LOCATION		AS IMG_LOCATION
				FROM
					PRODUCT_IMG S_PI
				WHERE
					S_PI.IMG_TYPE = 'P' AND
					S_PI.IMG_SIZE = 'S' AND
					S_PI.DEL_FLG = FALSE
				GROUP BY
					S_PI.PRODUCT_IDX
			) AS J_PI ON
			BI.PRODUCT_IDX = J_PI.PRODUCT_IDX
		WHERE
			BI.MEMBER_IDX = ? AND
			BI.PARENT_IDX = 0 AND
			BI.DEL_FLG = FALSE AND
			BI.IDX IN (".implode(',',array_fill(0,count($basket_idx),'?')).")
	";
	
	$db->query($select_basket_product_sql,$bind_basket);
	
	$param_idx = array();
	
	foreach($db->fetch() as $data) {
		array_push($param_idx,$data['BASKET_IDX']);
		
		$price			= $data['PRICE_'.$_SERVER['HTTP_COUNTRY']];
		$discount		= $data['DISCOUNT_'.$_SERVER['HTTP_COUNTRY']];
		$sales_price	= $data['SALES_PRICE_'.$_SERVER['HTTP_COUNTRY']];

		if ($_SERVER['HTTP_COUNTRY'] == "EN") {
			$price			= round($price,1);
			$sales_price	= round($sales_price,1);
		}

		$price			= $price * $data['PRODUCT_QTY'];
		$sales_price	= $sales_price * $data['PRODUCT_QTY'];

		$member_price = $sales_price;
		if ($data['DISCOUNT_FLG'] == true && $data['DISCOUNT_PER'] > 0) {
			$member_price = $sales_price * ($data['DISCOUNT_PER'] / 100);
		} else {
			if ($discount_per > 0) {
				$member_price = $sales_price * ($discount_per / 100);
			} else {
				$member_price = 0;
			}
		}

		$t_price		= number_format($price);
		$t_sales_price	= number_format($sales_price);
		$t_member_price	= number_format($member_price);

		if ($_SERVER['HTTP_COUNTRY'] == "EN") {
			$t_price		= number_format($price,1);
			$t_sales_price	= number_format($sales_price,1);
			$t_member_price	= number_format($member_price,1);
		}
		
		$order_product[] = array(
			'basket_idx'			=>$data['BASKET_IDX'],
			'product_idx'			=>$data['PRODUCT_IDX'],
			'product_type'			=>$data['PRODUCT_TYPE'],
			'img_location'			=>$data['IMG_LOCATION'],
			'product_name'			=>$data['PRODUCT_NAME'],
			'color'					=>$data['COLOR'],
			'color_rgb'				=>$data['COLOR_RGB'],
			'option_name'			=>$data['OPTION_NAME'],
			'refund_flg'			=>$data['REFUND_FLG'],
			
			'product_qty'			=>$data['PRODUCT_QTY'],
			
			'price'					=>$price,
			't_price'				=>$t_price,
			'discount'				=>$discount,
			'sales_price'			=>$sales_price,
			't_sales_price'			=>$t_sales_price,
			
			'member_price'			=>$member_price,
			't_member_price'		=>$t_member_price,
		);
	}
	
	/* 5-1. 쇼핑백 세트 구성상품 조회 처리 */
	$set_product = getOrder_set($db,$param_idx);
	
	if (count($order_product) && count($param_idx) > 0) {
		foreach($order_product as $key => $product) {
			$product_type = $product['product_type'];
			if ($product_type == "S") {
				$basket_idx = $product['basket_idx'];
				$order_product[$key]['set_product_info'] = $set_product[$basket_idx];
			}
		}
	}
	
	return $order_product;
}

/* 5-1. 쇼핑백 세트 구성상품 조회 처리 */
function getOrder_set($db,$param_idx) {
	$set_product = array();
	
	$select_set_product_sql = "
		SELECT
			BI.PARENT_IDX			AS PARENT_IDX,
			J_PI.IMG_LOCATION		AS IMG_LOCATION,
			BI.PRODUCT_NAME			AS PRODUCT_NAME,
			PR.COLOR				AS COLOR,
			PR.COLOR_RGB			AS COLOR_RGB,
			BI.OPTION_NAME			AS OPTION_NAME
		FROM
			BASKET_INFO BI
			
			LEFT JOIN SHOP_PRODUCT PR ON
			BI.PRODUCT_IDX = PR.IDX
			
			LEFT JOIN (
				SELECT
					S_PI.PRODUCT_IDX,
					S_PI.IMG_LOCATION
				FROM
					PRODUCT_IMG S_PI
				WHERE
					S_PI.IMG_TYPE = 'P' AND
					S_PI.IMG_SIZE = 'S' AND
					S_PI.DEL_FLG = FALSE
				GROUP BY
					S_PI.PRODUCT_IDX
			) AS J_PI ON
			PR.IDX = J_PI.PRODUCT_IDX
		WHERE
			BI.PARENT_IDX IN (".implode(',',array_fill(0,count($param_idx),'?')).") AND
			BI.DEL_FLG = FALSE
	";
	
	$db->query($select_set_product_sql,$param_idx);
	
	foreach($db->fetch() as $data) {					
		$set_product[$data['PARENT_IDX']][] = array(
			'img_location'		=>$data['IMG_LOCATION'],
			'product_name'		=>$data['PRODUCT_NAME'],
			'color'				=>$data['COLOR'],
			'color_rgb'			=>$data['COLOR_RGB'],
			'option_name'		=>$data['OPTION_NAME']
		);
	}
	
	return $set_product;
}

/* 8. 결제 회원 보유 바우처 정보 조회 처리 */
function getOrder_voucher($db,$product_idx,$total_price) {
	$order_voucher = array();
	
	$select_voucher_info_sql = "
		SELECT
			VM.IDX				AS VOUCHER_IDX,
			VI.IDX				AS ISSUE_IDX,
			VM.VOUCHER_NAME		AS VOUCHER_NAME,
			VM.MIN_PRICE		AS MIN_PRICE,
			VM.SALE_TYPE		AS SALE_TYPE,
			VM.SALE_PRICE		AS SALE_PRICE,
			
			DATE_FORMAT(
				VI.USABLE_START_DATE,
				'%Y.%m.%d'
			)					AS USABLE_START_DATE,
			DATE_FORMAT(
				VI.USABLE_END_DATE,
				'%Y.%m.%d'
			)					AS USABLE_END_DATE,
			
			VI.USED_FLG			AS USED_FLG
		FROM
			VOUCHER_ISSUE VI
			
			LEFT JOIN VOUCHER_MST VM ON
			VI.VOUCHER_IDX = VM.IDX
		WHERE
			VI.COUNTRY = ? AND
			VI.MEMBER_IDX = ? AND
			(
				NOW() BETWEEN VI.USABLE_START_DATE AND VI.USABLE_END_DATE
			) AND
			VI.DEL_FLG = FALSE AND
			VM.DEL_FLG = FALSE AND
			VI.USED_FLg = FALSE
	";
	
	$db->query($select_voucher_info_sql,array($_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX']));
	
	$param_idx = array();
	
	foreach($db->fetch() as $data) {
		$usable = false;
		if ($total_price >= $data['MIN_PRICE']) {
			$usable = true;
		}
		
		array_push($param_idx,$data['VOUCHER_IDX']);
		
		$order_voucher[] = array(
			'v_idx'					=>$data['VOUCHER_IDX'],
			'voucher_idx'			=>$data['ISSUE_IDX'],
			'voucher_name'			=>$data['VOUCHER_NAME'],
			'sale_type'				=>$data['SALE_TYPE'],
			'sale_price'			=>$data['SALE_PRICE'],
			'usable_start_date'		=>$data['USABLE_START_DATE'],
			'usable_end_date'		=>$data['USABLE_END_DATE'],
			
			'min_price'				=>$data['MIN_PRICE'],
			'used_flg'				=>$data['USED_FLG'],
			'usable'				=>$usable
		);
	}
	
	if (!empty($param_idx)) {
        $voucher_product = getVoucher_product($db, $param_idx);
    } else {
        $voucher_product = array();
    }
	
	if (count($order_voucher) > 0 && count($voucher_product) > 0) {
		foreach($order_voucher as $key => $voucher) {
			$voucher_idx = $voucher['v_idx'];
	
			$check_product = false;
			if (isset($voucher_product[$voucher_idx]) && count($voucher_product[$voucher_idx]) > 0) {
				$check_product = $voucher_product[$voucher_idx];
				
				if (!array_intersect($product_idx,$check_product)) {
					$order_voucher[$key]['usable'] = true;
				} else {
					$order_voucher[$key]['usable'] = false;
				}
			} else {
				$order_voucher[$key]['usable'] = true;
			}
			
			if ($total_price < $voucher['min_price']) {
				$order_voucher[$key]['usable'] = false;
			}
		}
	}
	
	return $order_voucher;
}

/* 8-2. 바우처 적용 제외상품 조회 처리 */
function getVoucher_product($db,$param_idx) {
	$voucher_product = array();
	
	$select_voucher_product_sql = "
		SELECT
			VP.VOUCHER_IDX,
			GROUP_CONCAT(
				VP.PRODUCT_IDX
			)		AS PRODUCT_IDX
		FROM
			VOUCHER_PRODUCT VP
		WHERE
			VP.VOUCHER_IDX IN (".implode(',',array_fill(0,count($param_idx),'?')).")
		GROUP BY
			VP.VOUCHER_IDX
	";
	
	$db->query($select_voucher_product_sql,$param_idx);
	
	foreach($db->fetch() as $data) {
		$product_idx = array();
		if ($data['PRODUCT_IDX'] != null && strlen($data['PRODUCT_IDX']) > 0) {
			$product_idx = explode(",",$data['PRODUCT_IDX']);
		}
		
		$voucher_product[$data['VOUCHER_IDX']] = $product_idx;
	}
	
	return $voucher_product;
}

/* 9. 선택 가능 한 기본 주문 메모 조회처리 */
function getOrder_memo($db) {
	$order_memo = array();
	
	$select_order_memo_sql = "
		SELECT
			OM.IDX					AS MEMO_IDX,
			OM.MEMO_TXT_KR			AS MEMO_TXT_KR,
			OM.MEMO_TXT_EN			AS MEMO_TXT_EN,
			OM.DIRECT_FLG			AS DIRECT_FLG
		FROM
			ORDER_MEMO OM
		ORDER BY
			OM.DISPLAY_NUM ASC
	";
	
	$db->query($select_order_memo_sql);
	
	foreach($db->fetch() as $data) {
		$order_memo[] = array(
			'memo_idx'			=>$data['MEMO_IDX'],
			'memo_txt'			=>$data['MEMO_TXT_'.$_SERVER['HTTP_COUNTRY']],
			'direct_flg'		=>$data['DIRECT_FLG']
		);
	}
	
	return $order_memo;
}

$json_result['order_code'] = order_num();