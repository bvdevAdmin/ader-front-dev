<?php
/*
 +=============================================================================
 | 
 | 마이페이지_주문취소,주문교환/반품 - 고객별 주문 리스트 조회
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

include_once(dir_f_api."/mypage/order/order-common.php");
include_once(dir_f_api."/mypage/order/order-pg.php");

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

if (isset($country) && $member_idx > 0 && isset($order_idx) && isset($param_status)) {
	$select_order_info_sql = "
		SELECT
			OI.IDX				AS ORDER_IDX,
			OI.ORDER_CODE		AS ORDER_CODE,
			OI.ORDER_TITLE		AS ORDER_TITLE,
			OI.ORDER_STATUS		AS ORDER_STATUS,
			
			OI.MEMBER_NAME		AS MEMBER_NAME,
			OI.MEMBER_MOBILE	AS MEMBER_MOBILE,
			
			OI.TO_NAME			AS TO_NAME,
			OI.TO_MOBILE		AS TO_MOBILE,
			OI.TO_ZIPCODE		AS TO_ZIPCODE,
			IFNULL(
				OI.TO_ROAD_ADDR,
				OI.TO_LOT_ADDR
			)					AS TO_ADDR,
			TO_DETAIL_ADDR		AS TO_DETAIL_ADDR,
			IFNULL(
				OI.ORDER_MEMO,
				''
			)					AS ORDER_MEMO,
			
			DC.COMPANY_NAME		AS COMPANY_NAME,
			OI.DELIVERY_NUM		AS DELIVERY_NUM,
			
			OI.PG_ISSUE_CODE		AS PG_ISSUE_CODE,
			OI.PG_CARD_NUMBER		AS PG_CARD_NUMBER,
			DATE_FORMAT(
				OI.PG_DATE,
				'%Y.%m.%d %H:%i:%s'
			)					AS PG_DATE,
			OI.PG_PAYMENT			AS PG_PAYMENT, 
			OI.PG_RECEIPT_URL		AS PG_RECEIPT_URL,
			
			CASE
				WHEN
					OI.ORDER_STATUS = 'DCP' AND
					NOW() <= DATE_ADD(OI.DELIVERY_END_DATE, INTERVAL 7 DAY)
					THEN
						TRUE
				ELSE
						FALSE
			END					AS UPDATE_FLG,
			
			DATE_FORMAT(
				OI.CREATE_DATE,
				'%Y.%m.%d'
			)					AS CREATE_DATE
		FROM
			ORDER_INFO OI
			LEFT JOIN DELIVERY_COMPANY DC ON
			OI.DELIVERY_IDX = DC.IDX
		WHERE
			OI.IDX = ".$order_idx." AND
			OI.MEMBER_IDX = ".$member_idx."
	";
	
	$db->query($select_order_info_sql);
	
	foreach($db->fetch() as $order_data) {
		$order_idx = $order_data['ORDER_IDX'];
		$order_code = $order_data['ORDER_CODE'];
		
		delTmpOrderProduct($db,$order_code);
		
		$txt_issue_name = "";
		if ($order_data['PG_ISSUE_CODE'] != null) {
			$txt_issue_name = setTxtIssueName($order_data['PG_ISSUE_CODE']);
		}
		
		$order_product			= null;
		$order_product_cancel	= null;
		$order_product_exchange	= null;
		$order_product_refund	= null;
		
		$order_price			= null;
		$order_cancel_price		= null;
		$order_exchange_price	= null;
		$order_refund_price		= null;
		
		$order_extra_price = null;
		
		if (!empty($order_code)) {
			$order_product = getOrderProductByStatus($db,"PRD",$order_code,"SUM");
			$order_price = getOrderPriceInfo($db,"PRD",$order_code);
			
			if ($param_status != "ALL") {
				switch ($param_status) {
					case "OCC" :
						$order_product_cancel	= getOrderProductByStatus($db,"OCC",$order_code,"SUM");
						$order_cancel_price = getOrderPriceInfo($db,"OCC",$order_code);
						break;
					
					case "OEX" :
						$order_product_exchange	= getOrderProductByStatus($db,"OEX",$order_code,"SUM");
						$order_exchange_price = getOrderPriceInfo($db,"OEX",$order_code);
						
						$order_extra_price = getOrderExtraPrice($db,"OEX",$order_code);
						break;
					
					case "ORF" :
						$order_product_refund	= getOrderProductByStatus($db,"ORF",$order_code,"SUM");
						$order_refund_price = getOrderPriceInfo($db,"ORF",$order_code);
						
						$order_extra_price = getOrderExtraPrice($db,"ORF",$order_code);
						break;
				}
			} else {
				$order_product_cancel	= getOrderProductByStatus($db,"OCC",$order_code,"SUM");
				$order_product_exchange	= getOrderProductByStatus($db,"OEX",$order_code,"SUM");
				$order_product_refund	= getOrderProductByStatus($db,"ORF",$order_code,"SUM");
				
				$order_extra_price = getOrderExtraPrice($db,"ALL",$order_code);
			}
		}
		
		$json_result['data'] = array(
			'order_idx'					=>$order_data['ORDER_IDX'],
			'order_code'				=>$order_data['ORDER_CODE'],
			'order_title'				=>$order_data['ORDER_TITLE'],
			'order_status'				=>$order_data['ORDER_STATUS'],
			
			'member_name'				=>$order_data['MEMBER_NAME'],
			'member_mobile'				=>$order_data['MEMBER_MOBILE'],
			
			'to_name'					=>$order_data['TO_NAME'],
			'to_mobile'					=>$order_data['TO_MOBILE'],
			'to_zipcode'				=>$order_data['TO_ZIPCODE'],
			'to_addr'					=>$order_data['TO_ADDR'],
			'to_detail_addr'			=>$order_data['TO_DETAIL_ADDR'],
			'order_memo'				=>$order_data['ORDER_MEMO'],
			
			'company_name'				=>$order_data['COMPANY_NAME'],
			'delivery_num'				=>$order_data['DELIVERY_NUM'],
			'update_flg'				=>$order_data['UPDATE_FLG'],
			
			'pg_issue_code'				=>$order_data['PG_ISSUE_CODE'],
			'txt_issue_name'			=>$txt_issue_name,
			'pg_card_number'			=>$order_data['PG_CARD_NUMBER'],
			'pg_date'					=>$order_data['PG_DATE'],
			'pg_receipt_url'			=>$order_data['PG_RECEIPT_URL'],
			'pg_payment'				=>$order_data['PG_PAYMENT'],
			
			'order_product'				=>$order_product,
			'order_product_cancel'		=>$order_product_cancel,
			'order_product_exchange'	=>$order_product_exchange,
			'order_product_refund'		=>$order_product_refund,
			
			'order_price'				=>$order_price,
			'order_cancel_price'		=>$order_cancel_price,
			'order_exchange_price'		=>$order_exchange_price,
			'order_refund_price'		=>$order_refund_price,
			
			'order_extra_price'			=>$order_extra_price,
			
			'create_date'				=>$order_data['CREATE_DATE']
		);
	}
}

/* 주문 교환/반품 시 추가 결제한 배송비 정보 조회 */
function getOrderExtraPrice($db,$order_status,$order_code) {
	$order_extra_price = null;
	
	if ($order_status == "ALL") {
		$select_order_extra_price_sql = "
			(
				SELECT
					'OE'				AS ORDER_STATUS,
					OE.PG_PRICE			AS EXTRA_PRICE,
					OE.PG_RECEIPT_URL	AS EXTRA_URL
				FROM
					ORDER_EXCHANGE OE
				WHERE
					OE.ORDER_CODE = '".$order_code."'
				ORDER BY
					OE.ORDER_UPDATE_CODE ASC
			)
			
			UNION
			
			(
				SELECT
					'OR'				AS ORDER_STATUS,
					`OF`.PG_PRICE		AS EXTRA_PRICE,
					`OF`.PG_RECEIPT_URL	AS EXTRA_URL
				FROM
					ORDER_REFUND `OF`
				WHERE
					`OF`.ORDER_CODE = '".$order_code."'
				ORDER BY
					`OF`.ORDER_UPDATE_CODE ASC
			)
		";
	} else {
		$order_table = getOrderTable($order_status,false);
		
		$select_order_extra_price_sql = "
			SELECT
				'".substr($order_status,0,2)."'
									AS ORDER_STATUS,
				OT.PG_PRICE			AS EXTRA_PRICE,
				OT.PG_RECEIPT_URL	AS EXTRA_URL
			FROM
				".$order_table['info']." OT
			WHERE
				OT.ORDER_CODE = '".$order_code."'
			ORDER BY
				OT.ORDER_UPDATE_CODE ASC
		";
	}
	
	$db->query($select_order_extra_price_sql);
	
	$total_extra = 0;
	$extra_price = null;
	
	foreach($db->fetch() as $data) {
		$total_extra += $data['EXTRA_PRICE'];
		$extra_price[] = array(
			'order_status'		=>$data['ORDER_STATUS'],
			'extra_price'		=>number_format($data['EXTRA_PRICE']),
			'extra_url'			=>$data['EXTRA_URL']
		);
	}
	
	$order_extra_price = array(
		'total_extra'		=>number_format($total_extra),
		'extra_price'		=>$extra_price,
	);
	
	return $order_extra_price;
}

?>