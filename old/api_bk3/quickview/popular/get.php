<?php
/*
 +=============================================================================
 | 
 | 퀵뷰 - 실시간 인기제품 (TOP 20 등록제품)
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

include_once("/var/www/www/api/common.php");

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
} else if (isset($_SERVER['HTTP_COUNTRY'])) {
	$country = $_SERVER['HTTP_COUNTRY'];
}

if (isset($country)) {
	$select_popular_product_sql = "
		SELECT
			PP.PRODUCT_IDX		AS PRODUCT_IDX,
			PR.PRODUCT_TYPE		AS PRODUCT_TYPE,
			PR.SET_TYPE			AS SET_TYPE,
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
			COUNTRY = '".$country."' AND
			PR.DEL_FLG = FALSE
		ORDER BY
			PP.DISPLAY_NUM ASC
	";
	
	$db->query($select_popular_product_sql);
	
	foreach($db->fetch() as $popular_data) {
		$product_size = getProductSize($db,$popular_data['PRODUCT_TYPE'],$popular_data['SET_TYPE'],$popular_data['PRODUCT_IDX']);
		
		$stock_status = null;
		if($popular_data['SET_TYPE'] == 'B'){
			$stock_status = $product_size[0]['stock_status'];
		} else if ($popular_data['SET_TYPE'] == 'S') {
			$soldout_cnt = 0;
			$stock_close_cnt = 0;
			
			for ($i=0; $i<count($product_size[0]); $i++) {
				$tmp_stock_status = $product_size[0][$i]['stock_status'];
				if ($tmp_stock_status == "STSO") {
					$soldout_cnt++;
				} else if ($tmp_stock_status == "STCL") {
					$stock_close_cnt++;
				}
			}
			
			if (count($product_size[0]) == $soldout_cnt) {
				$stock_status = "STSO";
			} else if ($stock_close_cnt > 0) {
				$stock_status = "STCL";
			}
		}
		
		$json_result['data'][] = array(
			'product_idx'		=>$popular_data['PRODUCT_IDX'],
			'product_name'		=>$popular_data['PRODUCT_NAME'],
			'img_location'		=>$popular_data['IMG_LOCATION'],
			'stock_status'		=>$stock_status
		);
	}


//Top 20 일경우. (원복가능성있어서 주석처리)
/*
	$select_top_20_sql = "
		SELECT
			H1.IDX				AS MENU_IDX,
			H1.MENU_LINK		AS MENU_LINK
		FROM
			MENU_HL_1 H1
		WHERE
			H1.COUNTRY = '".$country."' AND
			H1.MENU_TITLE = 'TOP 20'
	";
	
	$db->query($select_top_20_sql);
	
	foreach($db->fetch() as $top_data) {
		$menu_idx = $top_data['MENU_IDX'];
		
		$page_idx = 0;
		if ($top_data['MENU_LINK'] != null) {
			$menu_link = explode("=",$top_data['MENU_LINK']);
			$page_idx = $menu_link[1];
		}
		
		$menu_info = array();
		$product_size = array();
		
		if ($page_idx > 0) {
			$popular_link = "/product/best?page_idx=".$page_idx."&menu_type=HL1&menu_idx=".$menu_idx;
			
			$select_product_grid_sql = "
				SELECT
					PG.PAGE_IDX			AS PAGE_IDX,
					PR.IDX				AS PRODUCT_IDX,
					PR.PRODUCT_TYPE		AS PRODUCT_TYPE,
					IFNULL(
						PR.SET_TYPE,'BS'
					)					AS SET_TYPE,
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
					)					AS IMG_LOCATION,
					PR.PRODUCT_NAME		AS PRODUCT_NAME
				FROM
					PRODUCT_GRID PG
					LEFT JOIN SHOP_PRODUCT PR ON
					PG.PRODUCT_IDX = PR.IDX
				WHERE
					PG.PAGE_IDX = ".$page_idx." AND
					PG.DEL_FLG = FALSE
			";
			
			$db->query($select_product_grid_sql);
			
			foreach($db->fetch() as $product_data) {
				$json_result['popular_link'] = $popular_link;
				
				$product_size = getProductSize($db,$product_data['PRODUCT_TYPE'],$product_data['SET_TYPE'],$product_data['PRODUCT_IDX']);
				
				$json_result['data'][] = array(
					'product_idx'		=>$product_data['PRODUCT_IDX'],
					'product_type'		=>$product_data['PRODUCT_TYPE'],
					'set_type'			=>$product_data['SET_TYPE'],
					'img_location'		=>$product_data['IMG_LOCATION'],
					'product_name'		=>$product_data['PRODUCT_NAME'],
					
					'product_size'		=>$product_size,
				);
			}
		}
	}
	*/
}

?>