<?php
/*
 +=============================================================================
 | 
 | 상품 리스트 - 상품 리스트 조회
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.10.19
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

include_once(dir_f_api."/common.php");
include_once(dir_f_api."/common/check.php");

error_reporting(E_ALL^ E_WARNING); 

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
} else if (isset($_SERVER['HTTP_COUNTRY'])) {
	$country = $_SERVER['HTTP_COUNTRY'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$member_level = 0;
if (isset($_SESSION['LEVEL_IDX'])) {
	$member_level = $_SESSION['LEVEL_IDX'];
}

$menu_info = array();
if (isset($menu_type) && isset($menu_idx)) {
	$menu_info = getMenuInfo($db,$country,$menu_type,$menu_idx);
}

if (isset($country) && isset($page_idx)) {
	if ($preview_flg == null) {
		$tmp_display_flg = "
			AND (DISPLAY_FLG = TRUE)
		";
	}
	
	$cnt_page = $db->count(
		"PAGE_PRODUCT",
		"
			IDX = ".$page_idx." AND
			BEST_FLG = TRUE AND
			(NOW() BETWEEN DISPLAY_START_DATE AND DISPLAY_END_DATE) AND
			DEL_FLG = FALSE
		".$tmp_display_flg
	);
	
	if ($cnt_page == 0) {
		$json_result['code'] = 402;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0095', array());
		
		echo json_encode($json_result);
		exit;
	}
	
	/* 상품 진열 페이지 조회 가능 회원 등급 체크 */
	$check_result = checkListLevel($db,$member_idx,$page_idx);	
	if ($check_result['result'] == false) {
		$json_result['code'] = 402;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0089', array());
		
		echo json_encode($json_result);
		exit;
	}
	
	$select_product_best_sql = "
		SELECT
			PG.DISPLAY_NUM				AS DISPLAY_NUM,
			PG.BACKGROUND_COLOR			AS BACKGROUND_COLOR,
			PR.IDX						AS PRODUCT_IDX,
			PR.PRODUCT_TYPE				AS PRODUCT_TYPE,
			(
				SELECT
					S_PI.IMG_LOCATION
				FROM
					PRODUCT_IMG S_PI
				WHERE
					S_PI.PRODUCT_IDX = PR.IDX AND
					IMG_TYPE = 'P' AND
					IMG_SIZE = 'M'
				ORDER BY
					S_PI.IDX ASC
				LIMIT
					0,1
			)							AS IMG_LOCATION,
			PR.PRODUCT_NAME				AS PRODUCT_NAME,
			PR.PRICE_".$country."		AS PRICE,
			PR.DISCOUNT_".$country."	AS DISCOUNT,
			PR.SALES_PRICE_".$country."	AS SALES_PRICE,
			PR.COLOR					AS COLOR
		FROM
			PRODUCT_GRID PG
			LEFT JOIN SHOP_PRODUCT PR ON
			PG.PRODUCT_IDX = PR.IDX
		WHERE
			PG.PAGE_IDX = ? AND
			PG.DEL_FLG = FALSE AND
			PR.DEL_FLG = FALSE
		ORDER BY
			PG.DISPLAY_NUM ASC
	";
	
	$db->query($select_product_best_sql,array($page_idx));
	
	foreach($db->fetch() as $data) {
		$product_idx = $data['PRODUCT_IDX'];
		
		$whish_flg = false;
		if ($member_idx > 0) {
			$cnt_wish = $db->count("WHISH_LIST","MEMBER_IDX = ".$member_idx." AND PRODUCT_IDX = ".$product_idx." AND DEL_FLG = FALSE");
			if ($cnt_wish > 0) {
				$whish_flg = true;
			}
		}
		
		$product_color = array();
		
		$tmp_product_color = getProductColor($db,$product_idx);
		if (count($tmp_product_color) > 0) {
			for ($i=0; $i<count($tmp_product_color); $i++) {
				if ($tmp_product_color[$i]['stock_status'] == 'STIN') {
					array_push($product_color,$tmp_product_color[$i]);
				}
			}
		}
		
		$grid_info[] = array(
			'display_num'		=>$data['DISPLAY_NUM'],
			'background_color'	=>$data['BACKGROUND_COLOR'],
			
			'product_idx'		=>$data['PRODUCT_IDX'],
			'product_type'		=>$data['PRODUCT_TYPE'],
			'img_location'		=>$data['IMG_LOCATION'],
			'product_name'		=>$data['PRODUCT_NAME'],
			'price'				=>number_format($data['PRICE']),
			'discount'			=>$data['DISCOUNT'],
			'sales_price'		=>number_format($data['SALES_PRICE']),
			'color'				=>$data['COLOR'],
			
			'product_color'		=>$product_color,
			'whish_flg'			=>$whish_flg
		);
	}
	
	$json_result['data'] = array(
		'menu_info'		=>$menu_info,
		'grid_info'		=>$grid_info,
	);
}

?>