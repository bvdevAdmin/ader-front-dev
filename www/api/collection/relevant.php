<?php
/*
 +=============================================================================
 | 
 | 컬렉션 조회 - 컬렉션 관련상품 조회
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2023.02.10
 | 최종 수정일	: 2024.09.03
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$cnt_relevant = $db->get('COLLECTION_RELEVANT_PRODUCT',"C_PRODUCT_IDX = ?",array($c_product_idx));
$relevant_flg = $db->get('COLLECTION_PRODUCT','IDX = ?',array($c_product_idx))[0]['RELEVANT_FLG'];
$relevant_product = [];
if ($cnt_relevant > 0 && $relevant_flg == true) {
	$select_relevant_product_sql = "
		SELECT
			PR.IDX								AS PRODUCT_IDX,
			PR.PRODUCT_NAME						AS PRODUCT_NAME,
			PR.PRODUCT_TYPE						AS PRODUCT_TYPE,
			PR.SET_TYPE							AS SET_TYPE,
			J_PI.IMG_LOCATION					AS IMG_LOCATION,

			PR.PRICE_KR							AS PRICE_KR,
			PR.DISCOUNT_KR						AS DISCOUNT_KR,
			PR.SALES_PRICE_KR					AS SALES_PRICE_KR,
			
			PR.PRICE_EN							AS PRICE_EN,
			PR.DISCOUNT_EN						AS DISCOUNT_EN,
			PR.SALES_PRICE_EN					AS SALES_PRICE_EN,

			RP.SOLD_OUT_FLG						AS SOLD_OUT_FLG
		FROM
			COLLECTION_RELEVANT_PRODUCT RP
			
			LEFT JOIN SHOP_PRODUCT PR ON
			RP.PRODUCT_IDX = PR.IDX
			
			LEFT JOIN (
				SELECT
					S_PI.PRODUCT_IDX			AS PRODUCT_IDX,
					S_PI.IMG_LOCATION			AS IMG_LOCATION
				FROM
					PRODUCT_IMG S_PI
				WHERE
					S_PI.IMG_TYPE = 'P' AND
					S_PI.IMG_SIZE = 'S' AND
					S_PI.DEL_FLG = FALSE
				GROUP BY
					S_PI.PRODUCT_IDX
				ORDER BY
					S_PI.IDX ASC
			) AS J_PI ON
			PR.IDX = J_PI.PRODUCT_IDX
		WHERE
			RP.C_PRODUCT_IDX = ? AND
			RP.DISPLAY_FLG = TRUE AND
			PR.DEL_FLG = FALSE
		ORDER BY
			RP.DISPLAY_NUM
	";
	
	$db->query($select_relevant_product_sql,array($c_product_idx));
	
	$param_idx_B	= array();
	$param_idx_S	= array();
	
	foreach($db->fetch() as $data) {
		$wish_flg = false;
		if ($member_idx > 0) {
			$cnt_wish = $db->count(
				"WHISH_LIST",
				"
					COUNTRY = ? AND
					MEMBER_IDX = ? AND
					PRODUCT_IDX = ? AND
					DEL_FLG = FALSE
				",
				array(
					$_SERVER['HTTP_COUNTRY'],
					$member_idx,
					$data['PRODUCT_IDX']
				)
			);
			
			$wish_flg = $cnt_wish > 0 ? true : false;
		}
		
		switch ($data['PRODUCT_TYPE']) {
			case "B" :
				array_push($param_idx_B,$data['PRODUCT_IDX']);
				
				break;
			
			case "S" :
				array_push($param_idx_S,$data['PRODUCT_IDX']);
				
				break;
		}

		$relevant_product[] = array(
			'product_idx'		=>$data['PRODUCT_IDX'],
			'product_type'		=>$data['PRODUCT_TYPE'],
			'product_name'		=>$data['PRODUCT_NAME'],
			'img_location'		=>$data['IMG_LOCATION'],
			'price'				=>$data['PRICE_'.$_SERVER['HTTP_COUNTRY']],
			'txt_price'			=>number_format($data['PRICE_'.$_SERVER['HTTP_COUNTRY']]),
			'discount'			=>$data['DISCOUNT_'.$_SERVER['HTTP_COUNTRY']],
			'sales_price'		=>$data['SALES_PRICE_'.$_SERVER['HTTP_COUNTRY']],
			'txt_sales_price'	=>number_format($data['SALES_PRICE_'.$_SERVER['HTTP_COUNTRY']]),
			'sold_out_flg'		=>$data['SOLD_OUT_FLG'],
			'whish_flg'			=>$wish_flg
		);
	}

	if (count($relevant_product) > 0) {
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
	}
	
	$json_result['data'] = $relevant_product;
}

?>