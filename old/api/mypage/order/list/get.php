<?php
/*
 +=============================================================================
 | 
 | 마이페이지_주문내역 - 고객별 주문 리스트 조회
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

$search_date = null;
if (isset($_POST['search_date'])) {
	$search_date = $_POST['search_date'];
}

$rows = 10;

$page = 1;
if (isset($_POST['page'])) {
	$page = $_POST['page'];
}

if ($country != null && $member_idx > 0) {
	$where = "OI.COUNTRY = '".$country."' AND OI.MEMBER_IDX = ".$member_idx;
	
	if ($order_from != null || $order_to != null) {
		if ($order_from != null && $order_to == null) {
			$where .= " AND (DATE_FORMAT(OI.CREATE_DATE,'%Y-%m-%d') >= '".$order_from."') ";
		} else if ($order_from == null && $order_to != null) {
			$where .= " AND (DATE_FORMAT(OI.CREATE_DATE,'%Y-%m-%d') <= '".$order_to."') ";
		} else if ($order_from != null && $order_to != null) {
			$where .= " AND (DATE_FORMAT(OI.CREATE_DATE,'%Y-%m-%d') BETWEEN '".$order_from."' AND '".$order_to."') ";
		}
	}

	if ($search_date != null) {
		switch($search_date) {
			case "1W":
				$where .= " AND (DATE_FORMAT(OI.CREATE_DATE,'%Y-%m-%d') >= (CURDATE() - INTERVAL 7 DAY)) ";
				break;

			case "1M":
				$where .= " AND (DATE_FORMAT(OI.CREATE_DATE,'%Y-%m-%d') >= (CURDATE() - INTERVAL 1 MONTH)) ";
				break;

			case "3M":
				$where .= " AND (DATE_FORMAT(OI.CREATE_DATE,'%Y-%m-%d') >= (CURDATE() - INTERVAL 3 MONTH)) ";
				break;

			case "1Y":
				$where .= " AND (DATE_FORMAT(OI.CREATE_DATE,'%Y-%m-%d') >= (CURDATE() - INTERVAL 1 YEAR)) ";
				break;
		}
	}
	
	$json_result = array(
		'total' => $db->count("ORDER_INFO OI",$where)
	);
	
	$select_order_info_sql = "
		SELECT
			OI.IDX				AS ORDER_IDX,
			OI.ORDER_CODE		AS ORDER_CODE,
			OI.ORDER_TITLE		AS ORDER_TITLE,
			OI.ORDER_STATUS		AS ORDER_STATUS,
			
			DC.COMPANY_NAME		AS COMPANY_NAME,
			OI.DELIVERY_NUM		AS DELIVERY_NUM,
			
			DATE_FORMAT(
				OI.CREATE_DATE,
				'%Y.%m.%d'
			)					AS CREATE_DATE
		FROM
			ORDER_INFO OI
			LEFT JOIN DELIVERY_COMPANY DC ON
			OI.DELIVERY_IDX = DC.IDX
		WHERE
			".$where."
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
		$order_code = $order_data['ORDER_CODE'];
		
		delTmpOrderProduct($db,$order_code);
		
		$order_product = array();
		$order_product_cancel = array();
		$order_product_exchange = array();
		$order_product_refund = array();
		
		if (!empty($order_code)) {
			if ($order_status != "ALL") {
				switch ($order_status) {
					case "OCC" :
						$order_product_cancel	= getOrderProductByStatus($db,"OCC",$order_code,"SUM");
						break;
					
					case "OEX" :
						$order_product_exchange	= getOrderProductByStatus($db,"OEX",$order_code,"SUM");
						break;
					
					case "ORF" :
						$order_product_refund	= getOrderProductByStatus($db,"ORF",$order_code,"SUM");
						break;
				}
			} else {
				$order_product			= getOrderProductByStatus($db,"PRD",$order_code,"SUM");
				$order_product_cancel	= getOrderProductByStatus($db,"OCC",$order_code,"SUM");
				$order_product_exchange	= getOrderProductByStatus($db,"OEX",$order_code,"SUM");
				$order_product_refund	= getOrderProductByStatus($db,"ORF",$order_code,"SUM");
			}
		}
		
		$cnt_PRD = count($order_product);
		$cnt_OCC = count($order_product_cancel);
		$cnt_OEX = count($order_product_exchange);
		$cnt_ORF = count($order_product_refund);
		
		if (($cnt_PRD + $cnt_OCC + $cnt_OEX + $cnt_ORF) > 0) {
			$order_info[] = array(
				'order_idx'					=>$order_data['ORDER_IDX'],
				'order_code'				=>$order_data['ORDER_CODE'],
				'order_title'				=>$order_data['ORDER_TITLE'],
				
				'create_date'				=>$order_data['CREATE_DATE'],
				
				'company_name'				=>$order_data['COMPANY_NAME'],
				'delivery_num'				=>$order_data['DELIVERY_NUM'],
				
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