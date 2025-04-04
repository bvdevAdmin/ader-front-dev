<?php
/*
 +=============================================================================
 | 
 | 상품 목록 - 관련 상품 조회
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.10.25
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

if (isset($_SERVER['HTTP_COUNTRY']) && isset($relevant_idx)) {
	$param_idx = null;
	if (is_array($relevant_idx)) {
		$param_idx = $relevant_idx;
	} else {
		$param_idx = explode(",",$relevant_idx);
	}
	
	$param_bind = array($_SERVER['HTTP_COUNTRY'],$member_idx);
	
	$param_bind = array_merge($param_bind,$param_idx);
	
	$relevant_product = array();
	
	$select_relevant_product_sql = "
		SELECT
			PR.IDX						AS PRODUCT_IDX,
			PR.PRODUCT_TYPE				AS PRODUCT_TYPE,
			PR.SET_TYPE					AS SET_TYPE,
			J_PI.IMG_LOCATION			AS IMG_LOCATION,
			PR.PRODUCT_NAME				AS PRODUCT_NAME,
			PR.COLOR					AS COLOR,
			PR.COLOR_RGB				AS COLOR_RGB,
			
			PR.PRICE_KR					AS PRICE_KR,
			PR.DISCOUNT_KR				AS DISCOUNT_KR,
			PR.SALES_PRICE_KR			AS SALES_PRICE_KR,
			
			PR.PRICE_EN					AS PRICE_EN,
			PR.DISCOUNT_EN				AS DISCOUNT_EN,
			PR.SALES_PRICE_EN			AS SALES_PRICE_EN,
			
			IFNULL(J_WL.CNT_WISH,0)		AS CNT_WISH
		FROM
			SHOP_PRODUCT PR
			
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
		WHERE
			PR.IDX IN (".implode(',',array_fill(0,count($param_idx),'?')).") AND
			PR.DEL_FLG = FALSE
	";
	
	$db->query($select_relevant_product_sql,$param_bind);
	
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

		$discount		= $data['DISCOUNT_'.$_SERVER['HTTP_COUNTRY']];
		$price			= number_format($data['PRICE_'.$_SERVER['HTTP_COUNTRY']]);
		$sales_price	= number_format($data['SALES_PRICE_'.$_SERVER['HTTP_COUNTRY']]);
		
		if ($_SERVER['HTTP_COUNTRY'] == "EN") {
			$price			= number_format($data['PRICE_'.$_SERVER['HTTP_COUNTRY']],1);
			$sales_price	= number_format($data['SALES_PRICE_'.$_SERVER['HTTP_COUNTRY']],1);
		}
		
		$relevant_product[] = array(
			'product_idx'		=>$data['PRODUCT_IDX'],
			'product_type'		=>$data['PRODUCT_TYPE'],
			'product_img'		=>$data['IMG_LOCATION'],
			'product_name'		=>$data['PRODUCT_NAME'],
			'color'				=>$data['COLOR'],
			'color_rgb'			=>$data['COLOR_RGB'],
			'price'				=>$price,
			'discount'			=>$discount,
			'sales_price'		=>$sales_price,
			
			'whish_flg'			=>$wish_flg
		);
	}
	
	if (count($param_idx_B) > 0 || count($param_idx_S) > 0) {
		$product_color	= getProduct_color($db,$_SERVER['HTTP_COUNTRY'],$member_idx,array_merge($param_idx_B,$param_idx_S));
	}
	
	$product_size_B = array();
	if (count($param_idx_B) > 0) {
		$product_size_B	= getProduct_size_B($db,$param_idx_B);
	}
	
	$product_size_S = array();
	if (count($param_idx_S) > 0) {
		$product_size_S = getProduct_size_S($db,$param_idx_S);
	}
	
	foreach($relevant_product as $key => $relevant) {
		$param_idx = $relevant['product_idx'];
		
		if (count($product_color) > 0 && isset($product_color[$param_idx])) {
			$relevant_product[$key]['product_color'] = $product_color[$param_idx];
		}
		
		if (count($product_size_B) > 0 && isset($product_size_B[$param_idx])) {
			$relevant_product[$key]['product_size'] = $product_size_B[$param_idx];
		}
		
		if (count($product_size_S) > 0 && isset($product_size_S[$param_idx])) {
			$relevant_product[$key]['product_size'] = $product_size_S[$param_idx];
		}
	}
	
	$json_result['data'] = $relevant_product;
}

?>