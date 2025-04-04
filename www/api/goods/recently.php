<?php
/*
 +=============================================================================
 | 
 | 최근본 상품 리스트 - 상품 리스트 조회
 | -------
 |
 | 최초 작성	: 양한빈
 | 최초 작성일	: 2024.01.20
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

include_once $_CONFIG['PATH']['API'].'goods/filter.php';

error_reporting(E_ALL^ E_WARNING);

if ($_SESSION['MEMBER_IDX'] > 0) {
	$recently_info = array();
	
	$table = "
		SHOP_GOODS_RECENTLYVIEW A
				
		LEFT JOIN SHOP_PRODUCT PR ON
		A.GOODS_NO = PR.IDX
		
		LEFT JOIN (
			SELECT
				S_PI.PRODUCT_IDX			AS PRODUCT_IDX,
				S_PI.IMG_LOCATION			AS IMG_LOCATION
			FROM
				PRODUCT_IMG S_PI
			WHERE
				S_PI.IMG_TYPE = 'P' AND
				S_PI.IMG_SIZE = 'M' AND
				S_PI.DEL_FLG = FALSE
			GROUP BY
				S_PI.PRODUCT_IDX
		) AS J_PI ON
		A.GOODS_NO = J_PI.PRODUCT_IDX
		
		LEFT JOIN (
			SELECT
				S_WL.PRODUCT_IDX			AS PRODUCT_IDX,
				COUNT(S_WL.PRODUCT_IDX)		AS CNT_WISH
			FROM
				WHISH_LIST S_WL
			WHERE
				S_WL.MEMBER_IDX = ? AND
				S_WL.COUNTRY = ? AND
				S_WL.DEL_FLG = FALSE
			GROUP BY
				S_WL.PRODUCT_IDX
		) AS J_WL ON
		A.GOODS_NO = J_WL.PRODUCT_IDX
	";

	$where = "
		A.ID = ? 
		AND DATEDIFF(NOW(),A.REG_DATE) < ?
		AND PR.DEL_FLG = FALSE
	";

	$param_bind = array($_SESSION['MEMBER_IDX'],$_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_ID'],RECENTLY_EXPIRE_DATE);

	/* 4-1. 상품 진열 페이지 - 필터 검색조건 설정 */
	if (isset($param_filter)) {
		$sql_filter = setSQL_filter($param_filter);
		if (isset($sql_filter['where_filter']) && $sql_filter['bind_filter']) {
			$where .= $sql_filter['where_filter'];
			
			$param_bind = array_merge($param_bind,$sql_filter['bind_filter']);
		}
	}
		
	/* 4-2. 상품 진열 페이지 - 정렬 검색조건 설정 */
	$order	= setSQL_order($param_sort);
	
	$cnt_filter = $db->count($table,$where,$param_bind);
	$json_result['cnt_filter'] = $cnt_filter;

	$select_recently_product_sql = "
		SELECT
			A.GOODS_NO					AS PRODUCT_IDX,
			PR.PRODUCT_TYPE				AS PRODUCT_TYPE,
			PR.SET_TYPE					AS SET_TYPE,
			J_PI.IMG_LOCATION			AS IMG_LOCATION,
			PR.PRODUCT_NAME				AS PRODUCT_NAME,
			
			PR.PRICE_KR					AS PRICE_KR,
			PR.DISCOUNT_KR				AS DISCOUNT_KR,
			PR.SALES_PRICE_KR			AS SALES_PRICE_KR,
			
			PR.PRICE_EN					AS PRICE_EN,
			PR.DISCOUNT_EN				AS DISCOUNT_EN,
			PR.SALES_PRICE_EN			AS SALES_PRICE_EN,
			
			PR.COLOR					AS COLOR,
			PR.COLOR_RGB				AS COLOR_RGB,
			
			IFNULL(J_WL.CNT_WISH,0)		AS CNT_WISH
		FROM
			".$table."
		WHERE
			".$where."
		ORDER BY
			".$order."
	";
	
	if (isset($last_idx) && $last_idx > 0) {
		$select_recently_product_sql .= " LIMIT ?,12 ";
		
		array_push($param_bind,$last_idx);
	} else {
		$select_recently_product_sql .= " LIMIT 0,12 ";
	}
	
	$db->query($select_recently_product_sql,$param_bind);
	
	$param_idx_B	= array();
	$param_idx_S	= array();
	
	if (isset($last_idx)) {
		$display_num = $last_idx;
	} else {
		$display_num = 0;
	}
	
	foreach($db->fetch() as $data) {
		$display_num++;
		
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
		
		$product_img	= getProduct_img($db,$data['PRODUCT_IDX']);
		
		$price			= number_format($data['PRICE_'.$_SERVER['HTTP_COUNTRY']]);
		$discount		= $data['DISCOUNT_'.$_SERVER['HTTP_COUNTRY']];
		$sales_price	= number_format($data['SALES_PRICE_'.$_SERVER['HTTP_COUNTRY']]);

		if ($_SERVER['HTTP_COUNTRY'] == "EN") {
			$price			= number_format($data['PRICE_'.$_SERVER['HTTP_COUNTRY']],1);
			$sales_price	= number_format($data['SALES_PRICE_'.$_SERVER['HTTP_COUNTRY']],1);
		}
		
		$recently_info[] = array(
			'display_num'		=>$display_num,
			'product_idx'		=>$data['PRODUCT_IDX'],
			'product_type'		=>$data['PRODUCT_TYPE'],
			'set_type'			=>$data['SET_TYPE'],
			'img_location'		=>$data['IMG_LOCATION'],
			'product_img'		=>$product_img,
			'product_name'		=>$data['PRODUCT_NAME'],
			
			'price'				=>$price,
			'discount'			=>$discount,
			'sales_price'		=>$sales_price,
			
			'color'				=>$data['COLOR'],
			'color_rgb'			=>$data['COLOR_RGB'],
			'set_type'			=>$data['SET_TYPE'],
			'whish_flg'			=>$wish_flg
		);
	}
	
	if (count($param_idx_B) > 0 || count($param_idx_S) > 0) {
		$product_color	= getProduct_color($db,$_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX'],array_merge($param_idx_B,$param_idx_S));
	}
	
	$product_size_B = array();
	if (count($param_idx_B) > 0) {
		$product_size_B	= getProduct_size_B($db,$param_idx_B);
	}
	
	$product_size_S = array();
	if (count($param_idx_S) > 0) {
		$product_size_S = getProduct_size_S($db,$param_idx_S);
	}
	
	if (count($recently_info) > 0) {
		foreach($recently_info as $key => $recent) {
			$param_idx = $recent['product_idx'];
			
			if (count($product_color) > 0 && isset($product_color[$param_idx])) {
				$recently_info[$key]['product_color'] = $product_color[$param_idx];
			}
			
			if (count($product_size_B) > 0 && isset($product_size_B[$param_idx])) {
				$recently_info[$key]['product_size'] = $product_size_B[$param_idx];
			}
			
			if (count($product_size_S) > 0 && isset($product_size_S[$param_idx])) {
				$recently_info[$key]['product_size'] = $product_size_S[$param_idx];
			}
		}
	}
	
	$json_result['data'] = $recently_info;
}

/* 4-2. 상품 진열 페이지 - 정렬 검색조건 설정 */
function setSQL_order($param) {
	$order	= " A.REG_DATE DESC ";
	
	if (isset($param)) {
		switch ($param) {
			case "POP" :
				$order = " ORDER_QTY DESC";
				break;
			
			case "NEW" :
				$order = " PR.CREATE_DATE DESC ";
				break;
			
			case "MIN" :
				$order = " PR.SALES_PRICE_".$_SERVER['HTTP_COUNTRY']." ASC ";
				break;
				
			case "MAX" :
				$order = " PR.SALES_PRICE_".$_SERVER['HTTP_COUNTRY']." DESC ";
				break;
		}
	}
	
	return $order;
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
