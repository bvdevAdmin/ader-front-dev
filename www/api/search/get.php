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

if (isset($search_keyword) && strlen($search_keyword) > 0) {
	$select_search_product_sql = "
		SELECT
			DISTINCT PR.IDX		AS PRODUCT_IDX,
			PR.PRODUCT_NAME		AS PRODUCT_NAME,
			J_PI.IMG_LOCATION	AS IMG_LOCATION
		FROM
			SHOP_PRODUCT PR
			
			LEFT JOIN SHOP_OPTION OO ON
			PR.IDX = OO.PRODUCT_IDX
			
			LEFT JOIN (
				SELECT
					S_PI.PRODUCT_IDX	AS PRODUCT_IDX,
					S_PI.IMG_LOCATION	AS IMG_LOCATION
				FROM
					PRODUCT_IMG S_PI
				WHERE
					S_PI.IMG_TYPE = 'P' AND
					S_PI.IMG_SIZE = 'S' AND
					S_PI.DEL_FLG = FALSE
				GROUP BY
					S_PI.PRODUCT_IDX
			) AS J_PI ON
			PR.IDX = J_PI.PRODUCT_IDX
			
			LEFT JOIN (
				SELECT
					SP.PRODUCT_IDX		AS PRODUCT_IDX,
					COUNT(
						SP.PRODUCT_IDX
					)					AS CNT_STANDBY
				FROM
					STANDBY_PRODUCT SP
					
					LEFT JOIN PAGE_STANDBY PS ON
					SP.STANDBY_IDX = PS.IDX
				WHERE
					PS.PURCHASE_END_DATE > NOW()
				GROUP BY
					SP.PRODUCT_IDX
			) AS J_SP ON
			PR.IDX = J_SP.PRODUCT_IDX
		WHERE
			(
				PR.COLOR REGEXP				? OR
				
				PR.PRODUCT_NAME REGEXP		? OR
				OO.OPTION_NAME REGEXP		? OR
				
				PR.PRODUCT_KEYWORD REGEXP	? OR
				PR.PRODUCT_TAG REGEXP		?
			) AND
			
			PR.SALE_FLG = TRUE AND
			J_PI.IMG_LOCATION IS NOT NULL AND
			J_SP.CNT_STANDBY IS NULL AND
			
			PR.DEL_FLG = FALSE
		ORDER BY
			PR.IDX DESC
		LIMIT
			0,12
	";
	
	$db->query(
		$select_search_product_sql,
		array(
			$search_keyword,
			$search_keyword,
			$search_keyword,
			$search_keyword,
			$search_keyword
		)
	);
	
	foreach($db->fetch() as $data) {
		$json_result['data'][] = array(
			'product_idx'		=>$data['PRODUCT_IDX'],
			'product_name'		=>$data['PRODUCT_NAME'],
			'img_location'		=>$data['IMG_LOCATION']
		);
	}
}

?>