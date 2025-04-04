<?php
/*
 +=============================================================================
 | 
 | 마이페이지_스탠바이 - 스탠바이 리스트 조회 // '/var/www/www/api/mypage/standby/list/get.php'
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2023.01.15
 | 최종 수정    : 양한빈
 | 최종 수정일	: 2024.05.07
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

error_reporting(E_ALL^ E_WARNING);

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

if (isset($_SERVER['HTTP_COUNTRY']) && $member_idx > 0 && $standby_idx != null) {
	$standby_page		= getStandby_page($db,$standby_idx);
	$standby_product	= getStandby_product($db,$member_idx,$standby_idx);
	
	$json_result['data'] = array(
		'standby_page'		=>$standby_page,
		'standby_product'	=>$standby_product
	);
} else {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0018',array());
	
	echo json_encode($json_result);
	exit;
}

function getStandby_page($db,$standby_idx) {
	$standby_page = array();
	
	$select_page_standby_sql = "
		SELECT
			PS.IDX					AS STANDBY_IDX,
			PS.TITLE				AS TITLE,
			PS.DESCRIPTION			AS DESCRIPTION,
			
			PS.BANNER_LOCATION_W	AS BANNER_LOCATION_W,
			PS.BANNER_LOCATION_M	AS BANNER_LOCATION_M,
			
			DATE_FORMAT(
				PS.ENTRY_START_DATE,
				'%m/%d'
			)						AS E_START_DATE,
			DATE_FORMAT(
				PS.ENTRY_START_DATE,
				'%H:%i'
			)						AS E_START_TIME,
			DATE_FORMAT(
				PS.ENTRY_END_DATE,
				'%m/%d'
			)						AS E_END_DATE,
			DATE_FORMAT(
				PS.ENTRY_END_DATE,
				'%H:%i'
			)						AS E_END_TIME,
			
			DATE_FORMAT (
				PS.ORDER_LINK_DATE,
				'%m/%d'
			)						AS O_DATE,
			DATE_FORMAT (
				PS.ORDER_LINK_DATE,
				'%h:%i'
			)						AS O_TIME,
			
			DATE_FORMAT(
				PS.PURCHASE_START_DATE,
				'%m/%d'
			)						AS P_START_DATE,
			DATE_FORMAT(
				PS.PURCHASE_START_DATE,
				'%H:%i'
			)						AS P_START_TIME,
			DATE_FORMAT(
				PS.PURCHASE_END_DATE,
				'%m/%d'
			)						AS P_END_DATE,
			DATE_FORMAT(
				PS.PURCHASE_END_DATE,
				'%H:%i'
			)						AS P_END_TIME
		FROM
			PAGE_STANDBY PS
		WHERE
			PS.IDX = ?
	";
	
	$db->query($select_page_standby_sql,array($standby_idx));
	
	foreach($db->fetch() as $data) {
		$e_start_date	= $data['E_START_DATE']." ";
		$e_end_date		= $data['E_END_DATE']." ";
		
		if ($data['E_START_DATE'] == $data['E_END_DATE']) {
			$e_end_date = "";
		}
		
		$period_entry = $e_start_date.$data['E_START_TIME']." ~ ".$e_end_date.$data['E_END_TIME'];
		
		$period_order		= $data['O_DATE']." ".$data['O_TIME'];
		
		$p_start_date	= $data['P_START_DATE']." ";
		$p_end_date		= $data['P_END_DATE']." ";
		
		if ($data['P_START_DATE'] == $data['P_END_DATE']) {
			$p_end_date = "";
		}
		
		$period_purchase = $p_start_date.$data['P_START_TIME']." ~ ".$p_end_date.$data['P_END_TIME'];
		
		$standby_page = array(
			'title'					=>$data['TITLE'],
			'description'			=>$data['DESCRIPTION'],
			'banner_location_W'		=>$data['BANNER_LOCATION_W'],
			'banner_location_M'		=>$data['BANNER_LOCATION_M'],
			
			'period_entry'			=>$period_entry,
			'period_order'			=>$period_order,
			'period_purchase'		=>$period_purchase
		);
	}
	
	return $standby_page;
}

function getStandby_product($db,$member_idx,$standby_idx) {
	$standby_product = array();
	
	$select_standby_product_sql = "
		SELECT
			SP.DISPLAY_NUM			AS DISPLAY_NUM,
			PR.IDX					AS PRODUCT_IDX,
			'PRD'					AS GRID_TYPE,
			1						AS GRID_SIZE,
			'#ffffff'				AS BACKGROUND_COLOR,
			
			PR.PRODUCT_TYPE			AS PRODUCT_TYPE,
			PR.SET_TYPE				AS SET_TYPE,
			PR.PRODUCT_NAME			AS PRODUCT_NAME,
			J_PI.IMG_LOCATION		AS IMG_LOCATION,
			
			PR.COLOR				AS COLOR,
			
			PR.PRICE_KR				AS PRICE_KR,
			PR.DISCOUNT_KR			AS DISCOUNT_KR,
			PR.SALES_PRICE_KR		AS SALES_PRICE_KR,
			
			PR.PRICE_EN				AS PRICE_EN,
			PR.DISCOUNT_EN			AS DISCOUNT_EN,
			PR.SALES_PRICE_EN		AS SALES_PRICE_EN,
			
			IFNULL(
				J_WL.CNT_WISH,0
			)						AS CNT_WISH,
			IFNULL(
				J_ST.ORDER_QTY,0
			)						AS ORDER_QTY
		FROM
			STANDBY_PRODUCT SP
			
			LEFT JOIN SHOP_PRODUCT PR ON
			SP.PRODUCT_IDX = PR.IDX
			
			LEFT JOIN (
				SELECT
					S_WL.PRODUCT_IDX		AS PRODUCT_IDX,
					COUNT(S_WL.PRODUCT_IDX)	AS CNT_WISH
				FROM
					WHISH_LIST S_WL
				WHERE
					S_WL.COUNTRY = ? AND
					S_WL.MEMBER_IDX = ? AND
					S_WL.DEL_FLG = FALSE
				GROUP BY
					S_WL.PRODUCT_IDX
			) AS J_WL ON
			PR.IDX = J_WL.PRODUCT_IDX
			
			LEFT JOIN (
				SELECT
					V_ST.PRODUCT_IDX		AS PRODUCT_IDX,
					SUM(V_ST.ORDER_QTY)		AS ORDER_QTY
				FROM
					V_STOCK V_ST
				GROUP BY
					V_ST.PRODUCT_IDX
			) AS J_ST ON
			PR.IDX = J_ST.PRODUCT_IDX
			
			LEFT JOIN (
				SELECT
					S_PI.PRODUCT_IDX		AS PRODUCT_IDX,
					S_PI.IMG_LOCATION		AS IMG_LOCATION
				FROM
					PRODUCT_IMG S_PI
				WHERE
					S_PI.IMG_TYPE = 'P' AND
					S_PI.IMG_SIZE = 'M' AND
					S_PI.DEL_FLG = FALSE
				GROUP BY
					S_PI.PRODUCT_IDX
			) AS J_PI ON
			PR.IDX = J_PI.PRODUCT_IDX
		WHERE
			SP.STANDBY_IDX = ? AND
			PR.SALE_FLG = TRUE AND
			PR.DEL_FLG = FALSE
		ORDER BY
			SP.DISPLAY_NUM ASC
	";
	
	$param_bind = array($_SERVER['HTTP_COUNTRY'],$member_idx,$standby_idx);
	
	if ($last_idx > 0) {
		$select_standby_product_sql .= " LIMIT ?,12 ";
		
		array_push($param_bind,$last_idx);
	} else {
		$select_standby_product_sql .= " LIMIT 0,12 ";
	}
	
	$db->query($select_standby_product_sql,$param_bind);
	
	$param_idx_B	= array();
	$param_idx_S	= array();
	
	foreach($db->fetch() as $data) {
		switch ($data['PRODUCT_TYPE']) {
			case "B" :
				array_push($param_idx_B,$data['PRODUCT_IDX']);
				
				break;
			
			case "S" :
				array_push($param_idx_S,$data['PRODUCT_IDX']);
				
				break;
		}
		
		$wish_flg = false;
		if ($data['CNT_WISH'] > 0) {
			$wish_flg = true;
		}
		
		$stock_status = null;
		$soldout_cnt = 0;
		$stock_close_cnt = 0;
		
		$standby_product[] = array(
			'display_num'		=>$data['DISPLAY_NUM'],
			'grid_type'			=>$data['GRID_TYPE'],
			'grid_size'			=>$data['GRID_SIZE'],
			'background_color'	=>$data['BACKGROUND_COLOR'],
			'product_idx'		=>$data['PRODUCT_IDX'],
			
			'product_type'		=>$data['PRODUCT_TYPE'],
			'product_name'		=>$data['PRODUCT_NAME'],
			'color'				=>$data['COLOR'],
			
			'price'				=>$data['PRICE_'.$_SERVER['HTTP_COUNTRY']],
			'txt_price'			=>number_format($data['PRICE_'.$_SERVER['HTTP_COUNTRY']]),
			'discount'			=>$data['DISCOUNT_'.$_SERVER['HTTP_COUNTRY']],
			'sales_price'		=>$data['SALES_PRICE_'.$_SERVER['HTTP_COUNTRY']],
			'txt_sales_price'	=>number_format($data['SALES_PRICE_'.$_SERVER['HTTP_COUNTRY']]),
			
			'img_location'		=>$data['IMG_LOCATION'],
			
			'stock_status'		=>$stock_status,
			
			'whish_flg'			=>$wish_flg
		);
	}
	
	if (count($param_idx_B) > 0 || count($param_idx_S) > 0) {
		$product_color	= getProduct_color($db,$_SERVER['HTTP_COUNTRY'],$member_idx,array_merge($param_idx_B,$param_idx_S));
	}
	
	$product_size_B = array();
	if (count($param_idx_B) > 0) {
		$product_size_B = getProduct_size_B($db,$param_idx_B);
	}
	
	$product_size_S = array();
	if (count($param_idx_S) > 0) {
		$product_size_S = getProduct_size_S($db,$param_idx_S);
	}
	
	foreach($standby_product as $key => $product) {
		$param_idx = $product['product_idx'];
		
		if (count($product_color) > 0 && isset($product_color[$param_idx])) {
			$standby_product[$key]['product_color'] = $product_color[$param_idx];
		}
		
		if (count($product_size_B) > 0 && isset($product_size_B[$param_idx])) {
			$standby_product[$key]['product_size'] = $product_size_B[$param_idx];
		}
		
		if (count($product_size_S) > 0 && isset($product_size_S[$param_idx])) {
			$standby_product[$key]['product_size'] = $product_size_S[$param_idx];
		}
	}
	
	return $standby_product;
}

function getProduct_img($db,$product_idx) {
	$product_img = array();
	
	$img_type = "";
	$p_img_type = 'P';
	$o_img_type = 'O';
	
	$cnt_thmb = $db->count("PRODUCT_IMG","PRODUCT_IDX = ? AND IMG_TYPE LIKE 'T%'",array($product_idx));
	if ($cnt_thmb > 0) {
		$p_img_type = 'TP';
		$o_img_type = 'TO';
	}
	
	$product_p_img = getIMG($db,$product_idx,"P",$p_img_type);
	$product_o_img = getIMG($db,$product_idx,"O",$o_img_type);
	
	$product_img = array(
		'product_p_img'		=>$product_p_img,
		'product_o_img'		=>$product_o_img
	);
	
	return $product_img;
}

function getIMG($db,$product_idx,$img_type,$param_type) {
	$product_img = array();
	
	$select_product_img_sql = "
		SELECT
			PI.IMG_TYPE			AS IMG_TYPE,
			PI.IMG_LOCATION		AS IMG_LOCATION
		FROM
			PRODUCT_IMG PI
		WHERE
			PI.PRODUCT_IDX = ? AND
			PI.IMG_TYPE = ? AND
			PI.IMG_SIZE = 'M'
		ORDER BY
			PI.IDX ASC
	";
	
	$db->query($select_product_img_sql,array($product_idx,$param_type));
	
	foreach($db->fetch() as $data) {
		$product_img[] = array(
			'img_type'		=>$img_type,
			'img_location'	=>$data['IMG_LOCATION']
		);
	}
	
	return $product_img;
}

?>