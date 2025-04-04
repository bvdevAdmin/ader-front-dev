<?php
/*
 +=============================================================================
 | 
 | 마이페이지_주문취소,주문교환/반품 - [주문취소][주문교환][주문반품] 대상 [주문 상품 테이블] 전체 조회
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

if (isset($country) && $member_idx > 0 && isset($order_code)) {
	$recent_pg_info = null;
	if ($order_status == "OCC") {
		$recent_pg_info = getRecentPgInfo($db,$order_code);
	}
	
	$select_order_info_sql = "
		SELECT
			OI.IDX				AS ORDER_IDX,
			OI.ORDER_CODE		AS ORDER_CODE,
			
			DC.COMPANY_NAME		AS COMPANY_NAME,
			DC.COMPANY_TEL		AS COMPANY_TEL,
			
			DATE_FORMAT(
				OI.CREATE_DATE,
				'%Y.%m.%d'
			)					AS CREATE_DATE
		FROM
			ORDER_INFO OI
			LEFT JOIN DELIVERY_COMPANY DC ON
			OI.DELIVERY_IDX = DC.IDX
		WHERE
			OI.COUNTRY = '".$country."' AND
			OI.MEMBER_IDX = ".$member_idx." AND
			OI.ORDER_CODE = '".$order_code."'
	";
	
	$db->query($select_order_info_sql);
	
	foreach($db->fetch() as $order_data) {
		$order_idx = $order_data['ORDER_IDX'];
		$order_code = $order_data['ORDER_CODE'];
		
		if ($tmp_flg != true) {
			delTmpOrderProduct($db,$order_code);
		}
		
		$order_product = null;
		$tmp_product_exchange = null;
		$tmp_product_refund = null;
		
		if (!empty($order_code)) {
			$order_product = getOrderProductByStatus($db,"PRD",$order_code,"IND");
			
			if ($tmp_flg == true) {
				$tmp_product_exchange	= getTmpProductByStatus($db,"OEX",$order_code);
				$tmp_product_refund		= getTmpProductByStatus($db,"ORF",$order_code);
			}
			
			$order_info = array(
				'order_idx'					=>$order_data['ORDER_IDX'],
				'order_code'				=>$order_data['ORDER_CODE'],
				
				'company_name'				=>$order_data['COMPANY_NAME'],
				'company_tel'				=>$order_data['COMPANY_TEL'],
				
				'create_date'				=>$order_data['CREATE_DATE'],
				
				'order_product'				=>$order_product,
				
				'recent_pg_info'			=>$recent_pg_info,
				
				'tmp_product_exchange'		=>$tmp_product_exchange,
				'tmp_product_refund'		=>$tmp_product_refund,
			);
		}
	}
	
	$json_result['data'] = $order_info;
} else {
	$json_result['code'] = 301;
	$json_result['msg'] = "로그인 정보가 없습니다. 로그인 후 다시 시도해주세요.";
}

function getRecentPgInfo($db,$order_code) {
	$recent_pg_info = null;
	
	$order_pg_info = getOrderPgInfo($db,$order_code);
	
	$cnt_OCC = $db->count("ORDER_CANCEL","ORDER_CODE = '".$order_code."'");
	if ($cnt_OCC > 0) {
		$select_recent_pg_info_sql = "
			SELECT
				IFNULL(
					SUM(OC.PRICE_PRODUCT),0
				)		AS PRICE_PRODUCT,
				IFNULL(
					SUM(OC.PRICE_DISCOUNT),0
				)		AS PRICE_DISCOUNT,
				IFNULL(
					SUM(OC.PRICE_MILEAGE_POINT),0
				)		AS PRICE_MILEAGE_POINT,
				IFNULL(
					SUM(OC.PRICE_DELIVERY),0
				)		AS PRICE_DELIVERY,
				
				IFNULL(
					SUM(OC.PRICE_CANCEL),0
				)		AS PRICE_CANCEL,
				IFNULL(
					SUM(OC.PRICE_REFUND),0
				)		AS PRICE_REFUND
			FROM
				ORDER_CANCEL OC
			WHERE
				OC.ORDER_CODE = '".$order_code."'
		";
		
		$db->query($select_recent_pg_info_sql);
		
		foreach($db->fetch() as $pg_data) {
			$price_product			= $pg_data['PRICE_PRODUCT'];
			$price_discount			= $pg_data['PRICE_DISCOUNT'];
			$price_mileage_point	= $pg_data['PRICE_MILEAGE_POINT'];
			$price_delivery			= $pg_data['PRICE_DELIVERY'];
			
			$price_cancel			= $pg_data['PRICE_CANCEL'];
			$price_refund			= $pg_data['PRICE_REFUND'];
			
			$recent_pg_info = array(
				'price_product'				=>number_format($order_pg_info['price_product'] - $price_product),
				'price_discount'			=>number_format($order_pg_info['price_discount'] - $price_discount),
				'price_mileage_point'		=>$order_pg_info['price_mileage_point'] - $price_mileage_point,
				'txt_price_mileage_point'	=>number_format($order_pg_info['price_mileage_point'] - $price_mileage_point),
				'price_delivery'			=>number_format($order_pg_info['price_delivery'] + $price_delivery),
				
				'price_cancel'				=>number_format($price_cancel),
				'price_refund'				=>number_format($price_refund),
				
				'pg_remain_price'			=>number_format($order_pg_info['pg_remain_price'])
			);
		}
	} else {
		$recent_pg_info = array(
			'price_product'				=>number_format($order_pg_info['price_product']),
			'price_discount'			=>number_format($order_pg_info['price_discount']),
			'price_mileage_point'		=>$order_pg_info['price_mileage_point'],
			'txt_price_mileage_point'	=>number_format($order_pg_info['price_mileage_point']),
			'price_delivery'			=>number_format($order_pg_info['price_delivery']),
			
			'price_cancel'				=>0,
			'price_refund'				=>number_format($order_pg_info['price_total']),
			
			'pg_remain_price'			=>number_format($order_pg_info['pg_remain_price'])
		);
	}
	
	return $recent_pg_info;
}

?>