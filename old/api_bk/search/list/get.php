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
} else if (isset($_POST['country'])) {
	$country = $_POST['country'];
}

if ($country == null) {
	$json_result['code'] = 301;
	$json_result['code'] = "부적절한 접근이 감지되었습니다. 사용 언어를 선택 후 다시 시도해주세요.";
}

if ($country != null) {
	$select_recommend_keyword_sql = "
		SELECT
			KEYWORD_TXT		AS KEYWORD_TXT,
			MENU_TYPE		AS MENU_TYPE,
			MENU_IDX		AS MENU_IDX
		FROM
			RECOMMEND_KEYWORD RK
		WHERE
			RK.COUNTRY = '".$country."'
		ORDER BY
			RK.DISPLAY_NUM ASC
	";
	
	$db->query($select_recommend_keyword_sql);
	
	$keyword_info = array();
	foreach($db->fetch() as $keyword_data) {
		$menu_type = $keyword_data['MENU_TYPE'];
		$menu_idx = $keyword_data['MENU_IDX'];
		
		$menu_link = null;
		if (!empty($menu_type) && !empty($menu_idx)) {
			$menu_table = null;
			switch($menu_type) {
				case "SEG" :
					$menu_table = "MENU_SEGMENT MI";
					break;
				
				case "HL1" :
					$menu_table = "MENU_HL_1 MI";
					break;
				
				case "HL2" :
					$menu_table = "MENU_HL_2 MI";
					break;
			}
			
			$select_menu_sql = "
				SELECT
					MI.IDX				AS MENU_IDX,
					MI.EXT_LINK_FLG		AS EXT_LINK_FLG,
					MI.MENU_LINK		AS MENU_LINK
				FROM
					".$menu_table."
				WHERE
					MI.IDX = ".$menu_idx."
			";
			
			$db->query($select_menu_sql);
			
			foreach($db->fetch() as $menu_data) {
				$menu_param = "&menu_type=".$menu_type."&menu_idx=".$menu_data['MENU_IDX'];
				
				if ($menu_data['MENU_LINK'] != null) {
					if ($menu_data['EXT_LINK_FLG'] == true) {
						$menu_link = "http://".$menu_data['MENU_LINK'];
					} else if ($menu_data['EXT_LINK_FLG'] == false) {
						$menu_link = $menu_data['MENU_LINK'].$menu_param;
					}
				}
			}
		}
		
		$keyword_info[] = array(
			'keyword_txt'	=>$keyword_data['KEYWORD_TXT'],
			'menu_link'		=>$menu_link
		);
	}
	
	$popular_info = array();
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
			COUNTRY = '".$country."'
		ORDER BY
			PP.DISPLAY_NUM ASC
	";
	
	$db->query($select_popular_product_sql);
	
	foreach($db->fetch() as $popular_data) {
		$popular_info[] = array(
			'product_idx'		=>$popular_data['PRODUCT_IDX'],
			'product_name'		=>$popular_data['PRODUCT_NAME'],
			'img_location'		=>$popular_data['IMG_LOCATION'],
		);
	}
	
	$json_result['data'] = array(
		'keyword_info'		=>$keyword_info,
		'popular_info'		=>$popular_info
	);
}

?>