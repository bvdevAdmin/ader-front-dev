<?php
/*
 +=============================================================================
 | 
 | 퀵뷰 - 실시간 인기제품 (TOP 20 등록제품)
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.10.17
 | 최종 작성   : 양한빈
 | 최종 수정일	: 2024.04.30
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

if (isset($country)) {
	$best_setting = getBest_setting($db,$country);
	
	$best_product = array();
	if ($best_setting != null && count($best_setting) > 0) {
		$best_product = getBest_product($db,$country,$member_idx,$best_setting);
	}
	
	$json_result['data'] = $best_product;
}

function getBest_setting($db,$country) {
	$best_setting = array();
	
	$select_best_info_sql = "
		SELECT
			BI.PRODUCT_TYPE		AS PRODUCT_TYPE,
			
			BI.MIN_PRODUCT_PRICE	AS MIN_PRODUCT_PRICE,
			BI.MAX_PRODUCT_PRICE	AS MAX_PRODUCT_PRICE,
			BI.MIN_DISCOUNT			AS MIN_DISCOUNT,
			BI.MAX_DISCOUNT			AS MAX_DISCOUNT,
			BI.MIN_SALES_PRICE		AS MIN_SALES_PRICE,
			BI.MAX_SALES_PRICE		AS MAX_SALES_PRICE,
			
			BI.MIN_STOCK_QTY		AS MIN_STOCK_QTY,
			BI.MAX_STOCK_QTY		AS MAX_STOCK_QTY,
			BI.MIN_ORDER_QTY		AS MIN_ORDER_QTY,
			BI.MAX_ORDER_QTY		AS MAX_ORDER_QTY,
			BI.MIN_PRODUCT_QTY		AS MIN_PRODUCT_QTY,
			BI.MAX_PRODUCT_QTY		AS MAX_PRODUCT_QTY,
			
			DATE_FORMAT(
				BI.MIN_CREATE_DATE,'%Y-%m-%d'
			)						AS MIN_CREATE_DATE,
			DATE_FORMAT(
				BI.MAX_CREATE_DATE,'%Y-%m-%d'
			)						AS MAX_CREATE_DATE,
			DATE_FORMAT(
				BI.MIN_UPDATE_DATE,'%Y-%m-%d'
			)						AS MIN_UPDATE_DATE,
			DATE_FORMAT(
				BI.MAX_UPDATE_DATE,'%Y-%m-%d'
			)						AS MAX_UPDATE_DATE,
			
			BI.ORDER_COLUMN			AS ORDER_COLUMN,
			BI.ORDER_TYPE			AS ORDER_TYPE,
			
			BI.DISPLAY_CNT			AS DISPLAY_CNT
		FROM
			BEST_INFO BI
		WHERE
			COUNTRY = ?
	";
	
	$db->query($select_best_info_sql,array($country));
	
	foreach($db->fetch() as $best_data) {
		$best_setting = array(
			'product_type'			=>$best_data['PRODUCT_TYPE'],
			
			'min_product_price'		=>$best_data['MIN_PRODUCT_PRICE'],
			'max_product_price'		=>$best_data['MAX_PRODUCT_PRICE'],
			'min_discount'			=>$best_data['MIN_DISCOUNT'],
			'max_discount'			=>$best_data['MAX_DISCOUNT'],
			'min_sales_price'		=>$best_data['MIN_SALES_PRICE'],
			'max_sales_price'		=>$best_data['MAX_SALES_PRICE'],
			
			'min_stock_qty'			=>$best_data['MIN_STOCK_QTY'],
			'max_stock_qty'			=>$best_data['MAX_STOCK_QTY'],
			'min_order_qty'			=>$best_data['MIN_ORDER_QTY'],
			'max_order_qty'			=>$best_data['MAX_ORDER_QTY'],
			'min_product_qty'		=>$best_data['MIN_PRODUCT_QTY'],
			'max_product_qty'		=>$best_data['MAX_PRODUCT_QTY'],
			
			'min_create_date'		=>$best_data['MIN_CREATE_DATE'],
			'max_create_date'		=>$best_data['MAX_CREATE_DATE'],
			'min_update_date'		=>$best_data['MIN_UPDATE_DATE'],
			'max_update_date'		=>$best_data['MAX_UPDATE_DATE'],
			
			'order_column'			=>$best_data['ORDER_COLUMN'],
			'order_type'			=>$best_data['ORDER_TYPE'],
			
			'display_cnt'			=>$best_data['DISPLAY_CNT']
		);
	}
	
	return $best_setting;
}

function getBest_product($db,$country,$member_idx,$setting) {
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

	$where = " 1=1 ";
	
	$order = "";
	$limit = "";
	
	$param_bind = array($country,$member_idx);
	
	if ($product_type != null && $product_type != "ALL") {
		$where .= " AND (PRODUCT_TYPE = ?) ";
		
		array_push($param_bind,$product_type);
	}
	
	if ($min_product_price != null || $max_product_price != null) {
		if ($min_product_price != null && $max_product_price == null) {
			$where .= "AND (PRICE >= ?)";
			
			array_push($param_bind,$min_product_price);
		} else if ($min_product_price == null && $max_product_price != null) {
			$where .= "AND (PRICE <= ?)";
			
			array_push($param_bind,$max_product_price);
		} else if ($min_product_price != null && $max_product_price != null) {
			$where .= "AND (PRICE BETWEEN ? AND ?)";
			
			array_push($param_bind,$min_product_price);
			array_push($param_bind,$max_product_price);
		}
	}
	
	if ($min_discount != null || $max_discount != null) {
		if ($min_discount != null && $max_discount == null) {
			$where .= " AND (DISCOUNT >= ?) ";
			
			array_push($param_bind,$min_discount);
		} else if ($min_discount == null && $max_discount != null) {
			$where .= " AND (DISCOUNT <= ?) ";
			
			array_push($param_bind,$max_discount);
		} else if ($min_discount != null && $max_discount != null) {
			$where .= " AND (DISCOUNT BETWEEN ? AND ?) ";
			
			array_push($param_bind,$min_discount);
			array_push($param_bind,$max_discount);
		}
	}
	
	if ($min_sales_price != null || $max_sales_price != null) {
		if ($min_sales_price != null && $max_sales_price == null) {
			$where .= " AND (SALES_PRICE >= ?) ";
			
			array_push($param_bind,$min_sales_price);
		} else if ($min_sales_price == null && $max_sales_price != null) {
			$where .= " AND (SALES_PRICE <= ?) ";
			
			array_push($param_bind,$max_sales_price);
		} else if ($min_sales_price != null && $max_sales_price != null) {
			$where .= " AND (SALES_PRICE BETWEEN ? AND ?) ";
			
			array_push($param_bind,$min_sales_price);
			array_push($param_bind,$max_sales_price);
		}
	}
	
	if ($min_order_qty != null || $max_order_qty != null) {
		if ($min_order_qty != null && $max_order_qty == null) {
			$where .= " AND (ORDER_QTY >= ?) ";
			
			array_push($param_bind,$min_order_qty);
		} else if ($min_order_qty == null && $max_order_qty != null) {
			$where .= " AND (ORDER_QTY <= ?) ";
			
			array_push($param_bind,$min_order_qty);
		} else if ($min_order_qty != null && $max_order_qty != null) {
			$where .= " AND (ORDER_QTY BETWEEN ? AND ?) ";
			
			array_push($param_bind,$min_order_qty);
			array_push($param_bind,$max_order_qty);
		}
	}
	
	if ($min_stock_qty != null || $max_stock_qty != null) {
		if ($min_stock_qty != null && $max_stock_qty == null) {
			$where .= " AND (WCC_QTY >= ?) ";
			
			array_push($param_bind,$min_stock_qty);
		} else if ($min_stock_qty == null && $max_stock_qty != null) {
			$where .= " AND (WCC_QTY <= ?) ";
			
			array_push($param_bind,$max_stock_qty);
		} else if ($min_stock_qty != null && $max_stock_qty != null) {
			$where .= " AND (WCC_QTY BETWEEN ? AND ?) ";
			
			array_push($param_bind,$min_stock_qty);
			array_push($param_bind,$max_stock_qty);
		}
	}
	
	if ($min_product_qty != null || $max_product_qty != null) {
		if ($min_product_qty != null && $max_product_qty == null) {
			$where .= " AND (REMAIN_WCC_QTY >= ?) ";
			
			array_push($param_bind,$min_product_qty);
		} else if ($min_product_qty == null && $max_product_qty != null) {
			$where .= " AND (REMAIN_WCC_QTY <= ?) ";
			
			array_push($param_bind,$max_product_qty);
		} else if ($min_product_qty != null && $max_product_qty != null) {
			$where .= " AND (REMAIN_WCC_QTY BETWEEN ? AND ?) ";
			
			array_push($param_bind,$min_product_qty);
			array_push($param_bind,$max_product_qty);
		}
	}
	
	if ($min_create_date != null || $max_create_date != null) {
		$tmp_date = " DATE_FORMAT(CREATE_DATE,'%Y-%m-%d') ";
		
		if ($min_create_date != null && $max_create_date == null) {
			$where .= " AND (".$tmp_date." >= ?) ";
			
			array_push($param_bind,$min_create_date);
		} else if ($min_create_date == null && $max_create_date != null) {
			$where .= " AND (".$tmp_date." <= ?) ";
			
			array_push($param_bind,$max_create_date);
		} else if ($min_create_date != null && $max_create_date != null) {
			$where .= " AND (".$tmp_date." BETWEEN ? AND ?) ";
			
			array_push($param_bind,$min_create_date);
			array_push($param_bind,$max_create_date);
		}
	}
	
	if ($min_update_date != null || $max_update_date != null) {
		$tmp_date = " DATE_FORMAT(UPDATE_DATE,'%Y-%m-%d') ";
		
		if ($min_update_date != null && $max_update_date == null) {
			$where .= " AND (".$tmp_date." >= ?) ";
			
			array_push($param_bind,$min_update_date);
		} else if ($min_update_date == null && $max_update_date != null) {
			$where .= " AND (".$tmp_date." <= ?) ";
			
			array_push($param_bind,$max_update_date);
		} else if ($min_update_date != null && $max_update_date != null) {
			$where .= " AND (".$tmp_date." BETWEEN ? AND ?) ";
			
			array_push($param_bind,$min_update_date);
			array_push($param_bind,$max_update_date);
		}
	}
	
	$column_price		= setColumn_name("PR.PRICE",$country);
	$column_discount	= setColumn_name("PR.DISCOUNT",$country);
	$column_sales		= setColumn_name("PR.SALES_PRICE",$country);
	
	$select_best_product_sql = "
		SELECT
			*
		FROM
			(
				SELECT
					PR.IDX								AS PRODUCT_IDX,
					PR.PRODUCT_TYPE						AS PRODUCT_TYPE,
					PR.SET_TYPE							AS SET_TYPE,
					PR.PRODUCT_NAME						AS PRODUCT_NAME,
					
					$column_price						AS PRICE,
					$column_discount					AS DISCOUNT,
					$column_sales						AS SALES_PRICE,
					
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
				WHERE
					PR.SALE_FLG = TRUE AND
					PR.DEL_FLG = FALSE
			) AS BEST
		WHERE
			".$where."
	";
	
	if ($order_column != null && $order_type != null) {
		$order = " ORDER BY $order_column $order_type ";
		
		$select_best_product_sql .= $order;
	}
	
	$select_best_product_sql .= " LIMIT 0,3 ";
	
	$db->query($select_best_product_sql,$param_bind);
	
	foreach($db->fetch() as $data) {
		$wish_flg = false;
		if ($data['CNT_WISH'] > 0) {
			$wish_flg = true;
		}
		
		$best_product[] = array(
			'product_idx'		=>$data['PRODUCT_IDX'],
			'product_name'		=>$data['PRODUCT_NAME'],
			'img_location'		=>$data['IMG_LOCATION'],
			'whish_flg'			=>$wish_flg
		);
	}
	
	return $best_product;
}

function setColumn_name($column_name,$country) {
	$result = "";
	
	if ($column_name != null && strlen($column_name) > 0) {
		$result = $column_name."_".$country;
	}
	
	return $result;
}

?>