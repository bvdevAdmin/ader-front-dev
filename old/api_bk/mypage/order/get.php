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

include_once("/var/www/www/api/mypage/order/order-common.php");
include_once("/var/www/www/api/mypage/order/order-pg.php");

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
			DC.COMPANY_TEL		AS COMPANY_TEL,
			
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
						break;
					
					case "ORF" :
						$order_product_refund	= getOrderProductByStatus($db,"ORF",$order_code,"SUM");
						$order_refund_price = getOrderPriceInfo($db,"ORF",$order_code);
						break;
				}
			} else {
				$order_product_cancel	= getOrderProductByStatus($db,"OCC",$order_code,"SUM");
				$order_product_exchange	= getOrderProductByStatus($db,"OEX",$order_code,"SUM");
				$order_product_refund	= getOrderProductByStatus($db,"ORF",$order_code,"SUM");
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
			'company_tel'				=>$order_data['COMPANY_TEL'],
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
			
			'create_date'				=>$order_data['CREATE_DATE']
		);
	}
}

?>