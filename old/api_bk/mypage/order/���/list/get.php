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

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

$order_from = null;
if (isset($_POST['order_from'])) {
	$order_from = str_replace(".","-",$_POST['order_from']);
}

$order_to = null;
if (isset($_POST['order_to'])) {
	$order_to = str_replace(".","-",$_POST['order_to']);
}

$order_status = null;
if (isset($_POST['order_status'])) {
	$order_status = $_POST['order_status'];
}

$rows = 10;
$page = 1;
if (isset($_POST['page'])) {
	$page = $_POST['page'];
}

$where = null;
$where .= "OI.COUNTRY = '".$country."' AND OI.MEMBER_IDX = ".$member_idx;

$where_order_date = null;
if ($order_from != null || $order_to != null) {
	if ($order_from != null && $order_to == null) {
		$where_order_date = " AND (DATE_FORMAT(OI.ORDER_DATE,'%Y-%m-%d') >= '".$order_from."') ";
	} else if ($order_from == null && $order_to != null) {
		$where_order_date = " AND (DATE_FORMAT(OI.ORDER_DATE,'%Y-%m-%d') <= '".$order_to."') ";
	} else if ($order_from != null && $order_to != null) {
		$where_order_date = " AND (DATE_FORMAT(OI.ORDER_DATE,'%Y-%m-%d') BETWEEN '".$order_from."' AND '".$order_to."') ";
	}
}

if ($member_idx > 0) {
	$table = "
		ORDER_INFO OI
		LEFT JOIN DELIVERY_COMPANY DC ON
		OI.DELIVERY_IDX = DC.IDX
	";
	
	$json_result = array(
		'total' => $db->count($table,$where)
	);
	
	$select_order_info_sql = "
		SELECT
			OI.IDX		AS ORDER_IDX,
			OI.ORDER_CODE		AS ORDER_CODE,
			OI.ORDER_TITLE		AS ORDER_TITLE,
			OI.ORDER_STATUS		AS ORDER_STATUS,
			DATE_FORMAT(
				OI.ORDER_DATE,
				'%Y.%m.%d'
			)					AS ORDER_DATE,
			DC.COMPANY_NAME		AS COMPANY_NAME,
			DC.COMPANY_TEL		AS COMPANY_TEL,
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
			".$table."
		WHERE
			".$where."
			".$where_order_date."
		ORDER BY
			OI.IDX DESC
	";
	
	$limit_start = (intval($page)-1)*$rows;
	if ($rows != null) {
		$select_order_info_sql .= " LIMIT ".$limit_start.",".$rows;
	}
	
	$db->query($select_order_info_sql);
	
	$order_info = array();
	foreach($db->fetch() as $order_data) {
		$order_idx = $order_data['ORDER_IDX'];
		deleteTmpOrderProduct($db,$order_idx);
		
		$preorder_flg = false;
		$preorder_cnt = $db->count("ORDER_PRODUCT","ORDER_IDX = ".$order_idx." AND PREORDER_FLG = TRUE");
		if ($preorder_cnt > 0) {
			$preorder_flg = true;
		}
		
		$order_product = array();
		$order_product_cancel = array();
		$order_product_exchange = array();
		$order_product_refund = array();
		
		$order_product_cnt = 0;
		if (!empty($order_idx)) {
			if ($order_status == "ALL") {
				$select_flg = true;
				$order_product = getOrderProductInfoByStatus($db,"PRD",$order_idx,"SUM",false);
				
				$order_product_cnt += count($order_product);
			}
			
			if ($order_status == "ALL" || $order_status == "OCC") {
				$select_flg = true;
				$order_product_cancel = getOrderProductInfoByStatus($db,"OCC",$order_idx,"SUM",false);
				
				$order_product_cnt += count($order_product_cancel);
			}
			
			if ($order_status == "ALL" || $order_status == "OEX") {
				$select_flg = true;
				$order_product_exchange = getOrderProductInfoByStatus($db,"OEX",$order_idx,"SUM",false);
				
				$order_product_cnt += count($order_product_exchange);
			}
			
			if ($order_status == "ALL" || $order_status == "ORF") {
				$select_flg = true;
				$order_product_refund = getOrderProductInfoByStatus($db,"ORF",$order_idx,"SUM",false);
				
				$order_product_cnt += count($order_product_refund);
			}
		}
		
		if ($order_product_cnt > 0) {
			$order_info[] = array(
				'order_idx'					=>$order_data['ORDER_IDX'],
				'order_code'				=>$order_data['ORDER_CODE'],
				'order_title'				=>$order_data['ORDER_TITLE'],
				'order_status'				=>$order_data['ORDER_STATUS'],
				'txt_order_status'			=>setTxtOrderStatus($order_data['ORDER_STATUS'],$country),
				'preorder_flg'				=>$preorder_flg,
				'order_date'				=>$order_data['ORDER_DATE'],
				'company_name'				=>$order_data['COMPANY_NAME'],
				'company_tel'				=>$order_data['COMPANY_TEL'],
				'update_flg'				=>$order_data['UPDATE_FLG'],
				
				'order_product'				=>$order_product,
				'order_product_cancel'		=>$order_product_cancel,
				'order_product_exchange'	=>$order_product_exchange,
				'order_product_refund'		=>$order_product_refund
			);
		}
	}
	
	$json_result['data'] = $order_info;
} else {
	$json_result['code'] = 301;
	$json_result['msg'] = "로그인 정보가 없습니다. 로그인 후 다시 시도해주세요.";
}

?>