<?php
/*
 +=============================================================================
 | 
 | 상품 목록 - 검색 상품 조회
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

include_once $_CONFIG['PATH']['API'].'goods/filter.php';

error_reporting(E_ALL^ E_WARNING);

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

/* 검색 상품 페이지 - 검색 상품 목록 조회 */
if (isset($_SERVER['HTTP_COUNTRY']) && isset($keyword)) {
	$search_product = array();
		
	/* 4. 검색 상품 페이지 - 상품 페이지 정보 조회 */
	$table = "
		SHOP_PRODUCT PR
		
		LEFT JOIN SHOP_OPTION OO ON
		PR.IDX = OO.PRODUCT_IDX
		
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
		
		LEFT JOIN (
			SELECT
				V_ST.PRODUCT_IDX		AS PRODUCT_IDX,
				SUM(V_ST.ORDER_QTY)		AS ORDER_QTY
			FROM
				V_STOCK V_ST
			GROUP BY
				V_ST.PRODUCT_IDX
		) AS J_ST ON
		PR.IDX = J_ST.PRODUCT_IDX
		
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
	";	
		
	$where = "
		(
			PR.COLOR REGEXP				? OR
			
			PR.PRODUCT_NAME REGEXP		? OR
			OO.OPTION_NAME REGEXP		? OR
			
			PR.PRODUCT_KEYWORD REGEXP	? OR
			PR.PRODUCT_TAG REGEXP		?
		) AND
		
		PR.SALE_FLG = TRUE AND
		J_SP.CNT_STANDBY IS NULL AND
		
		PR.DEL_FLG = FALSE
	";
		
	$param_bind = array(
		$_SERVER['HTTP_COUNTRY'],
		$member_idx,

		$keyword,
		$keyword,
		$keyword,
		$keyword,
		$keyword
	);
		
	/* 4-1. 검색 상품 페이지 - 필터 검색조건 설정 */
	if (isset($param_filter)) {
		$sql_filter = setSQL_filter($param_filter);
		if (isset($sql_filter['where_filter']) && $sql_filter['bind_filter']) {
			$where .= $sql_filter['where_filter'];
			
			$param_bind = array_merge($param_bind,$sql_filter['bind_filter']);
		}
	}
		
	/* 4-2. 검색 상품 페이지 - 정렬 검색조건 설정 */
	$order	= setSQL_order($param_sort);

	$count_search_product_sql = "
		SELECT
			COUNT(DISTINCT PR.IDX) AS cnt
		FROM
			".$table."
		WHERE
			".$where."
	";

	// 쿼리 실행
	$db->query($count_search_product_sql, $param_bind);

	// 결과 가져오기
	$result = $db->fetch();
	$cnt_filter = $result[0]['cnt'];

	// JSON 응답에 추가
	$json_result['cnt_filter'] = $cnt_filter;

	$select_search_product_sql = "
		SELECT
			DISTINCT PR.IDX			AS PRODUCT_IDX,
			'PRD'					AS GRID_TYPE,
			1						AS GRID_SIZE,
			'#ffffff'				AS BACKGROUND_COLOR,
			
			PR.PRODUCT_TYPE			AS PRODUCT_TYPE,
			PR.SET_TYPE				AS SET_TYPE,
			PR.PRODUCT_NAME			AS PRODUCT_NAME,
			
			PR.COLOR				AS COLOR,
			
			PR.PRICE_KR				AS PRICE_KR,
			PR.DISCOUNT_KR			AS DISCOUNT_KR,
			PR.SALES_PRICE_KR		AS SALES_PRICE_KR,
			
			PR.PRICE_EN				AS PRICE_EN,
			PR.DISCOUNT_EN			AS DISCOUNT_EN,
			PR.SALES_PRICE_EN		AS SALES_PRICE_EN,
			
			IFNULL(
				J_WL.CNT_WISH,0
			)							AS CNT_WISH,
			IFNULL(
				J_ST.ORDER_QTY,0
			)							AS ORDER_QTY,
			IFNULL(
				J_SP.CNT_STANDBY,0
			)							AS CNT_STANDBY
		FROM
			".$table."
		WHERE
			".$where."
		ORDER BY
			".$order."
	";
		
	if ($last_idx > 0) {
		$select_search_product_sql .= " LIMIT ?,12 ";
		
		array_push($param_bind,$last_idx);
	} else {
		$select_search_product_sql .= " LIMIT 0,12 ";
	}
	
	$db->query($select_search_product_sql,$param_bind);
	
	$param_idx_B	= array();
	$param_idx_S	= array();
	
	if (isset($last_idx)) {
		$display_num = $last_idx;
	} else {
		$display_num = 0;
	}
	
	foreach($db->fetch() as $data) {
		$display_num++;
		
		$product_idx	= $data['PRODUCT_IDX'];
		
		switch ($data['PRODUCT_TYPE']) {
			case "B" :
				array_push($param_idx_B,$product_idx);
				
				break;
			
			case "S" :
				array_push($param_idx_S,$product_idx);
				
				break;
		}
		
		$wish_flg = false;
		if ($data['CNT_WISH'] > 0) {
			$wish_flg = true;
		}
		
		$product_img	= getProduct_img($db,$product_idx);
		
		$stock_status = null;
		$soldout_cnt = 0;
		$stock_close_cnt = 0;

		$price			= number_format($data['PRICE_'.$_SERVER['HTTP_COUNTRY']]);
		$discount		= $data['DISCOUNT_'.$_SERVER['HTTP_COUNTRY']];
		$sales_price	= number_format($data['SALES_PRICE_'.$_SERVER['HTTP_COUNTRY']]);

		if ($_SERVER['HTTP_COUNTRY'] == "EN") {
			$price			= number_format($data['PRICE_'.$_SERVER['HTTP_COUNTRY']],1);
			$sales_price	= number_format($data['SALES_PRICE_'.$_SERVER['HTTP_COUNTRY']],1);
		}
		
		$search_product[] = array(
			'display_num'		=>$display_num,
			'grid_type'			=>$data['GRID_TYPE'],
			'grid_size'			=>$data['GRID_SIZE'],
			'background_color'	=>$data['BACKGROUND_COLOR'],
			'product_idx'		=>$data['PRODUCT_IDX'],
			
			'product_type'		=>$data['PRODUCT_TYPE'],
			'product_name'		=>$data['PRODUCT_NAME'],
			'color'				=>$data['COLOR'],
			
			'price'				=>$price,
			'discount'			=>$discount,
			'sales_price'		=>$sales_price,
			
			'product_img'		=>$product_img,
			
			'stock_status'		=>$stock_status,
			
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
	
	foreach($search_product as $key => $product) {
		$param_idx = $product['product_idx'];
		
		if (count($product_color) > 0 && isset($product_color[$param_idx])) {
			$search_product[$key]['product_color'] = $product_color[$param_idx];
		}
		
		if (count($product_size_B) > 0 && isset($product_size_B[$param_idx])) {
			$search_product[$key]['product_size'] = $product_size_B[$param_idx];
		}
		
		if (count($product_size_S) > 0 && isset($product_size_S[$param_idx])) {
			$search_product[$key]['product_size'] = $product_size_S[$param_idx];
		}
	}
	
	$json_result['data'] = array(
		'grid_info'		=>$search_product
	);
} else {
	$json_result['code'] = 402;
	$json_result['msg'] = getMsgToMsgCode($db, $_SERVER['HTTP_COUNTRY'], 'MSG_B_ERR_0095', array());
	
	echo json_encode($json_result);
	exit;
}

/* 4-2. 검색 상품 페이지 - 정렬 검색조건 설정 */
function setSQL_order($param) {
	$order	= " PR.IDX DESC ";
	
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