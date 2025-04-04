<?php
/*
 +=============================================================================
 |
 | 마이페이지 - 주문내역 조회
 | -------
 |
 | 최초 작성	: 이재민
 | 최초 작성일	: 2024.11.16
 | 최종 수정일	:
 | 버전		: 1.0
 | 설명		:
 |
 +=============================================================================
*/

if (isset($_SERVER['HTTP_COUNTRY']) && isset($_SESSION['MEMBER_IDX'])) {
	if (isset($order_idx)) {
		$cnt_order = $db->count("ORDER_INFO", "IDX = ? AND COUNTRY = ? AND MEMBER_IDX = ?",array($order_idx,$_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX']));
		if ($cnt_order > 0) {
			$order_info			= getOrder_info($db,$order_idx);
			$order_product		= getOrder_product($db,$order_idx);
			
			$order_cancel		= array();
			$cancel_product		= array();

			$cnt_cancel		= $db->count("ORDER_CANCEL","ORDER_CODE = ?",array($order_info['order_code']));
			if ($cnt_cancel > 0) {
				$order_cancel		= getOrder_cancel($db,$order_info['order_code']);
				$cancel_product		= getCancel_product($db,$order_info['order_code']);
			}
			
			$exchange_product	= array();

			$cnt_exchange	= $db->count("ORDER_EXCHANGE","ORDER_CODE = ?",array($order_info['order_code']));
			if ($cnt_exchange > 0) {
				$exchange_product	= getExchange_product($db,$order_info['order_code']);
			}

			$order_refund		= array();
			$refund_product		= array();

			$cnt_refund		= $db->count("ORDER_REFUND","ORDER_CODE = ?",array($order_info['order_code']));
			if ($cnt_refund > 0) {
				$order_refund		= getOrder_refund($db,$order_info['order_code']);
				$refund_product		= getRefund_product($db,$order_info['order_code']);
			}

			$order_recent			= calcOrder_recent($order_info,$order_cancel,$order_refund);

			$json_result['data'] = array(
				'order_info'				=>$order_info,
				'order_recent'				=>$order_recent,
				'order_cancel'				=>$order_cancel,
				'order_refund'				=>$order_refund,
				
				'order_product'				=>$order_product,
				'cancel_product'			=>$cancel_product,
				'exchange_product'			=>$exchange_product,
				'refund_product'			=>$refund_product,
			);
		} else {
			$json_result['code'] = 403;
			
			echo json_encode($json_result);
			exit;
		}
	}	
} else {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0018',array());

	echo json_encode($json_result);
	exit;
}

function getOrder_info($db,$order_idx) {
	$order_info = array();
	
	/* 오더 Info */
	$select_order_info_sql = "
		SELECT
			OI.IDX					AS ORDER_IDX,
			OI.ORDER_CODE			AS ORDER_CODE,
			OI.ORDER_TITLE			AS ORDER_TITLE,
			OI.ORDER_STATUS			AS ORDER_STATUS,
			
			OI.PRICE_PRODUCT		AS PRICE_PRODUCT,
			OI.PRICE_MEMBER			AS PRICE_MEMBER,
			OI.PRICE_DISCOUNT		AS PRICE_DISCOUNT,
			OI.PRICE_MILEAGE_POINT	AS PRICE_MILEAGE,
			OI.PRICE_DELIVERY		AS PRICE_DELIVERY,
			OI.PRICE_TOTAL			AS PRICE_TOTAL,
			
			OI.TO_PLACE				AS TO_PLACE,
			OI.TO_NAME				AS TO_NAME,
			OI.TO_MOBILE			AS TO_MOBILE,
			OI.TO_ZIPCODE			AS TO_ZIPCODE,
			OI.TO_ROAD_ADDR			AS TO_ROAD_ADDR,
			IFNULL(
				OI.TO_DETAIL_ADDR,'-'
			)						AS TO_DETAIL_ADDR,
			
			IFNULL(
				OI.DELIVERY_STATUS,
				'NDP'
			)						AS DELIVERY_STATUS,
			IFNULL(
				DC.COMPANY_NAME,'-'
			)						AS DELIVERY_COMPANY,
			DC.COMPANY_CODE			AS COMPANY_CODE,
			IFNULL(
				OI.DELIVERY_NUM,'-'
			)						AS DELIVERY_NUM,
			IFNULL(
				DATE_FORMAT(
					OI.DELIVERY_DATE,
					'%Y.%m.%d'
				),'-'
			)						AS DELIVERY_DATE,
			IFNULL(
				DATE_FORMAT(
					OI.DELIVERY_START_DATE,
					'%Y.%m.%d'
				),'-'
			)						AS DELIVERY_START_DATE,
			IFNULL(
				DATE_FORMAT(
					OI.DELIVERY_END_DATE,
					'%Y.%m.%d'
				),'-'
			)						AS DELIVERY_END_DATE,
			
			OI.ORDER_MEMO			AS ORDER_MEMO,
			
			CASE
				WHEN
					(
						OI.ORDER_STATUS = 'DCP'
						OR
						(SELECT SUM(REMAIN_QTY) FROM ORDER_PRODUCT WHERE ORDER_CODE = OI.ORDER_CODE AND REMAIN_QTY > 0 AND ORDER_STATUS = 'DCP') > 0
					) AND
					
					DATE_FORMAT(
						NOW(),'%Y-%m-%d'
					) <= DATE_ADD(
						DATE_FORMAT(
							OI.DELIVERY_END_DATE,
							'%Y-%m-%d'
						),INTERVAL 7 DAY
					)
				THEN 1
				ELSE 0
			END						AS UPDATE_FLG,
			
			OI.PG_CURRENCY			AS PG_CURRENCY,
			OI.PG_ISSUE_CODE		AS PG_ISSUE_CODE,
			OI.PG_CARD_NUMBER		AS PG_CARD_NUMBER,
			OI.PG_DATE				AS PG_DATE,		
			OI.PG_PAYMENT			AS PG_PAYMENT,			 
			OI.PG_RECEIPT_URL		AS PG_RECEIPT_URL,
			OI.PG_REMAIN_PRICE		AS PG_REMAIN_PRICE,
			DATE_FORMAT(
				OI.CREATE_DATE,
				'%Y.%m.%d'
			)						AS CREATE_DATE,

			IFNULL(
                J_OP.CNT_REMAIN,0
            )                       AS CNT_REMAIN
		FROM
			ORDER_INFO OI
			
			LEFT JOIN DELIVERY_COMPANY DC ON
			OI.DELIVERY_IDX = DC.IDX

			LEFT JOIN (
                SELECT
                    S_OP.ORDER_CODE         AS ORDER_CODE,
                    SUM(S_OP.REMAIN_QTY)    AS CNT_REMAIN
                FROM
                    ORDER_PRODUCT S_OP
                WHERE
                    S_OP.PRODUCT_TYPE NOT REGEXP 'V|D'
                GROUP BY
                    S_OP.ORDER_CODE
            ) AS J_OP ON
            OI.ORDER_CODE = J_OP.ORDER_CODE
		WHERE
			OI.IDX = ? AND
			OI.COUNTRY = ? AND
			OI.MEMBER_IDX = ?
	";

	$db->query($select_order_info_sql,array($order_idx,$_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX']));
	
	$order_info = array();
	
	foreach ($db->fetch() as $data) {
		$t_price_product	= number_format($data['PRICE_PRODUCT']);
		$t_price_member		= number_format($data['PRICE_MEMBER']);
		$t_price_discount	= number_format($data['PRICE_DISCOUNT']);
		$t_price_mileage	= number_format($data['PRICE_MILEAGE']);
		$t_price_delivery	= number_format($data['PRICE_DELIVERY']);
		$t_price_total		= number_format($data['PRICE_TOTAL']);
		
		if ($_SERVER['HTTP_COUNTRY'] == "EN") {
			$t_price_product	= number_format($data['PRICE_PRODUCT'],1);
			$t_price_member		= number_format($data['PRICE_MEMBER'],1);
			$t_price_discount	= number_format($data['PRICE_DISCOUNT'],1);
			$t_price_mileage	= number_format($data['PRICE_MILEAGE'],1);
			$t_price_delivery	= number_format($data['PRICE_DELIVERY'],1);
			$t_price_total		= number_format($data['PRICE_TOTAL'],1);
		}

		$url_delivery = null;
		if ($data['COMPANY_CODE'] != null && $data['DELIVERY_NUM'] != "-") {
			$param_delivery = array(
				'company_code'	=>$data['COMPANY_CODE'],
				'delivery_num'	=>$data['DELIVERY_NUM']
			);

			$url_delivery = setURL_delivery($param_delivery);
		}
		
		$pg_payment = $data['PG_PAYMENT'];
		if ($_SERVER['HTTP_COUNTRY'] == "EN") {
			$pg_payment = "Paypal";
		}

		$order_info = array(
			'order_idx'				=>$data['ORDER_IDX'],
			'order_code'			=>$data['ORDER_CODE'],
			'order_title'			=>$data['ORDER_TITLE'],
			'order_status'			=>$data['ORDER_STATUS'],
			'txt_order_status'		=>setTXT_status($data['ORDER_STATUS']),
			
			'price_product'			=>$data['PRICE_PRODUCT'],
			't_price_product'		=>$t_price_product,
			'price_member'			=>$data['PRICE_MEMBER'],
			't_price_member'		=>$t_price_member,
			'price_discount'		=>$data['PRICE_DISCOUNT'],
			't_price_discount'		=>$t_price_discount,
			'price_mileage'			=>$data['PRICE_MILEAGE'],
			't_price_mileage'		=>$t_price_mileage,
			'price_delivery'		=>$data['PRICE_DELIVERY'],
			't_price_delivery'		=>$t_price_delivery,
			'price_total'			=>$data['PRICE_TOTAL'],
			't_price_total'			=>$t_price_total,
			
			'to_place'				=>$data['TO_PLACE'],
			'to_name'				=>$data['TO_NAME'],
			'to_mobile'				=>$data['TO_MOBILE'],
			'to_zipcode'			=>$data['TO_ZIPCODE'],
			'to_addr'				=>$data['TO_ROAD_ADDR'],
			'to_detail_addr'		=>$data['TO_DETAIL_ADDR'],
			'order_memo'			=>$data['ORDER_MEMO'],
			
			'company_name'			=>$data['DELIVERY_COMPANY'],
			'delivery_status'		=>setTXT_status($data['DELIVERY_STATUS']),
			'delivery_num'			=>$data['DELIVERY_NUM'],
			'delivery_start_date'	=>$data['DELIVERY_START_DATE'],
			'delivery_end_date'		=>$data['DELIVERY_END_DATE'],
			'delivery_date'			=>$data['DELIVERY_DATE'],
			'url_delivery'			=>$url_delivery,
			
			'update_flg'			=>$data['UPDATE_FLG'],
			'pg_currency'			=>$data['PG_CURRENCY'],
			'pg_payment'			=>$pg_payment,
			'txt_issue_name'		=>setTXT_issue($data['PG_ISSUE_CODE']),
			'pg_card_number'		=>$data['PG_CARD_NUMBER'],
			'pg_date'				=>$data['PG_DATE'],
			'pg_receipt_url'		=>$data['PG_RECEIPT_URL'],
			'pg_remain_price'		=>$data['PG_REMAIN_PRICE'],
			'create_date'			=>$data['CREATE_DATE'],

			'cnt_remain'			=>$data['CNT_REMAIN']
		);
	}
	
	return $order_info;
}

function getOrder_cancel($db,$order_code) {
	$order_cancel = array();

	$select_order_cancel_sql = "
		SELECT
			SUM(PRICE_PRODUCT)			AS PRICE_PRODUCT,
			IFNULL(
				(
					SELECT
						SUM(
							(OP.MEMBER_PRICE/ OP.PRODUCT_QTY) * PC.PRODUCT_QTY
						)		AS MEMBER_PRICE
					FROM
						ORDER_PRODUCT_CANCEL PC
						
						LEFT JOIN ORDER_PRODUCT OP ON
						PC.ORDER_PRODUCT_CODE  = OP.ORDER_PRODUCT_CODE
					WHERE
						PC.ORDER_CODE = OI.ORDER_CODE
				),0
			)							AS PRICE_MEMBER,
			SUM(PRICE_DISCOUNT)			AS PRICE_DISCOUNT,
			SUM(PRICE_MILEAGE_POINT)	AS PRICE_MILEAGE,
			SUM(PRICE_DELIVERY)			AS PRICE_DELIVERY,
			SUM(DELIVERY_RETURN)		AS DELIVERY_RETURN,
			SUM(PRICE_CANCEL)			AS PRICE_CANCEL,
			SUM(PRICE_REFUND)			AS PRICE_REFUND
		FROM
			ORDER_CANCEL OI
		WHERE
			OI.ORDER_CODE = ?
		GROUP BY
			OI.ORDER_CODE
	";

	$db->query($select_order_cancel_sql,array($order_code));

	foreach($db->fetch() as $data) {
		$t_price_product	= number_format($data['PRICE_PRODUCT']);
		$t_price_member		= number_format($data['PRICE_MEMBER']);
		$t_price_discount	= number_format($data['PRICE_DISCOUNT']);
		$t_price_mileage	= number_format($data['PRICE_MILEAGE']);
		$t_price_delivery	= number_format($data['PRICE_DELIVERY']);
		$t_delivery_return	= number_format($data['DELIVERY_RETURN']);
		$t_price_cancel		= number_format($data['PRICE_CANCEL']);
		$t_price_refund		= number_format($data['PRICE_REFUND']);
		
		if ($_SERVER['HTTP_COUNTRY'] == "EN") {
			$t_price_product	= number_format($data['PRICE_PRODUCT'],1);
			$t_price_member		= number_format($data['PRICE_MEMBER'],1);
			$t_price_discount	= number_format($data['PRICE_DISCOUNT'],1);
			$t_price_mileage	= number_format($data['PRICE_MILEAGE'],1);
			$t_price_delivery	= number_format($data['PRICE_DELIVERY'],1);
			$t_delivery_return	= number_format($data['DELIVERY_RETURN'],1);
			$t_price_cancel		= number_format($data['PRICE_CANCEL'],1);
			$t_price_refund		= number_format($data['PRICE_REFUND'],1);
		}

		$order_cancel = array(
			'price_product'			=>$data['PRICE_PRODUCT'],
			't_price_product'		=>$t_price_product,
			'price_member'			=>$data['PRICE_MEMBER'],
			't_price_member'		=>$t_price_member,
			'price_discount'		=>$data['PRICE_DISCOUNT'],
			't_price_discount'		=>$t_price_discount,
			'price_mileage'			=>$data['PRICE_MILEAGE'],
			't_price_mileage'		=>$t_price_mileage,
			'price_delivery'		=>$data['PRICE_DELIVERY'],
			't_price_delivery'		=>$t_price_delivery,
			'delivery_return'		=>$data['DELIVERY_RETURN'],
			't_delivery_return'		=>$t_delivery_return,
			'price_cancel'			=>$data['PRICE_CANCEL'],
			't_price_cancel'		=>$t_price_cancel,
			'price_refund'			=>$data['PRICE_REFUND'],
			't_price_refund'		=>$t_price_refund
		);
	}

	return $order_cancel;
}

function getOrder_refund($db,$order_code) {
	$order_refund = array();

	$select_order_refund_sql = "
		SELECT
			SUM(PRICE_PRODUCT)			AS PRICE_PRODUCT,
			IFNULL(
				(
					SELECT
						SUM(
							(OP.MEMBER_PRICE/ OP.PRODUCT_QTY) * PF.PRODUCT_QTY
						)		AS MEMBER_PRICE
					FROM
						ORDER_PRODUCT_CANCEL PF
						
						LEFT JOIN ORDER_PRODUCT OP ON
						PF.ORDER_PRODUCT_CODE  = OP.ORDER_PRODUCT_CODE
					WHERE
						PF.ORDER_CODE = OI.ORDER_CODE
				),0
			)							AS PRICE_MEMBER,
			SUM(PRICE_DISCOUNT)			AS PRICE_DISCOUNT,
			SUM(PRICE_MILEAGE_POINT)	AS PRICE_MILEAGE,
			SUM(PRICE_DELIVERY)			AS PRICE_DELIVERY,
			SUM(DELIVERY_RETURN)		AS DELIVERY_RETURN,
			SUM(PRICE_CANCEL)			AS PRICE_CANCEL,
			SUM(PRICE_REFUND)			AS PRICE_REFUND
		FROM
			ORDER_REFUND OI
		WHERE
			OI.ORDER_CODE = ? AND
			OI.ORDER_STATUS = 'ORP'
		GROUP BY
			OI.ORDER_CODE
	";

	$db->query($select_order_refund_sql,array($order_code));

	foreach($db->fetch() as $data) {
		$t_price_product	= number_format($data['PRICE_PRODUCT']);
		$t_price_member		= number_format($data['PRICE_MEMBER']);
		$t_price_discount	= number_format($data['PRICE_DISCOUNT']);
		$t_price_mileage	= number_format($data['PRICE_MILEAGE']);
		$t_price_delivery	= number_format($data['PRICE_DELIVERY']);
		$t_delivery_return	= number_format($data['DELIVERY_RETURN']);
		$t_price_cancel		= number_format($data['PRICE_CANCEL']);
		
		if ($_SERVER['HTTP_COUNTRY'] == "EN") {
			$t_price_product	= number_format($data['PRICE_PRODUCT'],1);
			$t_price_member		= number_format($data['PRICE_MEMBER'],1);
			$t_price_discount	= number_format($data['PRICE_DISCOUNT'],1);
			$t_price_mileage	= number_format($data['PRICE_MILEAGE'],1);
			$t_price_delivery	= number_format($data['PRICE_DELIVERY'],1);
			$t_delivery_return	= number_format($data['DELIVERY_RETURN'],1);
			$t_price_cancel		= number_format($data['PRICE_CANCEL'],1);
		}

		$order_refund = array(
			'price_product'			=>$data['PRICE_PRODUCT'],
			't_price_product'		=>$t_price_product,
			'price_member'			=>$data['PRICE_MEMBER'],
			't_price_member'		=>$t_price_member,
			'price_discount'		=>$data['PRICE_DISCOUNT'],
			't_price_discount'		=>$t_price_discount,
			'price_mileage'			=>$data['PRICE_MILEAGE'],
			't_price_mileage'		=>$t_price_mileage,
			'price_delivery'		=>$data['PRICE_DELIVERY'],
			't_price_delivery'		=>$t_price_delivery,
			'delivery_return'		=>$data['DELIVERY_RETURN'],
			't_delivery_return'		=>$t_delivery_return,
			'price_cancel'			=>$data['PRICE_CANCEL'],
			't_price_cancel'		=>$t_price_cancel
		);
	}

	return $order_refund;
}

function calcOrder_recent($info,$cancel,$refund) {
	$order_recent = array();

	$price_product = $info['price_product'];
	if (isset($cancel['price_product'])) {
		$price_product -= $cancel['price_product'];
	}

	if (isset($refund['price_product'])) {
		$price_product -= $refund['price_product'];
	}

	$price_member = $info['price_member'];
	if (isset($cancel['price_member'])) {
		$price_member -= $cancel['price_member'];
	}

	if (isset($refund['price_member'])) {
		$price_member -= $refund['price_member'];
	}

	$price_discount = $info['price_discount'];
	if (isset($cancel['price_discount'])) {
		$price_discount -= $cancel['price_discount'];
	}

	if (isset($refund['price_refund'])) {
		$price_discount -= $refund['price_refund'];
	}

	$price_mileage = $info['price_mileage'];
	if (isset($cancel['price_mileage'])) {
		$price_mileage -= $cancel['price_mileage'];
	}

	if (isset($refund['price_mileage'])) {
		$price_mileage -= $refund['price_mileage'];
	}

	$price_delivery = $info['price_delivery'];
	if (isset($cancel['price_delivery'])) {
		$price_delivery += $cancel['price_delivery'];
	}

	if (isset($refund['price_delivery'])) {
		$price_delivery += $refund['price_delivery'];
	}

	$delivery_return = 0;
	if (isset($cancel['delivery_return'])) {
		$delivery_return += $cancel['delivery_return'];
	}

	if (isset($refund['delivery_return'])) {
		$delivery_return += $refund['delivery_return'];
	}

	$price_cancel = 0;
	if (isset($cancel['price_cancel'])) {
		$price_cancel += $cancel['price_cancel'];
	}

	if (isset($refund['price_cancel'])) {
		$price_cancel += $refund['price_cancel'];
	}
	/*
	if ($info['pg_remain_price'] == ($price_product - $price_member - $price_discount - $price_mileage + $price_delivery)) {
		$delivery_return = $price_delivery;
	}
	*/

	$t_price_product	= number_format($price_product);
	$t_price_member		= number_format($price_member);
	$t_price_discount	= number_format($price_discount);
	$t_price_mileage	= number_format($price_mileage);
	$t_price_delivery	= number_format($price_delivery);
	$t_delivery_return	= number_format($delivery_return);
	$t_price_cancel		= number_format($price_cancel);
	$t_price_remain		= number_format($info['pg_remain_price']);
	
	if ($_SERVER['HTTP_COUNTRY'] == "EN") {
		$t_price_product	= number_format($price_product,1);
		$t_price_member		= number_format($price_member,1);
		$t_price_discount	= number_format($price_discount,1);
		$t_price_mileage	= number_format($price_mileage,1);
		$t_price_delivery	= number_format($price_delivery,1);
		$t_delivery_return	= number_format($delivery_return,1);
		$t_price_cancel		= number_format($price_cancel,1);
		$t_price_remain		= number_format($info['pg_remain_price'],1);
	}

	$order_recent = array(
		'price_product'			=>$t_price_product,
		'price_member'			=>$t_price_member,
		'price_discount'		=>$t_price_discount,
		'price_mileage'			=>$t_price_mileage,
		'price_delivery'		=>$t_price_delivery,
		'delivery_return'		=>$t_delivery_return,
		'price_cancel'			=>$t_price_cancel,
		'price_remain'			=>$t_price_remain
	);

	return $order_recent;
}

function getOrder_product($db,$order_idx) {
	$order_product = array();
	
	/* 오더 Product */
	$select_order_product_sql = "
		SELECT
			OP.IDX					AS OP_IDX,
			OP.ORDER_CODE			AS ORDER_CODE,	
			OP.ORDER_PRODUCT_CODE	AS ORDER_PRODUCT_CODE,
			OP.ORDER_STATUS			AS ORDER_STATUS,
			
			PR.IDX					AS PRODUCT_IDX,
			PR.PRODUCT_TYPE			AS PRODUCT_TYPE,
			OP.PARENT_IDX			AS PARENT_IDX,
			PR.PRODUCT_NAME			AS PRODUCT_NAME,
			J_PI.IMG_LOCATION		AS IMG_LOCATION,
			PR.COLOR				AS COLOR,	
			PR.COLOR_RGB			AS COLOR_RGB,
			IFNULL(
				OO.OPTION_NAME,
				'Set'
			)						AS OPTION_NAME,
			OP.REMAIN_QTY			AS PRODUCT_QTY,
			(
				(OP.PRODUCT_PRICE / OP.PRODUCT_QTY) * OP.REMAIN_QTY
			)						AS PRODUCT_PRICE
		FROM
			ORDER_PRODUCT OP

			LEFT JOIN SHOP_PRODUCT PR ON
			OP.PRODUCT_IDX = PR.IDX
		
			LEFT JOIN SHOP_OPTION OO ON
			OP.OPTION_IDX = OO.IDX
		
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
			OP.ORDER_IDX = ? AND
			OP.PRODUCT_TYPE NOT IN ('V','D') AND
			OP.PARENT_IDX = 0 AND
			OP.REMAIN_QTY > 0
	";

	$db->query($select_order_product_sql,array($order_idx));
	
	$parent_idx = array();
	
	foreach ($db->fetch() as $data) {
		if ($data['PRODUCT_TYPE'] == "S") {
			array_push($parent_idx,$data['OP_IDX']);
		}
		
		$t_product_price = number_format($data['PRODUCT_PRICE']);
		if ($_SERVER['HTTP_COUNTRY'] == "EN") {
			$t_product_price = number_format($data['PRODUCT_PRICE'],1);
		}

		$order_product[] = array(
			'order_product_idx'		=>$data['OP_IDX'],
			'order_code'			=>$data['ORDER_CODE'],
			'order_product_code'	=>$data['ORDER_PRODUCT_CODE'],
			'order_status'			=>$data['ORDER_STATUS'],
			't_order_status'		=>setTXT_status($data['ORDER_STATUS']),
			
			'product_idx'			=>$data['PRODUCT_IDX'],
			'product_type'			=>$data['PRODUCT_TYPE'],
			'product_name'			=>$data['PRODUCT_NAME'],
			'img_location'			=>$data['IMG_LOCATION'],
			'color'					=>$data['COLOR'],
			'color_rgb'				=>$data['COLOR_RGB'],
			'option_name'			=>$data['OPTION_NAME'],
			'product_qty'			=>$data['PRODUCT_QTY'],
			'product_price'			=>$t_product_price
		);
	}
	
	if (count($order_product) > 0 && count($parent_idx) > 0) {
		$product_set = getProduct_set($db,$parent_idx);
		if (count($product_set) > 0) {
			foreach($order_product as $key => $product) {
				if ($product['product_type'] == "S") {
					$parent_idx = $product['order_product_idx'];
					if (isset($product_set[$parent_idx])) {
						$order_product[$key]['product_set'] = $product_set[$parent_idx];
					}
				}
			}
		}
	}
	
	return $order_product;
}

function getCancel_product($db,$order_code) {
	$cancel_product = array();
	
	$select_cancel_product_sql = "
		SELECT
			OP.IDX					AS OP_IDX,
			OP.ORDER_CODE			AS ORDER_CODE,
			OP.ORDER_UPDATE_CODE	AS UPDATE_CODE,
			OP.ORDER_PRODUCT_CODE	AS ORDER_PRODUCT_CODE,
			OP.ORDER_STATUS			AS ORDER_STATUS,
			
			PR.IDX					AS PRODUCT_IDX,
			PR.PRODUCT_TYPE			AS PRODUCT_TYPE,
			OP.PARENT_IDX			AS PARENT_IDX,
			PR.PRODUCT_NAME			AS PRODUCT_NAME,
			J_PI.IMG_LOCATION		AS IMG_LOCATION,
			PR.COLOR				AS COLOR,	
			PR.COLOR_RGB			AS COLOR_RGB,
			IFNULL(
				OO.OPTION_NAME,
				'Set'
			)						AS OPTION_NAME,
			OP.PRODUCT_QTY			AS PRODUCT_QTY,
			OP.PRODUCT_PRICE		AS PRODUCT_PRICE
		FROM
			ORDER_PRODUCT_CANCEL OP

			LEFT JOIN SHOP_PRODUCT PR ON
			OP.PRODUCT_IDX = PR.IDX
		
			LEFT JOIN SHOP_OPTION OO ON
			OP.OPTION_IDX = OO.IDX
		
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
			OP.ORDER_CODE = ? AND
			OP.PRODUCT_TYPE NOT IN ('V','D') AND
			OP.PARENT_IDX = 0 AND
			OP.PRODUCT_IDX > 0
		ORDER BY
			OP.ORDER_UPDATE_CODE
	";

	$db->query($select_cancel_product_sql,array($order_code));
	
	$parent_idx = array();
	
	foreach ($db->fetch() as $data) {
		if ($data['PRODUCT_TYPE'] == "S") {
			array_push($parent_idx,$data['OP_IDX']);
		}

		$t_product_price = number_format($data['PRODUCT_PRICE']);
		if ($_SERVER['HTTP_COUNTRY'] == "EN") {
			$t_product_price = number_format($data['PRODUCT_PRICE'],1);
		}
		
		$cancel_product[] = array(
			'order_product_idx'		=>$data['OP_IDX'],
			'update_code'			=>$data['UPDATE_CODE'],
			'order_code'			=>$data['ORDER_CODE'],
			'order_product_code'	=>$data['ORDER_PRODUCT_CODE'],
			'order_status'			=>$data['ORDER_STATUS'],
			't_order_status'		=>setTXT_status($data['ORDER_STATUS']),
			
			'product_idx'			=>$data['PRODUCT_IDX'],
			'product_type'			=>$data['PRODUCT_TYPE'],
			'product_name'			=>$data['PRODUCT_NAME'],
			'img_location'			=>$data['IMG_LOCATION'],
			'color'					=>$data['COLOR'],
			'color_rgb'				=>$data['COLOR_RGB'],
			'option_name'			=>$data['OPTION_NAME'],
			'product_qty'			=>$data['PRODUCT_QTY'],
			'product_price'			=>$t_product_price
		);
	}
	
	if (count($cancel_product) > 0 && count($parent_idx) > 0) {
		$product_set = getProduct_set($db,$parent_idx);
		if (count($product_set) > 0) {
			foreach($cancel_product as $key => $product) {
				if ($product['product_type'] == "S") {
					$parent_idx = $product['order_product_idx'];
					if (isset($product_set[$parent_idx])) {
						$cancel_product[$key]['product_set'] = $product_set[$parent_idx];
					}
				}
			}
		}
	}
	
	return $cancel_product;
}

function getExchange_product($db,$order_code) {
	$exchange_product = array();
	
	$select_exchange_product_sql = "
		SELECT
			OP.IDX					AS OP_IDX,
			OP.ORDER_CODE			AS ORDER_CODE,
			OP.ORDER_UPDATE_CODE	AS UPDATE_CODE,
			OP.ORDER_PRODUCT_CODE	AS ORDER_PRODUCT_CODE,
			OP.ORDER_STATUS			AS ORDER_STATUS,
			
			PR.IDX					AS PRODUCT_IDX,
			PR.PRODUCT_TYPE			AS PRODUCT_TYPE,
			OP.PARENT_IDX			AS PARENT_IDX,
			PR.PRODUCT_NAME			AS PRODUCT_NAME,
			J_PI.IMG_LOCATION		AS IMG_LOCATION,
			PR.COLOR				AS COLOR,	
			PR.COLOR_RGB			AS COLOR_RGB,
			OP.PREV_OPTION_NAME		AS PREV_OPTION_NAME,
			IFNULL(
				OO.OPTION_NAME,
				'Set'
			)						AS OPTION_NAME,
			OP.PRODUCT_QTY			AS PRODUCT_QTY,
			OP.PRODUCT_PRICE		AS PRODUCT_PRICE
		FROM
			ORDER_PRODUCT_EXCHANGE OP

			LEFT JOIN SHOP_PRODUCT PR ON
			OP.PRODUCT_IDX = PR.IDX
		
			LEFT JOIN SHOP_OPTION OO ON
			OP.OPTION_IDX = OO.IDX
		
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
			OP.ORDER_CODE = ? AND
			OP.PRODUCT_TYPE NOT IN ('V','D') AND
			OP.PARENT_IDX = 0 AND
			OP.PRODUCT_IDX > 0
		ORDER BY
			OP.ORDER_UPDATE_CODE
	";

	$db->query($select_exchange_product_sql,array($order_code));
	
	$parent_idx = array();
	
	foreach ($db->fetch() as $data) {
		if ($data['PRODUCT_TYPE'] == "S") {
			array_push($parent_idx,$data['OP_IDX']);
		}
		
		$t_product_price = number_format($data['PRODUCT_PRICE']);
		if ($_SERVER['HTTP_COUNTRY'] == "EN") {
			$t_product_price = number_format($data['PRODUCT_PRICE'],1);
		}

		$exchange_product[] = array(
			'order_product_idx'		=>$data['OP_IDX'],
			'update_code'			=>$data['UPDATE_CODE'],
			'order_code'			=>$data['ORDER_CODE'],
			'order_product_code'	=>$data['ORDER_PRODUCT_CODE'],
			'order_status'			=>$data['ORDER_STATUS'],
			't_order_status'		=>setTXT_status($data['ORDER_STATUS']),
			
			'product_idx'			=>$data['PRODUCT_IDX'],
			'product_type'			=>$data['PRODUCT_TYPE'],
			'product_name'			=>$data['PRODUCT_NAME'],
			'img_location'			=>$data['IMG_LOCATION'],
			'color'					=>$data['COLOR'],
			'color_rgb'				=>$data['COLOR_RGB'],
			
			'prev_option_name'		=>$data['PREV_OPTION_NAME'],
			'option_name'			=>$data['OPTION_NAME'],
			'product_qty'			=>$data['PRODUCT_QTY'],
			'product_price'			=>$t_product_price
		);
	}
	
	if (count($exchange_product) > 0 && count($parent_idx) > 0) {
		$product_set = getProduct_set($db,$parent_idx);
		if (count($product_set) > 0) {
			foreach($exchange_product as $key => $product) {
				if ($product['product_type'] == "S") {
					$parent_idx = $product['order_product_idx'];
					if (isset($product_set[$parent_idx])) {
						$exchange_product[$key]['product_set'] = $product_set[$parent_idx];
					}
				}
			}
		}
	}
	
	return $exchange_product;
}

function getRefund_product($db,$order_code) {
	$refund_product = array();

	$select_refund_product_sql = "
		SELECT
			OP.IDX					AS OP_IDX,
			OP.ORDER_CODE			AS ORDER_CODE,
			OP.ORDER_UPDATE_CODE	AS UPDATE_CODE,
			OP.ORDER_PRODUCT_CODE	AS ORDER_PRODUCT_CODE,
			OP.ORDER_STATUS			AS ORDER_STATUS,
			
			PR.IDX					AS PRODUCT_IDX,
			PR.PRODUCT_TYPE			AS PRODUCT_TYPE,
			OP.PARENT_IDX			AS PARENT_IDX,
			PR.PRODUCT_NAME			AS PRODUCT_NAME,
			J_PI.IMG_LOCATION		AS IMG_LOCATION,
			PR.COLOR				AS COLOR,	
			PR.COLOR_RGB			AS COLOR_RGB,
			IFNULL(
				OO.OPTION_NAME,
				'Set'
			)						AS OPTION_NAME,
			OP.PRODUCT_QTY			AS PRODUCT_QTY,
			OP.PRODUCT_PRICE		AS PRODUCT_PRICE
		FROM
			ORDER_PRODUCT_REFUND OP

			LEFT JOIN SHOP_PRODUCT PR ON
			OP.PRODUCT_IDX = PR.IDX
		
			LEFT JOIN SHOP_OPTION OO ON
			OP.OPTION_IDX = OO.IDX
		
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
			OP.ORDER_CODE = ? AND
			OP.PRODUCT_TYPE NOT IN ('V','D') AND
			OP.PARENT_IDX = 0 AND
			OP.PRODUCT_IDX > 0
		ORDER BY
			OP.ORDER_UPDATE_CODE
	";

	$db->query($select_refund_product_sql,array($order_code));
	
	$parent_idx = array();
	
	foreach ($db->fetch() as $data) {
		if ($data['PRODUCT_TYPE'] == "S") {
			array_push($parent_idx,$data['OP_IDX']);
		}

		$t_product_price = number_format($data['PRODUCT_PRICE']);
		if ($_SERVER['HTTP_COUNTRY'] == "EN") {
			$t_product_price = number_format($data['PRODUCT_PRICE'],1);
		}
		
		$refund_product[] = array(
			'order_product_idx'		=>$data['OP_IDX'],
			'update_code'			=>$data['UPDATE_CODE'],
			'order_code'			=>$data['ORDER_CODE'],
			'order_product_code'	=>$data['ORDER_PRODUCT_CODE'],
			'order_status'			=>$data['ORDER_STATUS'],
			't_order_status'		=>setTXT_status($data['ORDER_STATUS']),
			
			'product_idx'			=>$data['PRODUCT_IDX'],
			'product_type'			=>$data['PRODUCT_TYPE'],
			'product_name'			=>$data['PRODUCT_NAME'],
			'img_location'			=>$data['IMG_LOCATION'],
			'color'					=>$data['COLOR'],
			'color_rgb'				=>$data['COLOR_RGB'],
			'option_name'			=>$data['OPTION_NAME'],
			'product_qty'			=>$data['PRODUCT_QTY'],
			'product_price'			=>$t_product_price
		);
	}
	
	if (count($refund_product) > 0 && count($parent_idx) > 0) {
		$product_set = getProduct_set($db,$parent_idx);
		if (count($product_set) > 0) {
			foreach($refund_product as $key => $product) {
				if ($product['product_type'] == "S") {
					$parent_idx = $product['order_product_idx'];
					if (isset($product_set[$parent_idx])) {
						$refund_product[$key]['product_set'] = $product_set[$parent_idx];
					}
				}
			}
		}
	}
	
	return $refund_product;
}

function getProduct_set($db,$parent_idx) {
	$product_set = array();
	
	/* 오더 Product */
	$select_set_product_sql = "
		SELECT
			OP.IDX					AS OP_IDX,
			OP.ORDER_CODE			AS ORDER_CODE,	
			OP.ORDER_PRODUCT_CODE	AS ORDER_PRODUCT_CODE,
			
			OP.PRODUCT_IDX			AS PRODUCT_IDX,
			PR.PRODUCT_TYPE			AS PRODUCT_TYPE,
			OP.PARENT_IDX			AS PARENT_IDX,
			PR.PRODUCT_NAME			AS PRODUCT_NAME,
			J_PI.IMG_LOCATION		AS IMG_LOCATION,
			PR.COLOR				AS COLOR,	
			PR.COLOR_RGB			AS COLOR_RGB,
			OO.OPTION_NAME			AS OPTION_NAME
		FROM
			ORDER_PRODUCT OP

			LEFT JOIN SHOP_PRODUCT PR ON
			OP.PRODUCT_IDX = PR.IDX
		
			LEFT JOIN SHOP_OPTION OO ON
			OP.OPTION_IDX = OO.IDX
		
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
				 GROUP BY S_PI.PRODUCT_IDX
			) AS J_PI ON
			PR.IDX = J_PI.PRODUCT_IDX
		WHERE
			OP.PARENT_IDX IN (".implode(',',array_fill(0,count($parent_idx),'?')).")
	";
	
	$db->query($select_set_product_sql,$parent_idx);
	
	foreach ($db->fetch() as $data) {
		$product_set[$data['PARENT_IDX']][] = array(
			'order_product_idx'		=>$data['OP_IDX'],
			'order_code'			=>$data['ORDER_CODE'],
			'order_product_code'	=>$data['ORDER_PRODUCT_CODE'],
			
			'product_idx'			=>$data['PRODUCT_IDX'],
			'product_type'			=>$data['PRODUCT_TYPE'],
			'product_name'			=>$data['PRODUCT_NAME'],
			'img_location'			=>$data['IMG_LOCATION'],
			'color'					=>$data['COLOR'],
			'color_rgb'				=>$data['COLOR_RGB'],
			'option_name'			=>$data['OPTION_NAME']
		);
	}
	
	return $product_set;
}

function setTXT_issue($param) {
    $txt_issue_name = "-";

	$issue_name = array(
		'3K'		=>"기업 BC",
		'46'		=>"광주은행",
		'71'		=>"롯데카드",
		'30'		=>"KDB산업은행",
		'31'		=>"BC카드",
		'51'		=>"삼성카드",
		'38'		=>"새마을금고",
		'41'		=>"신한카드",
		'62'		=>"신협",
		'36'		=>"씨티카드",
		'33'		=>"우리BC카드(BC 매입)",
		'W1'		=>"우리카드(우리 매입)",
		'37'		=>"우체국예금보험",
		'39'		=>"저축은행중앙회",
		'35'		=>"전북은행",
		'42'		=>"제주은행",
		'15'		=>"카카오뱅크",
		'3A'		=>"케이뱅크",
		'24'		=>"토스뱅크",
		'21'		=>"하나카드",
		'61'		=>"현대카드",
		'11'		=>"KB국민카드",
		'91'		=>"NH농협카드",
		'34'		=>"Sh수협은행"
	);
    
	if (isset($issue_name[$param])) {
		$txt_issue_name = $issue_name[$param];
	}

    return $txt_issue_name;
}

function setTXT_status($param_status) {
	$txt_status = "";

	$status = array(
		'KR'		=>array(
			'PCP'		=>"결제완료",
			'PPR'		=>"상품준비",
			'NDP'		=>"배송대기",
			'DPR'		=>"배송준비",
			'DPG'		=>"배송중",
			'DCP'		=>"배송완료",
			'OCC'		=>"주문취소",
			'OEX'		=>"교환접수",
			'OEH'		=>"수거완료",
			'OEP'		=>"교환완료",
			'OEE'		=>"교환철회",
			'ORF'		=>"주문반품",
			'ORH'		=>"수거완료",
			'ORP'		=>"반품완료",
			'ORE'		=>"반품철회",
		),
		'EN'			=>array(
			'PCP'		=>"Payment completed",
			'PPR'		=>"Product preparation",
			'NDP'		=>"Wating for shipping",
			'DPR'		=>"Shipping preparation",
			'DPG'		=>"Shipping in progress",
			'DCP'		=>"Shipping completed",
			'OCC'		=>"Order canceled",
			'OEX'		=>"Exchange received",
			'OEH'		=>"Product collected",
			'OEP'		=>"Exchange completed",
			'OEE'		=>"Exchange rejected",
			'ORF'		=>"Refund received",
			'ORH'		=>"Product collected",
			'ORP'		=>"Refund completed",
			'ORE'		=>"Refund rejected",
		)
	);
    
	if (isset($status[$_SERVER['HTTP_COUNTRY']][$param_status])) {
		$txt_status = $status[$_SERVER['HTTP_COUNTRY']][$param_status];
	}

    return $txt_status;
}

function setURL_delivery($param) {
	$url_delivery = null;

	if ($param['company_code'] != null && ($param['delivery_num'] != null && $param['delivery_num'] != "운송장번호 미등록")) {
		$api_key = "xIr2GiKYVXYYM2VZQbwRiA";

		$url_delivery = "https://info.sweettracker.co.kr/tracking/5?t_key=".$api_key."&t_invoice=".$param['delivery_num']."&t_code=".$param['company_code'];
	}

	return $url_delivery;
}

?>