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

$order_status = null;
if (isset($_POST['order_status'])) {
	$order_status = $_POST['order_status'];
}

$tmp_flg = false;
if (isset($_POST['tmp_flg'])) {
	$tmp_flg = filter_var($_POST['tmp_flg'],FILTER_VALIDATE_BOOLEAN);
}

$order_idx = 0;
if (isset($_POST['order_idx'])) {
	$order_idx = $_POST['order_idx'];
}

if ($country != null && $member_idx > 0 && $order_idx > 0) {
	if ($tmp_flg != true) {
		deleteTmpOrderProduct($db,$order_idx);
	}
	
	$select_order_info_sql = "
		SELECT
			OI.IDX				AS ORDER_IDX,
			OI.ORDER_CODE		AS ORDER_CODE,
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
			ORDER_INFO OI
			LEFT JOIN DELIVERY_COMPANY DC ON
			OI.DELIVERY_IDX = DC.IDX
		WHERE
			OI.COUNTRY = '".$country."' AND
			OI.MEMBER_IDX = ".$member_idx." AND
			OI.IDX = ".$order_idx."
	";
	
	$db->query($select_order_info_sql);
	
	$order_info = array();
	foreach($db->fetch() as $order_data) {
		$order_idx = $order_data['ORDER_IDX'];
		
		$preorder_flg = false;
		$preorder_cnt = $db->count("ORDER_PRODUCT","ORDER_IDX = ".$order_idx." AND PREORDER_FLG = TRUE");
		if ($preorder_cnt > 0) {
			$preorder_flg = true;
		}
		
		$order_product = array();
		
		if (!empty($order_idx)) {
			$order_product = getOrderProductInfoByStatus($db,"PRD",$order_idx,"IND",false);
			
			$tmp_order_product_exchange = array();
			$tmp_order_product_refund = array();
			
			if ($tmp_flg == true) {
				$tmp_order_product_exchange = getOrderProductInfoByStatus($db,"OEX",$order_idx,"SUM",true);
				$tmp_order_product_refund = getOrderProductInfoByStatus($db,"ORF",$order_idx,"SUM",true);
			}
			
			$order_info = array(
				'order_idx'						=>$order_data['ORDER_IDX'],
				'order_code'					=>$order_data['ORDER_CODE'],
				'preorder_flg'					=>$preorder_flg,
				'order_date'					=>$order_data['ORDER_DATE'],
				'company_name'					=>$order_data['COMPANY_NAME'],
				'company_tel'					=>$order_data['COMPANY_TEL'],
				'update_flg'					=>$order_data['UPDATE_FLG'],
				
				'order_product'					=>$order_product,
				'tmp_order_product_exchange'	=>$tmp_order_product_exchange,
				'tmp_order_product_refund'		=>$tmp_order_product_refund,
			);
		}
	}
	
	$json_result['data'] = $order_info;
} else {
	$json_result['code'] = 301;
	$json_result['msg'] = "로그인 정보가 없습니다. 로그인 후 다시 시도해주세요.";
}

?>