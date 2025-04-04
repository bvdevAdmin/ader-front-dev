<?php
/*
 +=============================================================================
 | 
 | 상품 필터
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2024.10.16
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

if (isset($total_type) && isset($total_param)) {
	$table = "";
	
	$where = "";
	
	$param_bind = array();
	
	switch ($total_type) {
		case "page" :
			$table = "
				SHOP_PRODUCT PR
				
				LEFT JOIN PRODUCT_GRID PG ON
				PR.IDX = PG.PRODUCT_IDX
				
				LEFT JOIN LINE_INFO LI ON
				PR.LINE_IDX = LI.IDX
			";
			
			$where .= " AND (PG.PAGE_IDX = ?) AND
				PG.TYPE = 'PRD' AND
				
				PG.PRODUCT_IDX > 0 AND
				PR.SALE_FLG = TRUE AND
				
				PG.DEL_FLG = FALSE AND
				PR.DEL_FLG = FALSE";
			
			array_push($param_bind,$total_param);
			
			break;
		
		case "search" :
			$table = "
				SHOP_PRODUCT PR
				
				LEFT JOIN SHOP_OPTION OO ON
				PR.IDX = OO.PRODUCT_IDX
				
				LEFT JOIN LINE_INFO LI ON
				PR.LINE_IDX = LI.IDX
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
			
			$where .= "
				AND (
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
				$total_param,
				$total_param,
				$total_param,
				$total_param,
				$total_param
			);
			
			break;
		
		case "standby" :
			$table = "
				SHOP_PRODUCT PR

				LEFT JOIN LINE_INFO LI ON
				PR.LINE_IDX = LI.IDX
			";
			
			$where .= "
				AND (
					PR.IDX IN (
						SELECT
							S_SP.PRODUCT_IDX
						FROM
							STANDBY_PRODUCT S_SP
						WHERE
							S_SP.STANDBY_IDX = (
								SELECT
									S_PS.IDX
								FROM
									PAGE_STANDBY S_PS
								WHERE
									S_PS.UUID = ?
							)
					)
				)
			";
			
			array_push($param_bind,$total_param);
			
			break;
		
		case "best" :
			$total_param = setParam_best($db);

			$table = "
				SHOP_PRODUCT PR
				
				LEFT JOIN LINE_INFO LI ON
				PR.LINE_IDX = LI.IDX
				
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
			
			$where		= $total_param['where'];
			$param_bind	= $total_param['param_bind'];
		
			break;
		case "recently" :
			$table = "
				SHOP_PRODUCT PR

				LEFT JOIN LINE_INFO LI ON
				PR.LINE_IDX = LI.IDX
			";
			
			$where .= "
				AND (
					PR.IDX IN (
						SELECT
							S_RV.GOODS_NO
						FROM
							SHOP_GOODS_RECENTLYVIEW S_RV
						WHERE
							S_RV.ID = ? AND
							DATEDIFF(NOW(),S_RV.REG_DATE) < ?
					)
				)
			";

			$param_bind = array($total_param,RECENTLY_EXPIRE_DATE);

			break;
	}
	
	$select_total_product_sql = "
		SELECT
			DISTINCT PR.IDX			AS PRODUCT_IDX,
			
			FILTER_FT		AS FILTER_FT,
			FIT				AS FIT,
			
			FILTER_GP		AS FILTER_GP,
			GRAPHIC			AS GRAPHIC,
			
			FILTER_LN		AS FILTER_LN,
			PR.LINE_IDX		AS LINE,
			
			FILTER_CL		AS FILTER_CL,
			FILTER_SZ		AS FILTER_SZ
		FROM
			".$table."
		WHERE
			PR.SALE_FLG = TRUE AND
			PR.DEL_FLG = FALSE
			".$where."
	";
	
	$db->query($select_total_product_sql,$param_bind);
	
	foreach($db->fetch() as $data) {
		$filter_CL = array();
		if ($data['FILTER_CL'] != null && strlen($data['FILTER_CL']) > 0) {
			$filter_CL = explode(",",$data['FILTER_CL']);
		}
		
		$filter_SZ = array();
		if ($data['FILTER_SZ'] != null && strlen($data['FILTER_CL']) > 0) {
			$filter_SZ = explode(",",$data['FILTER_SZ']);
		}
		
		$fit = null;
		if ($data['FILTER_FT'] == true) {
			$fit = $data['FIT'];
		}
		
		$graphic = null;
		if ($data['FILTER_GP'] == true) {
			$graphic = $data['GRAPHIC'];
		}
		
		$line = null;
		if ($data['FILTER_LN'] == true) {
			$line = $data['LINE'];
		}
		
		$json_result['data'][] = array(
			'product_idx'	=>$data['PRODUCT_IDX'],
			
			'filter_FT'		=>$fit,
			'filter_GP'		=>$graphic,
			'filter_LN'		=>$line,
			
			'filter_CL'		=>$filter_CL,
			'filter_SZ'		=>$filter_SZ
		);
	}
}

function setParam_best($db) {
	global $member_idx;
	$setting = getBest_setting($db);
	
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
	
	if ($product_type != null && $product_type != "ALL") {
		$tmp_where .= " AND (PRODUCT_TYPE = ?) ";
		
		array_push($param_bind,$product_type);
	}
	
	if ($min_product_price != null || $max_product_price != null) {
		if ($min_product_price != null && $max_product_price == null) {
			$tmp_where .= "AND (PRICE >= ?)";
			
			array_push($param_bind,$min_product_price);
		} else if ($min_product_price == null && $max_product_price != null) {
			$tmp_where .= "AND (PRICE <= ?)";
			
			array_push($param_bind,$max_product_price);
		} else if ($min_product_price != null && $max_product_price != null) {
			$tmp_where .= "AND (PRICE BETWEEN ? AND ?)";
			
			array_push($param_bind,$min_product_price);
			array_push($param_bind,$max_product_price);
		}
	}
	
	if ($min_discount != null || $max_discount != null) {
		if ($min_discount != null && $max_discount == null) {
			$tmp_where .= " AND (DISCOUNT >= ?) ";
			
			array_push($param_bind,$min_discount);
		} else if ($min_discount == null && $max_discount != null) {
			$tmp_where .= " AND (DISCOUNT <= ?) ";
			
			array_push($param_bind,$max_discount);
		} else if ($min_discount != null && $max_discount != null) {
			$tmp_where .= " AND (DISCOUNT BETWEEN ? AND ?) ";
			
			array_push($param_bind,$min_discount);
			array_push($param_bind,$max_discount);
		}
	}
	
	if ($min_sales_price != null || $max_sales_price != null) {
		if ($min_sales_price != null && $max_sales_price == null) {
			$tmp_where .= " AND (SALES_PRICE >= ?) ";
			
			array_push($param_bind,$min_sales_price);
		} else if ($min_sales_price == null && $max_sales_price != null) {
			$tmp_where .= " AND (SALES_PRICE <= ?) ";
			
			array_push($param_bind,$max_sales_price);
		} else if ($min_sales_price != null && $max_sales_price != null) {
			$tmp_where .= " AND (SALES_PRICE BETWEEN ? AND ?) ";
			
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
		$tmp_date = " DATE_FORMAT(PR.CREATE_DATE,'%Y-%m-%d') ";
		
		if ($min_create_date != null && $max_create_date == null) {
			$tmp_where .= " AND (".$tmp_date." >= ?) ";
			
			array_push($param_bind,$min_create_date);
		} else if ($min_create_date == null && $max_create_date != null) {
			$tmp_where .= " AND (".$tmp_date." <= ?) ";
			
			array_push($param_bind,$max_create_date);
		} else if ($min_create_date != null && $max_create_date != null) {
			$tmp_where .= " AND (".$tmp_date." BETWEEN ? AND ?) ";
			
			array_push($param_bind,$min_create_date);
			array_push($param_bind,$max_create_date);
		}
	}
	
	if ($min_update_date != null || $max_update_date != null) {
		$tmp_date = " DATE_FORMAT(PR.UPDATE_DATE,'%Y-%m-%d') ";
		
		if ($min_update_date != null && $max_update_date == null) {
			$tmp_where .= " AND (".$tmp_date." >= ?) ";
			
			array_push($param_bind,$min_update_date);
		} else if ($min_update_date == null && $max_update_date != null) {
			$tmp_where .= " AND (".$tmp_date." <= ?) ";
			
			array_push($param_bind,$max_update_date);
		} else if ($min_update_date != null && $max_update_date != null) {
			$tmp_where .= " AND (".$tmp_date." BETWEEN ? AND ?) ";
			
			array_push($param_bind,$min_update_date);
			array_push($param_bind,$max_update_date);
		}
	}
	
	$where .= $tmp_where;
	
	$param_best = array(
		'where'			=>$tmp_where,
		'param_bind'	=>$param_bind
	);

	return $param_best;
}

function getBest_setting($db) {
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
	
	$db->query($select_best_info_sql,array($_SERVER['HTTP_COUNTRY']));
	
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

?>