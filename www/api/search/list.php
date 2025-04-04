<?php
/*
 +=============================================================================
 | 
 | 상품 검색 - 추천검색어 / 실시간 인게 제품 조회
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.02.13
 | 최종 수정    : 양한빈
 | 최종 수정일	: 2025.05.28
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

if (isset($_SERVER['HTTP_COUNTRY'])) {
	$search_keyword = array();
	$search_product = array();

	$keyword_mst = getKeyword_mst($db,$_SERVER['HTTP_COUNTRY']);
	if (sizeof($keyword_mst) > 0) {
		if (isset($keyword_mst['mst_idx'])) {
			$search_keyword = getSearch_keyword($db,$keyword_mst['mst_idx']);
		}
	}

	$product_mst = getProduct_mst($db,$_SERVER['HTTP_COUNTRY']);
	if (sizeof($product_mst) > 0) {
		if (isset($product_mst['mst_idx'])) {
			$search_product = getSearch_product($db,$product_mst['mst_idx']);
		}
	}
	
	$json_result['data'] = array(
		'search_keyword'	=>$search_keyword,
		'search_product'	=>$search_product
	);
}

function getKeyword_mst($db,$country) {
	$keyword_mst = array();

	$select_keyword_mst_sql = "
		SELECT
			KM.IDX		AS MST_IDX
		FROM
			P_SEARCH_KEYWORD KM
		WHERE
			KM.COUNTRY		= ? AND
			KM.DISPLAY_FLG	= TRUE AND
			(
				KM.ALWAYS_FLG = TRUE OR
				NOW() BETWEEN KM.DISPLAY_START_DATE AND KM.DISPLAY_END_DATE
			) AND
			KM.DEL_FLG		= FALSE
	";

	$db->query($select_keyword_mst_sql,array($country));

	foreach($db->fetch() as $data) {
		$keyword_mst = array(
			'mst_idx'		=>$data['MST_IDX']
		);
	}

	return $keyword_mst;
}

function getSearch_keyword($db,$mst_idx) {
	$search_keyword = array();

	$select_search_keyword_sql = "
		SELECT
			SK.KEYWORD_TXT		AS KEYWORD_TXT,
			SK.EXT_LINK_FLG		AS EXT_FLG,
			SK.KEYWORD_LINK		AS KEYWORD_LINK
		FROM
			SEARCH_KEYWORD SK
		WHERE
			SK.PARENT_IDX	= ? AND
			SK.DEL_FLG		= FALSE
		ORDER BY
			SK.DISPLAY_NUM ASC
	";

	$db->query($select_search_keyword_sql,array($mst_idx));

	foreach($db->fetch() as $data) {
		$keyword_link = $data['KEYWORD_LINK'];
		if ($data['EXT_FLG'] == true) {
			$keyword_link = "https://".$keyword_link;
		}

		$search_keyword[] = array(
			'keyword_txt'		=>$data['KEYWORD_TXT'],
			'ext_flg'			=>$data['EXT_FLG'],
			'keyword_link'		=>$keyword_link
		);
	}

	return $search_keyword;
}

function getProduct_mst($db,$country) {
	$product_mst = array();

	$select_product_mst_sql = "
		SELECT
			PM.IDX		AS MST_IDX
		FROM
			P_SEARCH_PRODUCT PM
		WHERE
			PM.COUNTRY = ? AND
			PM.DISPLAY_FLG = TRUE AND
			(
				PM.ALWAYS_FLG = TRUE OR
				NOW() BETWEEN PM.DISPLAY_START_DATE AND PM.DISPLAY_END_DATE
			) AND
			PM.DEL_FLG = FALSE
	";

	$db->query($select_product_mst_sql,array($country));

	foreach($db->fetch() as $data) {
		$product_mst = array(
			'mst_idx'		=>$data['MST_IDX']
		);
	}

	return $product_mst;
}

function getSearch_product($db,$mst_idx) {
	$search_product = array();

	$select_search_product_sql = "
		SELECT
			PR.IDX				AS PRODUCT_IDX,
			PR.PRODUCT_NAME		AS PRODUCT_NAME,
			J_PI.IMG_LOCATION	AS IMG_LOCATION
		FROM
			SEARCH_PRODUCT SP

			LEFT JOIN SHOP_PRODUCT PR ON
			SP.PRODUCT_IDX = PR.IDX

			LEFT JOIN (
				SELECT
					S_PI.PRODUCT_IDX	AS PRODUCT_IDX,
					S_PI.IMG_LOCATION	AS IMG_LOCATION
				FROM
					PRODUCT_IMG S_PI
				WHERE
					S_PI.IMG_TYPE = 'P' AND
					S_PI.IMG_SIZE = 'S'
				GROUP BY
					S_PI.PRODUCT_IDX
			) AS J_PI ON
			PR.IDX = J_PI.PRODUCT_IDX
		WHERE
			SP.PARENT_IDX	= ? AND
			SP.DEL_FLG		= FALSE
		ORDER BY
			SP.DISPLAY_NUM ASC
	";

	$db->query($select_search_product_sql,array($mst_idx));

	foreach($db->fetch() as $data) {
		$search_product[] = array(
			'product_idx'		=>$data['PRODUCT_IDX'],
			'product_name'		=>$data['PRODUCT_NAME'],
			'img_location'		=>$data['IMG_LOCATION']
		);
	}

	return $search_product;
}

?>
