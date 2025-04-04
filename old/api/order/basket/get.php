<?php
/*
 +=============================================================================
 | 
 | 찜한 상품 리스트 - 상품 정보 수정
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.10.17
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

include_once(dir_f_api."/common.php");

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

if (!isset($country) || $member_idx == 0) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	
	echo json_encode($json_result);
	exit;
}

if (isset($basket_idx)) {
	$select_product_sql = "
		SELECT
			PR.IDX				AS PRODUCT_IDX,
			PR.PRODUCT_TYPE		AS PRODUCT_TYPE,
			PR.SET_TYPE			AS SET_TYPE,
			PR.IDX				AS PRODUCT_IDX,
			PR.COLOR			AS COLOR,
			PR.COLOR_RGB		AS COLOR_RGB
		FROM
			SHOP_PRODUCT PR
		WHERE
			PR.STYLE_CODE = (
				SELECT
					DISTINCT S_PR.STYLE_CODE
				FROM
					SHOP_PRODUCT S_PR
				WHERE
					S_PR.IDX = (
						SELECT
							S_BI.PRODUCT_IDX
						FROM
							BASKET_INFO S_BI
						WHERE
							S_BI.IDX = ".$basket_idx." AND
							S_BI.COUNTRY = '".$country."' AND
							S_BI.MEMBER_IDX = ".$member_idx."
					)
			) AND
			(
				PR.LIMIT_MEMBER = 0 OR
				PR.LIMIT_MEMBER REGEXP (
					SELECT
						LEVEL_IDX
					FROM
						MEMBER_".$country." S_MB
					WHERE
						IDX = ".$member_idx."
				)
			) AND
			PR.SALE_FLG = TRUE AND
			PR.DEL_FLG = FALSE
	";
	
	$db->query($select_product_sql);
	
	$product_size = array();
	foreach($db->fetch() as $product_data) {
		$product_idx = $product_data['PRODUCT_IDX'];
		
		$product_stock = getProductStockByIdx($db,$product_idx,0);
		
		$product_qty = $product_stock['remain_wcc_qty'];
		
		$stock_status = "";
		if ($product_qty > 0) {
			$stock_status = "STIN";	//재고 있음 (Stock in)
		} else {
			$stock_status = "STSO";	//재고 없음(사선)		→ 증가 예정 재고 없음 (Stock sold out)
		}
		
		$product_size = array();
		if (!empty($product_idx)) {
			$product_size = getProductSize($db,$product_data['PRODUCT_TYPE'],$product_data['SET_TYPE'],$product_idx);
		}
		
		$json_result['data'][] = array(
			'product_idx'	=>$product_data['PRODUCT_IDX'],
			'color'			=>$product_data['COLOR'],
			'color_rgb'		=>$product_data['COLOR_RGB'],
			'product_size'	=>$product_size
		);
	}
}

if (isset($product_idx)) {
	$select_product_sql = "
		SELECT
			PR.IDX				AS PRODUCT_IDX,
			PR.PRODUCT_TYPE		AS PRODUCT_TYPE,
			PR.SET_TYPE			AS SET_TYPE
		FROM
			SHOP_PRODUCT PR
		WHERE
			IDX = ".$product_idx."
	";
	
	$db->query($select_product_sql);
	
	$product_size = array();
	foreach($db->fetch() as $product_data) {
		$product_size = getProductSize($db,$product_data['PRODUCT_TYPE'],$product_data['SET_TYPE'],$product_data['PRODUCT_IDX']);
	}
	
	$json_result['data'] = array(
		'product_size'	=>$product_size
	);
}

?>