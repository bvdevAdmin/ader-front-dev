<?php
/*
 +=============================================================================
 | 
 | 상품 검색 - 추천검색어 / 실시간 인게 제품 조회
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

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
} else if (isset($_SERVER['HTTP_COUNTRY'])) {
	$country = $_SERVER['HTTP_COUNTRY'];
}

if ($country != null) {
	/* 추천 검색어 조회 */
	$recommend_keyword	= getRecommendKeyword($db,$country);
	
	/* 인기상품 정보 조회 */
	$popular_product	= getPopularProduct($db,$country);
	
	$json_result['data'] = array(
		'keyword_info'	=>$recommend_keyword,
		'popular_info'	=>$popular_product
	);
}

/* 추천 검색어 조회 */
function getRecommendKeyword($db,$country) {
	$recommend_keyword = array();
	
	$select_recommend_keyword_sql = "
		SELECT
			KEYWORD_TXT		AS KEYWORD_TXT,
			
			MENU_TYPE		AS MENU_TYPE,
			MENU_IDX		AS MENU_IDX
		FROM
			RECOMMEND_KEYWORD RK
		WHERE
			RK.COUNTRY = ?
		ORDER BY
			RK.DISPLAY_NUM ASC
	";
	
	$db->query($select_recommend_keyword_sql,array($country));
	
	foreach($db->fetch() as $data) {
		$menu_type	= $data['MENU_TYPE'];
		$menu_idx	= $data['MENU_IDX'];
		
		/* 검색어 링크 조회 */
		$menu_link = getMenuLink($db,$menu_type,$menu_idx);
		
		$recommend_keyword[] = array(
			'keyword_txt'	=>$data['KEYWORD_TXT'],
			'menu_link'		=>$menu_link
		);
	}
	
	return $recommend_keyword;
}

/* 검색어 링크 조회 */
function getMenuLink($db,$param_type,$param_idx) {
	$menu_link = "";
	
	if ($param_type != null && $param_idx != null) {
		$menu_table = null;
		switch($param_type) {
			case "SEG" :
				$menu_table = "MENU_SEGMENTI";
				
				break;
			
			case "HL1" :
				$menu_table = "MENU_HL_1";
				
				break;
			
			case "HL2" :
				$menu_table = "MENU_HL_2";
				
				break;
		}
		
		$select_menu_sql = "
			SELECT
				MI.IDX				AS MENU_IDX,
				MI.EXT_LINK_FLG		AS EXT_LINK_FLG,
				MI.MENU_LINK		AS MENU_LINK
			FROM
				".$menu_table." MI
			WHERE
				MI.IDX = ?
		";
		
		$db->query($select_menu_sql,array($param_idx));
		
		foreach($db->fetch() as $data) {
			$param_menu = "&menu_type=".$param_type."&menu_idx=".$data['MENU_IDX'];
			
			if ($data['MENU_LINK'] != null) {
				if ($data['EXT_LINK_FLG'] == true) {
					$menu_link = "http://".$data['MENU_LINK'];
				} else if ($data['EXT_LINK_FLG'] == false) {
					$menu_link = $menu_link.$data['MENU_LINK'];
				}
			}
		}
	}
	
	return $menu_link;
}

/* 인기상품 정보 조회 */
function getPopularProduct($db,$country) {
	$popular_product = array();
	
	$select_popular_product_sql = "
		SELECT
			PP.PRODUCT_IDX		AS PRODUCT_IDX,
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
			POPULAR_PRODUCT PP
			LEFT JOIN SHOP_PRODUCT PR ON
			PP.PRODUCT_IDX = PR.IDX
		WHERE
			COUNTRY = ? AND
			PR.DEL_FLG = FALSE
		ORDER BY
			PP.DISPLAY_NUM ASC
	";
	
	$db->query($select_popular_product_sql,array($country));
	
	foreach($db->fetch() as $data) {
		$popular_product[] = array(
			'product_idx'		=>$data['PRODUCT_IDX'],
			'product_name'		=>$data['PRODUCT_NAME'],
			'img_location'		=>$data['IMG_LOCATION'],
		);
	}
	
	return $popular_product;
}

?>