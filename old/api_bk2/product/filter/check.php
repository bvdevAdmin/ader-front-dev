<?php
/*
 +=============================================================================
 | 
 | 상품 리스트 - 필터 적용 상품 카운트 취득
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2023.01.26
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

error_reporting(E_ALL^ E_WARNING); 

$page_idx = 0;
if (isset($_POST['page_idx'])) {
	$page_idx = $_POST['page_idx'];
}

$filter_param = null;
if (isset($_POST['filter_param'])) {
	$filter_param = $_POST['filter_param'];
}

if ($page_idx > 0) {
	$grid_filter_sql = "";
	if ($filter_param != null) {
		$filter_cl = $filter_param['filter_cl'];
		if ($filter_cl != null) {
			$grid_filter_sql .= "
				AND (
					PR.FILTER_CL REGEXP '".implode("|",$filter_cl)."'
				)
			";
		}
		
		$filter_ft = $filter_param['filter_ft'];
		if ($filter_ft != null) {
			$grid_filter_sql .= "
				AND (
					OM.FIT REGEXP '".implode("|",$filter_ft)."'
				)
			";
		}
		
		$filter_gp = $filter_param['filter_gp'];
		if ($filter_gp != null) {
			$grid_filter_sql .= "
				AND (
					OM.GRAPHIC REGEXP '".implode("|",$filter_gp)."'
				)
			";
		}
		
		$filter_ln = $filter_param['filter_ln'];
		if ($filter_ln != null) {
			$grid_filter_sql .= "
				AND (
					OM.LINE_IDX REGEXP '".implode("|",$filter_ln)."'
				)
			";
		}
		
		$filter_sz = $filter_param['filter_sz'];
		if ($filter_sz != null) {
			$grid_filter_sql .= "
				AND (
					PR.FILTER_SZ REGEXP '".implode("|",$filter_sz)."'
				)
			";
		}
	}

	$grid_table = "
		PRODUCT_GRID PG
		LEFT JOIN SHOP_PRODUCT PR ON
		PG.PRODUCT_IDX = PR.IDX
		LEFT JOIN ORDERSHEET_MST OM ON
		PR.ORDERSHEET_IDX = OM.IDX
	";
	
	$where = "
		PG.PAGE_IDX = ".$page_idx." AND
		PG.PRODUCT_IDX > 0 AND PR.SALE_FLG = TRUE AND
		PG.DEL_FLG = FALSE AND
		PR.DEL_FLG = FALSE
		".$grid_filter_sql."
	";
	
	$json_result['data'] = number_format($db->count($grid_table,$where));
}

?>