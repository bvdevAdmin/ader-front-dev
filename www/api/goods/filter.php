<?php
/*
 +=============================================================================
 | 
 | 상품 필터
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2024.11.16
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

error_reporting(E_ALL^ E_WARNING);

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

if (isset($_SERVER['HTTP_COUNTRY']) && isset($filter_type) && isset($filter_param)) {
	$filter_PR	= array();
	$filter_PG	= array();

	if ($filter_type == "best") {
		$filter_param = setParam_best($db);
	}
	
	$filter_idx = getFilter_idx($db,$filter_type,$filter_param);
	if (count($filter_idx) > 0) {
		/* 상품 색상/사이즈 필터 정보 조회 */
		$filter_PR	= getFilter_product($db,$filter_idx);
	}
	
	$filter_PG		= getFilter_page($db,$filter_type,$filter_param);
	
	$json_result['data'] = array(
		'filter_cl'		=>$filter_PR['filter_CL'],
		'filter_sz'		=>$filter_PR['filter_SZ'],
		
		'filter_ft'		=>$filter_PG['filter_FT'],
		'filter_ln'		=>$filter_PG['filter_LN'],
		'filter_gp'		=>$filter_PG['filter_GP']
	);
}

/* 현재 표시 할 페이지의 사이즈/컬러 필터 IDX값 조회 */
function getFilter_idx($db,$filter_type,$filter_param) {
	$filter_idx = array();
	
	$table = "";
	
	$where = "";
	
	$param_bind = array();
	
	switch ($filter_type) {
		case "page" :
			$table = "
				SHOP_PRODUCT PR
				
				LEFT JOIN PRODUCT_GRID PG ON
				PR.IDX = PG.PRODUCT_IDX
			";
			
			$where .= " AND (PG.PAGE_IDX = ?) ";
			
			array_push($param_bind,$filter_param);
			
			break;
		
		case "search" :
			$table = "
				SHOP_PRODUCT PR
				
				LEFT JOIN SHOP_OPTION OO ON
				PR.IDX = OO.PRODUCT_IDX
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
				)
			";
			
			$param_bind = array(
				$filter_param,
				$filter_param,
				$filter_param,
				$filter_param,
				$filter_param
			);
			
			break;
		
		case "standby" :
			$table = "SHOP_PRODUCT PR";
			
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
			
			array_push($param_bind,$filter_param);
			
			break;
		
		case "best" :
			$table = "
				SHOP_PRODUCT PR
				
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
			
			$where		= $filter_param['where'];
			$param_bind	= $filter_param['param_bind'];
		
			break;
		case "recently" : 
			$table = " SHOP_PRODUCT PR ";

			if (isset($filter_param) && strlen($filter_param) > 0) {
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

				$param_bind = array($filter_param,RECENTLY_EXPIRE_DATE);
			}

			break;
	}
	
	$select_filter_product_sql = "
		SELECT
			GROUP_CONCAT(
				PR.FILTER_CL
			)		AS FILTER_CL,
			GROUP_CONCAT(
				PR.FILTER_SZ
			)		AS FILTER_SZ
		FROM
			".$table."
		WHERE
			PR.SALE_FLG = TRUE AND
			PR.DEL_FLG = FALSE
			".$where."
	";
	
	$db->query($select_filter_product_sql,$param_bind);
	
	foreach($db->fetch() as $data) {
		$filter_CL = array();
		if ($data['FILTER_CL'] != null && strlen($data['FILTER_CL']) > 0) {
			$filter_CL = explode(",",$data['FILTER_CL']);
		}
		
		$filter_SZ = array();
		if ($data['FILTER_SZ'] != null && strlen($data['FILTER_SZ']) > 0) {
			$filter_SZ = explode(",",$data['FILTER_SZ']);
		}
		
		$filter_idx = array_merge($filter_CL,$filter_SZ);
		if (count($filter_idx) > 0) {
			$filter_idx = array_unique($filter_idx);
		}
	}
	
	return $filter_idx;
}

/* 상품 색상/사이즈 필터 정보 조회 */
function getFilter_product($db,$filter_idx) {
	/* 상품 색상 필터 */
	$filter_CL = array();
	
	/* 상품 사이즈 필터 */
	$filter_SZ = array();
	
	$size_UP = array();	//필터 사이즈 상의
	$size_LW = array();	//필터 사이즈 하의
	$size_HT = array();	//필터 사이즈 모자
	$size_SH = array();	//필터 사이즈 슈즈
	$size_JW = array();	//필터 사이즈 쥬얼리
	$size_AC = array();	//필터 사이즈 아세서리
	$size_TA = array();	//필터 사이즈 테크 악세서리
	
	if (count($filter_idx) > 0) {
		$select_product_filter_sql = "
			SELECT
				PF.IDX					AS FILTER_IDX,
				PF.FILTER_TYPE			AS FILTER_TYPE,
				PF.FILTER_NAME_KR		AS FILTER_NAME_KR,
				PF.FILTER_NAME_EN		AS FILTER_NAME_EN,
				PF.RGB_COLOR			AS RGB_COLOR,
				PF.SIZE_TYPE			AS SIZE_TYPE
			FROM
				PRODUCT_FILTER PF
			WHERE
				PF.IDX IN (".implode(',',array_fill(0,count($filter_idx),'?')).") AND
				PF.DEL_FLG = FALSE
		";
		
		$db->query($select_product_filter_sql,$filter_idx);
		
		foreach($db->fetch() as $data) {
			if ($data['FILTER_TYPE'] == "CL") {
				/* 상품 색상 필터 */
				$filter_CL[] = array(
					'filter_idx'		=>$data['FILTER_IDX'],
					'filter_name'		=>$data['FILTER_NAME_'.$_SERVER['HTTP_COUNTRY']],
					'rgb_color'			=>$data['RGB_COLOR']
				);
			} else if ($data['FILTER_TYPE'] == "SZ") {
				/* 상품 사이즈 필터 */
				$size_sort = substr($data['FILTER_NAME_'.$_SERVER['HTTP_COUNTRY']],0,1);
				if (!in_array($size_sort,array("O","A","S","M","L","X"))) {
					$size_sort = "E";
				}
				
				${'size_'.$data['SIZE_TYPE']}[] = array(
					'size_sort'			=>$size_sort,
					'filter_idx'		=>$data['FILTER_IDX'],
					'filter_name'		=>$data['FILTER_NAME_'.$_SERVER['HTTP_COUNTRY']]
				);
			}
		}
	}
	
	$filter_SZ = array(
		'filter_sz_up'		=>$size_UP,
		'filter_sz_lw'		=>$size_LW,
		'filter_sz_ht'		=>$size_HT,
		'filter_sz_sh'		=>$size_SH,
		'filter_sz_jw'		=>$size_JW,
		'filter_sz_ac'		=>$size_AC,
		'filter_sz_ta'		=>$size_TA
	);
	
	$filter_product = array(
		'filter_CL'		=>$filter_CL,
		'filter_SZ'		=>$filter_SZ
	);
	
	return $filter_product;
}

/* 상품 핏/그래픽/라인 필터 정보 조회 */
function getFilter_page($db,$filter_type,$filter_param) {
	$filter_page = array();
	
	$filter_FT	= array();	//필터 핏
	$filter_GP	= array();	//필터 그래픽
	$filter_LN	= array();	//필터 라인
	
	$line_info		= getLine($db);
	
	$table = "";
	
	$where = "";
	
	$param_bind = array();
	
	switch ($filter_type) {
		case "page" :
			$table = "
				SHOP_PRODUCT PR
				
				LEFT JOIN PRODUCT_GRID PG ON
				PR.IDX = PG.PRODUCT_IDX
			";
			
			$where .= " AND (PG.PAGE_IDX = ?) ";
			
			array_push($param_bind,$filter_param);
			
			break;
		
		case "search" :
			$table = "
				SHOP_PRODUCT PR
				
				LEFT JOIN SHOP_OPTION OO ON
				PR.IDX = OO.PRODUCT_IDX
			";
			
			$where .= "
				AND (
					PR.COLOR REGEXP				? OR
					
					PR.PRODUCT_NAME REGEXP		? OR
					OO.OPTION_NAME REGEXP		? OR
					
					PR.PRODUCT_KEYWORD REGEXP	? OR
					PR.PRODUCT_TAG REGEXP		?
				)
			";
			
			$param_bind = array(
				$filter_param,$filter_param,$filter_param,$filter_param,$filter_param
			);
			
			break;
		
		case "standby" :
			$table = "SHOP_PRODUCT PR";
			
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
			
			array_push($param_bind,$filter_param);
			
			break;
		
		case "best" :
			$table = "
				SHOP_PRODUCT PR
				
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
			
			$where		= $filter_param['where'];
			$param_bind	= $filter_param['param_bind'];
			
			break;
		
		case "recently" :
			$table = " SHOP_PRODUCT PR ";

			if (isset($filter_param) && strlen($filter_param) > 0) {
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

				$param_bind = array($filter_param,RECENTLY_EXPIRE_DATE);
			}

			break;
	}
	
	$select_filter_page_sql = "
		SELECT
			PR.FIT				AS FIT,
			PR.GRAPHIC			AS GRAPHIC,
			PR.LINE_IDX			AS LINE_IDX
		FROM
			".$table."
		WHERE
			PR.SALE_FLG = TRUE AND
			PR.DEL_FLG = FALSE
			".$where."
	";
	
	if (count($param_bind) > 0) {
		$db->query($select_filter_page_sql,$param_bind);
	} else {
		$db->query($select_filter_page_sql);
	}
	
	
	$tmp_FT		= array();
	$tmp_GP		= array();
	$tmp_LN		= array();
	
	foreach($db->fetch() as $data) {
		array_push($tmp_FT,$data['FIT']);
		
		array_push($tmp_GP,$data['GRAPHIC']);
		
		array_push($tmp_LN,$data['LINE_IDX']);
	}
	
	if (count($tmp_FT) > 0) {
		$tmp_FT = array_unique($tmp_FT);
		foreach($tmp_FT as $ft) {
			$filter_FT[] = array(
				'fit'		=>$ft
			);
		}
	}
	
	if (count($tmp_GP) > 0) {
		$tmp_GP = array_unique($tmp_GP);
		foreach($tmp_GP as $gp) {
			$filter_GP[] = array(
				'graphic'		=>$gp
			);
		}
	}
	
	if (count($tmp_LN) > 0) {
		$tmp_LN = array_unique($tmp_LN);
		foreach($tmp_LN as $line) {
			$filter_LN[] = array(
				'line_idx'		=>$line,
				'line_name'		=>$line_info[$line]
			);
		}
	}
	
	$filter_page = array(
		'filter_FT'		=>$filter_FT,
		'filter_GP'		=>$filter_GP,
		'filter_LN'		=>$filter_LN
	);

	return $filter_page;
}

function setSQL_filter($param) {
	$where_filter = "";
	$bind_filter = array();
	
	if ($param != null) {
		/* PARAM::컬러 필터 */
		if (isset($param['filter_cl'])) {
			$param_CL = $param['filter_cl'];
			if ($param_CL != null && count($param_CL) > 0) {
				$tmp_CL = array();
				$bind_CL = array();
				
				foreach($param_CL as $cl) {
					array_push($tmp_CL," FIND_IN_SET(?,FILTER_CL) ");
					array_push($bind_CL,$cl);
				}
				
				$where_filter .= "
					AND (
						".implode(" OR ",$tmp_CL)."
					)
				";
				
				$bind_filter = array_merge($bind_filter,$bind_CL);
			}
		}
		
		/* PARAM::사이즈 필터 */
		if (isset($param['filter_sz'])) {
			$param_SZ = $param['filter_sz'];
			if ($param_SZ != null && count($param_SZ) > 0) {
				$tmp_SZ = array();
				$bind_SZ = array();
				
				foreach($param_SZ as $sz) {
					array_push($tmp_SZ," FIND_IN_SET(?,FILTER_SZ) ");
					array_push($bind_SZ,$sz);
				}
				
				$where_filter .= " AND (".implode(" OR ",$tmp_SZ).") ";
				
				$bind_filter = array_merge($bind_filter,$bind_SZ);
			}
		}
		
		/* PARAM::핏 필터 */
		if (isset($param['filter_ft'])) {
			$param_FT = $param['filter_ft'];
			$where_filter .= " AND (PR.FIT IN (".implode(',',array_fill(0,count($param_FT),'?')).")) ";
			
			$bind_filter = array_merge($bind_filter,$param_FT);
		}
		
		/* PARAM::라인 필터 */
		if (isset($param['filter_ln'])) {
			$param_LN = $param['filter_ln'];
			$where_filter .= " AND (PR.LINE_IDX IN (".implode(',',array_fill(0,count($param_LN),'?')).")) ";
			
			$bind_filter = array_merge($bind_filter,$param_LN);
		}
		
		/* PARAM::그래픽 필터 */
		if (isset($param['filter_gp'])) {
			$param_GP = $param['filter_gp'];
			$where_filter .= " AND (PR.GRAPHIC IN (".implode(',',array_fill(0,count($param_GP),'?')).")) ";
			
			$bind_filter = array_merge($bind_filter,$param_GP);
		}
	}
	
	$sql_filter = array(
		'where_filter'	=>$where_filter,
		'bind_filter'	=>$bind_filter
	);
	
	return $sql_filter;
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
		$tmp_date = " DATE_FORMAT(CREATE_DATE,'%Y-%m-%d') ";
		
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
		$tmp_date = " DATE_FORMAT(UPDATE_DATE,'%Y-%m-%d') ";
		
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

function getLine($db) {
	$line_info = array();
	
	$select_line_info_sql = "
		SELECT
			LI.IDX				AS LINE_IDX,
			LI.LINE_NAME		AS LINE_NAME
		FROM
			LINE_INFO LI
		WHERE
			LI.DEL_FLG = FALSE
	";
	
	$db->query($select_line_info_sql);
	
	foreach($db->fetch() as $data) {
		$line_info[$data['LINE_IDX']] = $data['LINE_NAME'];
	}
	
	return $line_info;
}

?>