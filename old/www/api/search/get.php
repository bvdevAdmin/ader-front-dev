<?php
/*
 +=============================================================================
 | 
 | 상품 검색 - 추천검색어 / 실시간 인게 제품 검색
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.02.13
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

if (isset($search_keyword)) {
	$select_product_idx_sql = "
		SELECT
			PR.IDX				AS PRODUCT_IDX
		FROM
			SHOP_PRODUCT PR
			LEFT JOIN ORDERSHEET_MST OM ON
			PR.ORDERSHEET_IDX = OM.IDX
			LEFT JOIN ORDERSHEET_OPTION OO ON
			PR.ORDERSHEET_IDX = OO.ORDERSHEET_IDX
		WHERE
			(
				PR.PRODUCT_NAME REGEXP ? OR
				OO.OPTION_NAME REGEXP ? OR
				PR.PRODUCT_KEYWORD REGEXP ? OR
				PR.PRODUCT_TAG REGEXP ? OR
				OM.COLOR REGEXP ?
			) AND
			(
				SELECT
					COUNT(S_PI.IDX)
				FROM
					PRODUCT_IMG S_PI
				WHERE
					S_PI.PRODUCT_IDX = PR.IDX
			) > 0 AND
			PR.SALE_FLG = TRUE AND
			PR.DEL_FLG = FALSE AND
			PR.INDP_FLG = FALSE AND
			PR.SOLD_OUT_FLG = FALSE
	";
	
	$db->query($select_product_idx_sql,array($search_keyword,$search_keyword,$search_keyword,$search_keyword,$search_keyword));
	
	$product_idx = array();
	foreach($db->fetch() as $product_data) {
		$product_idx[] = $product_data['PRODUCT_IDX'];
	}
	
	$product_info = array();
	if (count($product_idx) > 0) {
		$select_product_sql = "
			SELECT
				PR.IDX				AS PRODUCT_IDX,
				PR.PRODUCT_NAME		AS PRODUCT_NAME,
				(
					SELECT
						S_PI.IMG_LOCATION
					FROM
						PRODUCT_IMG S_PI
					WHERE
						S_PI.PRODUCT_IDX = PR.IDX AND
						S_PI.IMG_TYPE = 'P' AND
						S_PI.IMG_SIZE = 'S'
					ORDER BY
						S_PI.IDX ASC
					LIMIT
						0,1
				)					AS IMG_LOCATION
			FROM
				SHOP_PRODUCT PR
			WHERE
				PR.IDX IN (".implode(",",$product_idx).")
		";
		
		$db->query($select_product_sql);
		
		foreach($db->fetch() as $search_data) {
			$product_info[] = array(
				'product_idx'		=>$search_data['PRODUCT_IDX'],
				'product_name'		=>$search_data['PRODUCT_NAME'],
				'img_location'		=>$search_data['IMG_LOCATION']
			);
		}
	}
	
	$json_result['data'] = $product_info;
}

?>