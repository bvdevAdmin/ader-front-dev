<?php
/*
 +=============================================================================
 | 
 | 마이페이지_주문조회화면
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

$order_idx = 0;
if (isset($_POST['order_idx'])) {
	$order_idx = $_POST['order_idx'];
}

$param_status = null;
if (isset($_POST['param_status'])) {
	$param_status = $_POST['param_status'];
}

if ($member_idx > 0 && $order_idx > 0 && $param_status != null) {
	$select_order_sql = "
		SELECT
			OI.IDX				AS ORDER_IDX,
			OI.ORDER_CODE		AS ORDER_CODE,
			OI.ORDER_TITLE		AS ORDER_TITLE,
			OI.ORDER_STATUS		AS ORDER_STATUS,
			DATE_FORMAT(
				OI.ORDER_DATE,
				'%Y.%m.%d'
			)					AS ORDER_DATE,
			
			OI.MEMBER_NAME			AS MEMBER_NAME,
			OI.MEMBER_MOBILE		AS MEMBER_MOBILE,
			
			OI.PRICE_PRODUCT		AS PRICE_PRODUCT,
			OI.PRICE_DELIVERY		AS PRICE_DELIVERY,
			OI.PRICE_DISCOUNT		AS PRICE_DISCOUNT,
			OI.PRICE_MILEAGE_POINT	AS PRICE_MILEAGE_POINT,
			OI.PRICE_CHARGE_POINT	AS PRICE_CHARGE_POINT,
			OI.PRICE_TOTAL			AS PRICE_TOTAL,
			
			OI.TO_NAME				AS TO_NAME,
			OI.TO_MOBILE			AS TO_MOBILE,
			OI.TO_ZIPCODE			AS TO_ZIPCODE,
			IFNULL(
				OI.TO_ROAD_ADDR,
				OI.TO_LOT_ADDR
			)						AS TO_ADDR,
			TO_DETAIL_ADDR			AS TO_DETAIL_ADDR,
			IFNULL(
				OI.ORDER_MEMO,
				''
			)						AS ORDER_MEMO,
			
			DC.COMPANY_NAME			AS COMPANY_NAME,
			DC.COMPANY_TEL			AS COMPANY_TEL,
			CASE
				WHEN
					OI.ORDER_STATUS = 'DCP' AND
					NOW() <= DATE_ADD(OI.DELIVERY_END_DATE, INTERVAL 7 DAY)
					THEN
						TRUE
				ELSE
						FALSE
			END					AS UPDATE_FLG
		FROM
			ORDER_INFO OI
			LEFT JOIN DELIVERY_COMPANY DC ON
			OI.DELIVERY_IDX = DC.IDX
		WHERE
			OI.IDX = ".$order_idx." AND
			OI.MEMBER_IDX = ".$member_idx."
	";
	
	$db->query($select_order_sql);
	
	foreach($db->fetch() as $order_data) {
		$order_idx = $order_data['ORDER_IDX'];
		
		$preorder_flg = false;
		$preorder_cnt = $db->count("ORDER_PRODUCT","ORDER_IDX = ".$order_idx." AND PREORDER_FLG = TRUE");
		
		if ($preorder_cnt > 0) {
			$preorder_flg = true;
		}
		
		$order_product = array();
		$order_product_cancel = array();
		$order_product_exchange = array();
		$order_product_refund = array();
		
		$order_reason = array();
		
		if (!empty($order_idx)) {
			$order_product = getOrderProductInfoByStatus($db,"PRD",$order_idx,"SUM",false);
			$order_product_cancel = getOrderProductInfoByStatus($db,"OCC",$order_idx,"SUM",false);
			$order_product_exchange = getOrderProductInfoByStatus($db,"OEX",$order_idx,"SUM",false);
			$order_product_refund = getOrderProductInfoByStatus($db,"ORF",$order_idx,"SUM",false);
		}

		switch ($param_status) {
			case "OCC" :
				$price_info = getOrderPriceInfo($db,$param_status,$order_idx);
				
				break;

			case "ORF" :
				$price_info = getOrderPriceInfo($db,$param_status,$order_idx);
				
				break;
			
			default :
				$price_info = array(
					'price_product'			=>number_format($order_data['PRICE_PRODUCT']),
					'price_delivery'		=>number_format($order_data['PRICE_DELIVERY']),
					'price_discount'		=>number_format($order_data['PRICE_DISCOUNT']),
					'price_mileage_point'	=>number_format($order_data['PRICE_MILEAGE_POINT']),
					'price_charge_point'	=>number_format($order_data['PRICE_CHARGE_POINT']),
					'price_total'			=>number_format($order_data['PRICE_TOTAL'])
				);
				
				break;
		}
		
		if ($param_status != "ALL") {
			$order_reason = getOrderReason($db,$country,$param_status,$order_idx);
		}
		
		$json_result['data'] = array(
			'order_idx'					=>$order_data['ORDER_IDX'],
			'order_code'				=>$order_data['ORDER_CODE'],
			'order_title'				=>$order_data['ORDER_TITLE'],
			'order_status'				=>$order_data['ORDER_STATUS'],
			'preorder_flg'				=>$preorder_flg,
			'order_date'				=>$order_data['ORDER_DATE'],
			
			'member_name'				=>$order_data['MEMBER_NAME'],
			'member_mobile'				=>$order_data['MEMBER_MOBILE'],

			'price_product'				=>$price_info['price_product'],
			'price_delivery'			=>$price_info['price_delivery'],
			'price_discount'			=>$price_info['price_discount'],
			'price_mileage_point'		=>$price_info['price_mileage_point'],
			'price_charge_point'		=>$price_info['price_charge_point'],
			'price_total'				=>$price_info['price_total'],
			
			'to_name'					=>$order_data['TO_NAME'],
			'to_mobile'					=>$order_data['TO_MOBILE'],
			'to_zipcode'				=>$order_data['TO_ZIPCODE'],
			'to_addr'					=>$order_data['TO_ADDR'],
			'to_detail_addr'			=>$order_data['TO_DETAIL_ADDR'],
			'order_memo'				=>$order_data['ORDER_MEMO'],
			
			'company_name'				=>$order_data['COMPANY_NAME'],
			'company_tel'				=>$order_data['COMPANY_TEL'],
			'update_flg'				=>$order_data['UPDATE_FLG'],
			
			'order_product'				=>$order_product,
			'order_product_cancel'		=>$order_product_cancel,
			'order_product_exchange'	=>$order_product_exchange,
			'order_product_refund'		=>$order_product_refund,
			
			'order_reason'				=>$order_reason
		);
	}
}

function getOrderPriceInfo($db,$param_status,$order_idx) {
	$price_info = array();
	
	$order_table = "";
	if ($param_status == "OCC") {
		$order_table = "ORDER_PRODUCT_CANCEL";
	} else if ($param_status == "ORF") {
		$order_table = "ORDER_PRODUCT_REFUND";
	}
	
	$select_order_price_sql = "
		SELECT
			SUM(OT.PRODUCT_PRICE)		AS PRICE_PRODUCT,
			SUM(OT.DELIVERY_PRICE)		AS PRICE_DELIVERY,
			SUM(OT.DISCOUNT_PRICE)		AS PRICE_DISCOUNT,
			SUM(OT.MILEAGE_PRICE)		AS PRICE_MILEAGE_POINT,
			0							AS PRICE_CHARGE_POINT,
			SUM(OT.REFUND_PRICE)		AS PRICE_TOTAL
		FROM
			".$order_table." OT
		WHERE
			OT.ORDER_IDX = ".$order_idx."
	";
	
	$db->query($select_order_price_sql);
	
	foreach($db->fetch() as $price_data) {
		$price_info = array(
			'price_product'			=>number_format($price_data['PRICE_PRODUCT']),
			'price_delivery'		=>number_format($price_data['PRICE_DELIVERY']),
			'price_discount'		=>number_format($price_data['PRICE_DISCOUNT']),
			'price_mileage_point'	=>number_format($price_data['PRICE_MILEAGE_POINT']),
			'price_charge_point'	=>number_format($price_data['PRICE_CHARGE_POINT']),
			'price_total'			=>number_format($price_data['PRICE_TOTAL'])
		);
	}
	
	return $price_info;
}

function getOrderReason($db,$country,$param_status,$order_idx) {
	$order_reason = array();
	
	$order_table = "";
	switch ($param_status) {
		case "OCC" :
			$order_table = "ORDER_PRODUCT_CANCEL";
			break;
		
		case "OEX" :
			$order_table = "ORDER_PRODUCT_EXCHANGE";
			break;
		
		case "ORF" :
			$order_table = "ORDER_PRODUCT_REFUND";
			break;
	}
	
	$select_order_reason_sql = "
		SELECT
			OT.REASON_DEPTH1_IDX		AS REASON_DEPTH1_IDX,
			OT.REASON_DEPTH2_IDX		AS REASON_DEPTH2_IDX,
			OT.REASON_MEMO				AS REASON_MEMO
		FROM
			".$order_table." OT
		WHERE
			OT.ORDER_IDX = ".$order_idx."
		ORDER BY
			OT.IDX DESC
		LIMIT
			0,1
	";
	
	$db->query($select_order_reason_sql);
	
	foreach($db->fetch() as $reason_data) {
		$reason_depth1_idx = $reason_data['REASON_DEPTH1_IDX'];
		$reason_depth2_idx = $reason_data['REASON_DEPTH2_IDX'];
		
		$txt_info = array();
		if (!empty($reason_depth1_idx) && !empty($reason_depth1_idx)) {
			$select_reason_txt_sql = "
				SELECT
					R1.REASON_TXT		AS REASON1_TXT,
					R2.REASON_TXT		AS REASON2_TXT
				FROM
					REASON_DEPTH_1 R1
					LEFT JOIN REASON_DEPTH_2 R2 ON
					R1.IDX = R2.DEPTH_1_IDX
				WHERE
					R2.COUNTRY = '".$country."' AND
					R2.IDX = ".$reason_depth2_idx." AND
					R2.REASON_TYPE = '".$param_status."' AND
					R2.DEL_FLG = FALSE
			";
			
			$db->query($select_reason_txt_sql);
			
			foreach($db->fetch() as $txt_data) {
				$txt_info = array(
					'reason1_txt'		=>$txt_data['REASON1_TXT'],
					'reason2_txt'		=>$txt_data['REASON2_TXT']
				);
			}
		}
		
		$order_reason = array(
			'reason1_txt'		=>$txt_info['reason1_txt'],
			'reason2_txt'		=>$txt_info['reason2_txt'],
			'reason_memo'		=>$reason_data['REASON_MEMO']
		);
	}
	
	return $order_reason;
}

?>