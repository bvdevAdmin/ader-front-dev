<?php
/*
 +=============================================================================
 | 
 | 상품 목록 - 상품 목록 조회
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

if (isset($_SERVER['HTTP_COUNTRY'])) {
	$setting = getBest_setting($db,$_SERVER['HTTP_COUNTRY']);
	
	$page_product = array();
	
	$where = "
		PR.SALE_FLG = TRUE AND
		PR.DEL_FLG = FALSE
	";
	
	$tmp_where = "";
	
	$product_type			= $setting['product_type'];
	
	$min_product_price		= $setting['min_product_price'];
	$max_product_price		= $setting['max_product_price'];
	$min_discount			= $setting['min_discount'];
	$max_discount			= $setting['max_discount'];
	$min_sales_price		= $setting['min_sales_price'];
	$max_sales_price		= $setting['max_sales_price'];
	
	$min_stock_qty			= $setting['min_stock_qty'];
	$max_stock_qty			= $setting['max_stock_qty'];
	$min_order_qty			= $setting['min_order_qty'];
	$max_order_qty			= $setting['max_order_qty'];
	$min_product_qty		= $setting['min_product_qty'];
	$max_product_qty		= $setting['max_product_qty'];
	
	$min_create_date		= $setting['min_create_date'];
	$max_create_date		= $setting['max_create_date'];
	$min_update_date		= $setting['min_update_date'];
	$max_update_date		= $setting['max_update_date'];

	$order_column			= $setting['order_column'];
	$order_type				= $setting['order_type'];
	
	$display_cnt			= $setting['display_cnt'];
	
	$param_bind = array($_SERVER['HTTP_COUNTRY'],$member_idx);
	
	$c_price = array(
		'KR'		=>"PRICE_KR",
		'EN'		=>"PRICE_EN"
	);

	$c_discount = array(
		'KR'		=>"DISCOUNT_KR",
		'EN'		=>"DISCOUNT_EN"
	);

	$c_sales_price = array(
		'KR'		=>"SALES_PRICE_KR",
		'EN'		=>"SALES_PRICE_EN"
	);

	if ($product_type != null && $product_type != "ALL") {
		$tmp_where .= " AND (PRODUCT_TYPE = ?) ";
		
		array_push($param_bind,$product_type);
	}
	
	if ($min_product_price != null || $max_product_price != null) {
		if ($min_product_price != null && $max_product_price == null) {
			$tmp_where .= "AND (".$c_price[$_SERVER['HTTP_COUNTRY']]." >= ?)";
			
			array_push($param_bind,$min_product_price);
		} else if ($min_product_price == null && $max_product_price != null) {
			$tmp_where .= "AND (".$c_price[$_SERVER['HTTP_COUNTRY']]." <= ?)";
			
			array_push($param_bind,$max_product_price);
		} else if ($min_product_price != null && $max_product_price != null) {
			$tmp_where .= "AND (".$c_price[$_SERVER['HTTP_COUNTRY']]." BETWEEN ? AND ?)";
			
			array_push($param_bind,$min_product_price);
			array_push($param_bind,$max_product_price);
		}
	}
	
	if ($min_discount != null || $max_discount != null) {
		if ($min_discount != null && $max_discount == null) {
			$tmp_where .= " AND (".$c_discount[$_SERVER['HTTP_COUNTRY']]." >= ?) ";
			
			array_push($param_bind,$min_discount);
		} else if ($min_discount == null && $max_discount != null) {
			$tmp_where .= " AND (".$c_discount[$_SERVER['HTTP_COUNTRY']]." <= ?) ";
			
			array_push($param_bind,$max_discount);
		} else if ($min_discount != null && $max_discount != null) {
			$tmp_where .= " AND (".$c_discount[$_SERVER['HTTP_COUNTRY']]." BETWEEN ? AND ?) ";
			
			array_push($param_bind,$min_discount);
			array_push($param_bind,$max_discount);
		}
	}
	
	if ($min_sales_price != null || $max_sales_price != null) {
		if ($min_sales_price != null && $max_sales_price == null) {
			$tmp_where .= " AND (".$c_sales_price[$_SERVER['HTTP_COUNTRY']]." >= ?) ";
			
			array_push($param_bind,$min_sales_price);
		} else if ($min_sales_price == null && $max_sales_price != null) {
			$tmp_where .= " AND (".$c_sales_price[$_SERVER['HTTP_COUNTRY']]." <= ?) ";
			
			array_push($param_bind,$max_sales_price);
		} else if ($min_sales_price != null && $max_sales_price != null) {
			$tmp_where .= " AND (".$c_sales_price[$_SERVER['HTTP_COUNTRY']]." BETWEEN ? AND ?) ";
			
			array_push($param_bind,$min_sales_price);
			array_push($param_bind,$max_sales_price);
		}
	}
	
	if ($min_order_qty != null || $max_order_qty != null) {
		if ($min_order_qty != null && $max_order_qty == null) {
			$tmp_where .= " AND (ORDER_QTY >= ?) ";
			
			array_push($param_bind,$min_order_qty);
		} else if ($min_order_qty == null && $max_order_qty != null) {
			$tmp_where .= " AND (ORDER_QTY <= ?) ";
			
			array_push($param_bind,$min_order_qty);
		} else if ($min_order_qty != null && $max_order_qty != null) {
			$tmp_where .= " AND (ORDER_QTY BETWEEN ? AND ?) ";
			
			array_push($param_bind,$min_order_qty);
			array_push($param_bind,$max_order_qty);
		}
	}
	
	if ($min_stock_qty != null || $max_stock_qty != null) {
		if ($min_stock_qty != null && $max_stock_qty == null) {
			$tmp_where .= " AND (WCC_QTY >= ?) ";
			
			array_push($param_bind,$min_stock_qty);
		} else if ($min_stock_qty == null && $max_stock_qty != null) {
			$tmp_where .= " AND (WCC_QTY <= ?) ";
			
			array_push($param_bind,$max_stock_qty);
		} else if ($min_stock_qty != null && $max_stock_qty != null) {
			$tmp_where .= " AND (WCC_QTY BETWEEN ? AND ?) ";
			
			array_push($param_bind,$min_stock_qty);
			array_push($param_bind,$max_stock_qty);
		}
	}
	
	if ($min_product_qty != null || $max_product_qty != null) {
		if ($min_product_qty != null && $max_product_qty == null) {
			$tmp_where .= " AND (REMAIN_WCC_QTY >= ?) ";
			
			array_push($param_bind,$min_product_qty);
		} else if ($min_product_qty == null && $max_product_qty != null) {
			$tmp_where .= " AND (REMAIN_WCC_QTY <= ?) ";
			
			array_push($param_bind,$max_product_qty);
		} else if ($min_product_qty != null && $max_product_qty != null) {
			$tmp_where .= " AND (REMAIN_WCC_QTY BETWEEN ? AND ?) ";
			
			array_push($param_bind,$min_product_qty);
			array_push($param_bind,$max_product_qty);
		}
	}
	
	if ($min_create_date != null || $max_create_date != null) {
		$tmp_date = " DATE_FORMAT(CREATE_DATE,'%Y-%m-%d') ";
		
		if ($min_create_date != null && $max_create_date == null) {
			$tmp_where .= "
				AND (? <= ".$tmp_date.")
			";
			
			array_push($param_bind,$min_create_date);
		} else if ($min_create_date == null && $max_create_date != null) {
			$tmp_where .= "
				AND (? >=".$tmp_date." <= ?)
			";
			
			array_push($param_bind,$max_create_date);
		} else if ($min_create_date != null && $max_create_date != null) {
			$tmp_where .= " AND (".$tmp_date." BETWEEN ? AND ?) ";
			
			array_push($param_bind,$min_create_date);
			array_push($param_bind,$max_create_date);
		}
	}
	
	if ($min_update_date != null || $max_update_date != null) {
		$tmp_date = " DATE_FORMAT(UPDATE_DATE,'%Y-%m-%d') ";
		
		if ($min_update_date != null && $max_update_date == null) {
			$tmp_where .= "
				AND (? <= ".$tmp_date.")
			";
			
			array_push($param_bind,$min_update_date);
		} else if ($min_update_date == null && $max_update_date != null) {
			$tmp_where .= "
				AND (? >= ".$tmp_date.")
			";
			
			array_push($param_bind,$max_update_date);
		} else if ($min_update_date != null && $max_update_date != null) {
			$tmp_where .= " AND (".$tmp_date." BETWEEN ? AND ?) ";
			
			array_push($param_bind,$min_update_date);
			array_push($param_bind,$max_update_date);
		}
	}
	
	$where .= $tmp_where;
	
	/* 4-1. 상품 진열 페이지 - 필터 검색조건 설정 */
	if (isset($param_filter)) {
		$sql_filter = setSQL_filter($param_filter);
		if (isset($sql_filter['where_filter']) && $sql_filter['bind_filter']) {
			$where .= $sql_filter['where_filter'];
			
			$param_bind = array_merge($param_bind,$sql_filter['bind_filter']);
		}
	}

	/* 4-2. 검색 상품 페이지 - 정렬 검색조건 설정 */
	if ($order_column != null && $order_type != null) {
		$order	= " ".$order_column." ".$order_type;
	} else {
		$order	= setSQL_order($param_sort);
	}

	$table = "
		SHOP_PRODUCT PR
		
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
		) AS J_PI ON
		PR.IDX = J_PI.PRODUCT_IDX
		
		LEFT JOIN (
			SELECT
				PRODUCT_IDX					AS PRODUCT_IDX,
				SUM(V_ST.WCC_QTY)			AS WCC_QTY,
				SUM(V_ST.ORDER_QTY)			AS ORDER_QTY,
				SUM(V_ST.REMAIN_WCC_QTY)	AS REMAIN_WCC_QTY
			FROM
				V_STOCK V_ST
			GROUP BY
				V_ST.PRODUCT_IDX
		) AS J_ST ON
		PR.IDX = J_ST.PRODUCT_IDX
		
		LEFT JOIN (
			SELECT
				S_WL.PRODUCT_IDX			AS PRODUCT_IDX,
				COUNT(S_WL.PRODUCT_IDX)		AS CNT_WISH
			FROM
				WHISH_LIST S_WL
			WHERE
				S_WL.COUNTRY = ? AND
				S_WL.MEMBER_IDX = ?
			GROUP BY
				S_WL.PRODUCT_IDX
		) AS J_WL ON
		PR.IDX = J_WL.PRODUCT_IDX
	";

	$cnt_filter = $db->count($table,$where,$param_bind);

	$json_result['cnt_filter'] = $cnt_filter;

	$select_best_product_sql = "
		SELECT
			'PRD'								AS GRID_TYPE,
			1									AS GRID_SIZE,
			NULL								AS BACKGROUND_COLOR,
			PR.IDX								AS PRODUCT_IDX,
			
			PR.PRODUCT_TYPE						AS PRODUCT_TYPE,
			PR.SET_TYPE							AS SET_TYPE,
			PR.PRODUCT_NAME						AS PRODUCT_NAME,
			PR.".$c_price[$_SERVER['HTTP_COUNTRY']]."			AS PRICE,
			PR.".$c_discount[$_SERVER['HTTP_COUNTRY']]."		AS DISCOUNT,
			PR.".$c_sales_price[$_SERVER['HTTP_COUNTRY']]."		AS SALES_PRICE,
			
			PR.CREATE_DATE						AS CREATE_DATE,
			PR.UPDATE_DATE						AS UPDATE_DATE,
			
			J_PI.IMG_LOCATION					AS IMG_LOCATION,
			
			IFNULL(
				J_ST.WCC_QTY,0
			)									AS WCC_QTY,
			IFNULL(
				J_ST.ORDER_QTY,0
			)									AS ORDER_QTY,
			IFNULL(
				J_ST.REMAIN_WCC_QTY,0
			)									AS REMAIN_WCC_QTY,
			
			IFNULL(J_WL.CNT_WISH,0)				AS CNT_WISH
		FROM
			".$table."
		WHERE
			".$where."
		ORDER BY
			".$order."
	";

	/* 4-2. 상품 진열 페이지 - 정렬 검색조건 설정 */

	if ($display_cnt > 0) {
		if ($last_idx > 0) {
			if ($last_idx <= $display_cnt) {
				$limit = $display_cnt - $last_idx;
	
				$select_best_product_sql .= " LIMIT ?,? ";
				
				array_push($param_bind,$last_idx);
				array_push($param_bind,$limit);
			} else {
				$select_best_product_sql .= " LIMIT 0,0 ";
			}
		} else {
			$select_best_product_sql .= " LIMIT 0,12 ";
		}
	} else {
		if ($last_idx > 0) {
			$select_best_product_sql .= " LIMIT ?,12 ";
			
			array_push($param_bind,$last_idx);
		} else {
			$select_best_product_sql .= " LIMIT 0,12 ";
		}
	}
	
	$db->query($select_best_product_sql,$param_bind);
	
	$param_idx_B	= array();
	$param_idx_S	= array();
	
	$display_num = $last_idx + 1;
	
	foreach($db->fetch() as $data) {
		$grid_type		= $data['GRID_TYPE'];
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
		
		$stock_status		= null;
		$soldout_cnt		= 0;
		$stock_close_cnt	= 0;

		$discount		= $data['DISCOUNT'];

		$price			 = number_format($data['PRICE']);
		$sales_price	 = number_format($data['SALES_PRICE']);

		if ($_SERVER['HTTP_COUNTRY'] == "EN") {
			$price		 = number_format($data['PRICE'],1);
			$sales_price = number_format($data['SALES_PRICE'],1);
		}
		
		$page_product[] = array(
			'display_num'		=>$display_num,
			'grid_type'			=>$data['GRID_TYPE'],
			'grid_size'			=>$data['GRID_SIZE'],
			'background_color'	=>$data['BACKGROUND_COLOR'],
			'product_idx'		=>$data['PRODUCT_IDX'],
			
			'product_type'		=>$data['PRODUCT_TYPE'],
			'product_name'		=>$data['PRODUCT_NAME'],
			
			'price'				=>$price,
			'discount'			=>$discount,
			'sales_price'		=>$sales_price,
			
			'color'				=>$data['COLOR'],
			
			'product_img'		=>$product_img,
			
			'stock_status'		=>$stock_status,
			
			'whish_flg'			=>$wish_flg
		);
		
		$display_num++;
	}
	
	if (count($param_idx_B) > 0 || count($param_idx_S) > 0) {
		$product_color	= getProduct_color($db,$_SERVER['HTTP_COUNTRY'],$member_idx,array_merge($param_idx_B,$param_idx_S));
	}
	
	if (count($param_idx_B) > 0) {
		$product_size_B	= getProduct_size_B($db,$param_idx_B);
	}
	
	$product_size_S = array();
	if (count($param_idx_S) > 0) {
		$product_size_S = getProduct_size_S($db,$param_idx_S);
	}
	
	foreach($page_product as $key => $product) {
		$param_idx = $product['product_idx'];
		
		if (count($product_color) > 0 && isset($product_color[$param_idx])) {
			$page_product[$key]['product_color'] = $product_color[$param_idx];
		}
		
		if (count($product_size_B) > 0 && isset($product_size_B[$param_idx])) {
			$page_product[$key]['product_size'] = $product_size_B[$param_idx];
		}
		
		if (count($product_size_S) > 0 && isset($product_size_S[$param_idx])) {
			$page_product[$key]['product_size'] = $product_size_S[$param_idx];
		}
	}
	
	$json_result['data'] = array(
		'grid_info'		=>$page_product
	);
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