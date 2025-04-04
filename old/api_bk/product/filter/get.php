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

$member_level = 0;
if (isset($_SESSION['MEMBER_LEVEL'])) {
	$member_level = $_SESSION['MEMBER_LEVEL'];
}

$page_idx = 0;
if (isset($_POST['page_idx'])) {
	$page_idx = $_POST['page_idx'];
}

$filter_param = null;
if (isset($_POST['filter_param'])) {
	$filter_param = $_POST['filter_param'];
}

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
				OM.LINE_IDX REGEXP '".implode("|",$filter_gp)."'
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

if ($page_idx > 0) {
	$member_level_sql = "";
	if ($member_level > 0) {
		$member_level_sql = " AND (DISPLAY_MEMBER_LEVEL = 'ALL' OR DISPLAY_MEMBER_LEVEL REGEXP '".$member_level."') ";
	} else {
		$member_level_sql = " AND DISPLAY_MEMBER_LEVEL = 'ALL' ";
	}
	
	$page_cnt = $db->count("PAGE_PRODUCT","IDX = ".$page_idx." AND DISPLAY_FLG = TRUE ".$member_level_sql);
	
	if ($page_cnt > 0) {
		$product_table = "
			PRODUCT_GRID PG
			LEFT JOIN SHOP_PRODUCT PR ON
			PG.PRODUCT_IDX = PR.IDX
			LEFT JOIN ORDERSHEET_MST OM ON
			PR.ORDERSHEET_IDX = OM.IDX
		";
		
		$product_where = "
			PG.PAGE_IDX = ".$page_idx." AND
			PG.DEL_FLG = FALSE
			".$grid_filter_sql."
		";
		
		$product_cnt = $db->count($product_table,$product_where);
		
		$json_result['data'] = array(
			'product_cnt'	=>$product_cnt,
		);
	} else {
		$json_result['code'] = 301;
		$json_result['msg'] = "부정확한 페이지는 존재하지 않습니다.";
	}
}
?>