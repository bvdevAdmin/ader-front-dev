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

include_once $_CONFIG['PATH']['API'].'_legacy/common/check.php';

error_reporting(E_ALL^ E_WARNING); 

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$member_level = 0;
if (isset($_SESSION['LEVEL_IDX'])) {
	$member_level = $_SESSION['LEVEL_IDX'];
}

/* 상품 진열 페이지 - 진열 상품 리스트 조회 */
if (isset($country) && isset($page_idx)) {
	/* 1. 상품 진열 페이지 - 메뉴 정보 조회 */
	$menu_info = array();
	if (isset($menu_type) && isset($menu_idx)) {
		$menu_info = getMenuInfo($db,$country,$menu_type,$menu_idx);
	}
	
	/* 2. 상품 진열 페이지 - 필터 정보 조회 */
	$filter_info = getPRODUCT_filter($db,$country,$page_idx);
	
	/* 3. 상품 진열 페이지 - 상품 정보 조회 */
	$check_result = checkPAGE($db,$page_idx,$preview_flg);
	if ($check_result) {
		$check_result = checkListLevel($db,$member_idx,$page_idx);
		if ($check_result['result'] != true && $preview_flg == null) {
			$json_result['code'] = 402;
			$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0089', array());
			
			echo json_encode($json_result);
			exit;
		} else {
			/* 3-1. 상품 진열 페이지 - 필터 검색조건 설정 */
			$sql_filter = getSQL_filter($filter_param);
			
			/* 3-2. 상품 진열 페이지 - 주문 수량 검색조건 설정 */
			$sql_order	= getSQL_order($country,$order_param);
			
			$select_product_grid_sql = "
				SELECT
					PG.DISPLAY_NUM				AS DISPLAY_NUM,
					PG.TYPE						AS GRID_TYPE,
					PG.SIZE						AS GRID_SIZE,
					PG.BACKGROUND_COLOR			AS BACKGROUND_COLOR,
					PG.PRODUCT_IDX				AS PRODUCT_IDX,
					
					PR.PRODUCT_TYPE				AS PRODUCT_TYPE,
					PR.SET_TYPE					AS SET_TYPE,
					PR.PRODUCT_NAME				AS PRODUCT_NAME,
					PR.PRICE_".$country."		AS PRICE,
					PR.DISCOUNT_".$country."	AS DISCOUNT,
					PR.SALES_PRICE_".$country."	AS SALES_PRICE,
					PR.COLOR					AS COLOR,
					(
						SELECT
							IFNULL(
								COUNT(S_WL.IDX),0
							)
						FROM
							WHISH_LIST S_WL
						WHERE
							S_WL.COUNTRY = ? AND
							S_WL.MEMBER_IDX = ? AND
							S_WL.PRODUCT_IDX = PG.PRODUCT_IDX
					)							AS CNT_WISH,
					
					IFNULL(
						J_OP.ORDER_QTY,0
					)							AS ORDER_QTY
				FROM
					PRODUCT_GRID PG
					LEFT JOIN SHOP_PRODUCT PR ON
					PG.PRODUCT_IDX = PR.IDX
					LEFT JOIN ORDERSHEET_MST OM ON
					PR.ORDERSHEET_IDX = OM.IDX
					
					LEFT JOIN (
						SELECT
							S_OP.PRODUCT_IDX		AS PRODUCT_IDX,
							SUM(S_OP.PRODUCT_QTY)	AS ORDER_QTY
						FROM
							ORDER_PRODUCT S_OP
						WHERE
							S_OP.PRODUCT_TYPE NOT IN ('V','D') AND
							S_OP.ORDER_STATUS NOT REGEXP 'OC|OE|OR'
						GROUP BY
							S_OP.PRODUCT_IDX
					) AS J_OP ON
					PG.PRODUCT_IDX = J_OP.PRODUCT_IDX
				WHERE
					PG.PAGE_IDX = ? AND
					PG.TYPE = 'PRD' AND
					
					PG.PRODUCT_IDX > 0 AND
					PR.SALE_FLG = TRUE AND
					
					PG.DEL_FLG = FALSE AND
					PR.DEL_FLG = FALSE
					
					".$sql_filter."
				ORDER BY
					".$sql_order."
			";
			
			if ($last_idx > 0) {
				$select_product_grid_sql .= " LIMIT ".$last_idx.",12 ";
			} else {
				$select_product_grid_sql .= " LIMIT 0,12 ";
			}
			
			$db->query($select_product_grid_sql,array($country,$member_idx,$page_idx));
			
			foreach($db->fetch() as $data) {
				$grid_type		= $data['GRID_TYPE'];
				
				$product_idx	= $data['PRODUCT_IDX'];
				
				$whish_flg = false;
				if ($data['CNT_WISH'] > 0) {
					$whish_flg = true;
				}
				
				//$product_img	= getPRODUCT_img($db,$product_idx);
				
				//$product_color	= getProductColor($db,$product_idx);
				
				//$product_size	= getProductSize($db,$data['PRODUCT_TYPE'],$data['SET_TYPE'],$product_idx);
				
				$stock_status = null;
				$soldout_cnt = 0;
				$stock_close_cnt = 0;
				
				/*
				for ($i=0; $i<count($product_size); $i++) {
					$tmp_stock_status = $product_size[$i]['stock_status'];
					if ($tmp_stock_status == "STSO") {
						$soldout_cnt++;
					} else if ($tmp_stock_status == "STCL") {
						$stock_close_cnt++;
					}
				}
				
				if (count($product_size) == $soldout_cnt) {
					$stock_status = "STSO";
				} else if ($stock_close_cnt > 0) {
					$stock_status = "STCL";
				}
				*/
				
				$grid_info[] = array(
					'display_num'		=>$data['DISPLAY_NUM'],
					'grid_type'			=>$data['GRID_TYPE'],
					'grid_size'			=>$data['GRID_SIZE'],
					'background_color'	=>$data['BACKGROUND_COLOR'],
					'product_idx'		=>$data['PRODUCT_IDX'],
					
					'product_type'		=>$data['PRODUCT_TYPE'],
					'product_name'		=>$data['PRODUCT_NAME'],
					'price'				=>$data['PRICE'],
					'txt_price'			=>number_format($data['PRICE']),
					'discount'			=>$data['DISCOUNT'],
					'sales_price'		=>$data['SALES_PRICE'],
					'txt_sales_price'	=>number_format($data['SALES_PRICE']),
					'color'				=>$data['COLOR'],
					
					'product_img'		=>$product_img,
					'product_color'		=>$product_color,
					'product_size'		=>$product_size,
					
					'stock_status'		=>$stock_status,
					
					'whish_flg'			=>$whish_flg
				);
			}
		}
	}
	
	$json_result['data'] = array(
		'menu_info'		=>$menu_info,
		'filter_info'	=>$filter_info,
		'grid_info'		=>$grid_info
	);
} else {
	$json_result['code'] = 402;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0095', array());
	
	echo json_encode($json_result);
	exit;
}

/* 2. 상품 진열 페이지 - 필터 정보 조회 */
function getPRODUCT_filter($db,$country,$page_idx) {
	$select_filter_cl_sql = "
		SELECT
			GROUP_CONCAT(
				PR.FILTER_CL
			)		AS FILTER_CL_IDX,
			GROUP_CONCAT(
				PR.FILTER_SZ
			)		AS FILTER_SZ_IDX
		FROM
			SHOP_PRODUCT PR
		WHERE
			PR.IDX IN (
				SELECT
					S_PG.PRODUCT_IDX
				FROM
					PRODUCT_GRID S_PG
				WHERE
					S_PG.PAGE_IDX = ?
			) AND
			PR.DEL_FLG = FALSE
	";
	
	$db->query($select_filter_cl_sql,array($page_idx));
	
	$filter_cl = array();
	$filter_sz = array();
	
	foreach($db->fetch() as $data) {
		$filter_cl_idx = $data['FILTER_CL_IDX'];		
		if (!empty($filter_cl_idx)) {
			$tmp_arr = explode(",",$filter_cl_idx);
			$color_idx = array();
			
			for ($i=0; $i<count($tmp_arr); $i++) {
				if (!in_array($tmp_arr[$i],$color_idx)) {
					array_push($color_idx,$tmp_arr[$i]);
				}
			}
			
			if (count($color_idx) > 0) {
				$filter_cl = getFILTER_cl($db,$country,$color_idx);
			}
		}
		
		$filter_sz_idx = $data['FILTER_SZ_IDX'];
		if (!empty($filter_sz_idx)) {
			$tmp_arr = explode(",",$filter_sz_idx);
			$size_idx = array();
			
			for ($i=0; $i<count($tmp_arr); $i++) {
				if (!in_array($tmp_arr[$i],$size_idx)) {
					array_push($size_idx,$tmp_arr[$i]);
				}
			}
			
			if (count($size_idx) > 0) {
				$filter_sz	= getFILTER_sz($db,$country,$size_idx);
			}
		}
	}
	
	$filter_ft	= getFILTER_ft($db,$page_idx);
	$filter_ln	= getFILTER_ln($db,$page_idx);
	$filter_gp	= getFILTER_gp($db,$page_idx);
	
	$filter_info = array(
		'filter_cl'		=>$filter_cl,
		'filter_sz'		=>$filter_sz,
		
		'filter_ft'		=>$filter_ft,
		'filter_ln'		=>$filter_ln,
		'filter_gp'		=>$filter_gp
	);
	
	return $filter_info;
}

function getFILTER_cl($db,$country,$color_idx) {
	$filter_cl = array();
	
	$select_filter_color_sql = "
		SELECT
			PF.IDX						AS FILTER_IDX,
			PF.FILTER_NAME_".$country."	AS FILTER_NAME,
			PF.RGB_COLOR				AS RGB_COLOR
		FROM
			PRODUCT_FILTER PF
		WHERE
			PF.IDX IN (?) AND
			PF.FILTER_TYPE = 'CL' AND
			PF.DEL_FLG = FALSE
	";
	
	$db->query($select_filter_color_sql,array(implode(",",$color_idx)));
	
	foreach($db->fetch() as $data) {
		$filter_cl[] = array(
			'filter_idx'		=>$data['FILTER_IDX'],
			'filter_name'		=>$data['FILTER_NAME'],
			'rgb_color'			=>$data['RGB_COLOR'],
		);
	}
	
	return $filter_cl;
}

function getFILTER_sz($db,$country,$size_idx) {
	$filter_sz = array();
	
	$filter_sz_up = array();
	$filter_sz_lw = array();
	$filter_sz_ht = array();
	$filter_sz_sh = array();
	$filter_sz_jw = array();
	$filter_sz_ac = array();
	$filter_sz_ta = array();
	
	$select_filter_size_sql = "
		SELECT
			PF.IDX						AS FILTER_IDX,
			PF.FILTER_NAME_".$country."	AS FILTER_NAME,
			PF.SIZE_TYPE				AS SIZE_TYPE
		FROM
			PRODUCT_FILTER PF
		WHERE
			PF.IDX IN (?) AND
			PF.FILTER_TYPE = 'SZ' AND
			PF.DEL_FLG = FALSE
		ORDER BY
			PF.SIZE_TYPE,FILTER_NAME ASC
	";
	
	$db->query($select_filter_size_sql,array(implode(",",$size_idx)));
	
	$temp_size = array();
	
	foreach($db->fetch() as $data) {
		$filter_name = $data['FILTER_NAME'];
		$size_sort = substr($filter_name,0,1);
		if ($size_sort != "O" && $size_sort != "A") {
			$size_sort = "E";
		}
		
		$size_type = $data['SIZE_TYPE'];
		
		switch ($size_type) {
			case "UP" :
				$filter_sz_up[] = array(
					'size_sort'			=>$size_sort,
					'filter_idx'		=>$data['FILTER_IDX'],
					'filter_name'		=>$data['FILTER_NAME']
				);
				break;
			
			case "LW" :
				$filter_sz_lw[] = array(
					'size_sort'			=>$size_sort,
					'filter_idx'		=>$data['FILTER_IDX'],
					'filter_name'		=>$data['FILTER_NAME']
				);
				break;
			
			case "HT" :
				$filter_sz_ht[] = array(
					'size_sort'			=>$size_sort,
					'filter_idx'		=>$data['FILTER_IDX'],
					'filter_name'		=>$data['FILTER_NAME']
				);
				break;
			
			case "SH" :
				$filter_sz_sh[] = array(
					'size_sort'			=>$size_sort,
					'filter_idx'		=>$data['FILTER_IDX'],
					'filter_name'		=>$data['FILTER_NAME']
				);
				break;
			
			case "JW" :
				$filter_sz_jw[] = array(
					'size_sort'			=>$size_sort,
					'filter_idx'		=>$data['FILTER_IDX'],
					'filter_name'		=>$data['FILTER_NAME']
				);
				break;
			
			case "AC" :
				$filter_sz_ac[] = array(
					'size_sort'			=>$size_sort,
					'filter_idx'		=>$data['FILTER_IDX'],
					'filter_name'		=>$data['FILTER_NAME']
				);
				break;
			
			case "TA" :
				$filter_sz_ta[] = array(
					'size_sort'			=>$size_sort,
					'filter_idx'		=>$data['FILTER_IDX'],
					'filter_name'		=>$data['FILTER_NAME']
				);
				break;
		}
	}
	
	$filter_sz[] = array(
		'filter_sz_up'		=>$filter_sz_up,
		'filter_sz_lw'		=>$filter_sz_lw,
		'filter_sz_ht'		=>$filter_sz_ht,
		'filter_sz_sh'		=>$filter_sz_sh,
		'filter_sz_jw'		=>$filter_sz_jw,
		'filter_sz_ac'		=>$filter_sz_ac,
		'filter_sz_ta'		=>$filter_sz_ta
	);
	
	return $filter_sz;
}

function getFILTER_ft($db,$page_idx) {
	$filter_ft = array();
	
	$select_filter_fit_sql = "
		SELECT
			DISTINCT OM.FIT			AS FIT
		FROM
			ORDERSHEET_MST OM
			LEFT JOIN SHOP_PRODUCT PR ON
			OM.IDX = PR.ORDERSHEET_IDX
			LEFT JOIN PRODUCT_GRID PG ON
			PR.IDX = PG.PRODUCT_IDX
		WHERE
			PG.PAGE_IDX = ? AND
			PR.FILTER_FT = TRUE AND
			PR.DEL_FLG = FALSE
	";
	
	$db->query($select_filter_fit_sql,array($page_idx));
	
	foreach($db->fetch() as $fit_data) {
		$filter_ft[] = array(
			'fit'	=>$fit_data['FIT']
		);
	}
	
	return $filter_ft;
}

function getFILTER_ln($db,$page_idx) {
	$filter_ln = array();
	
	$select_filter_line_sql = "
		SELECT
			LI.IDX					AS LINE_IDX,
			LI.LINE_NAME			AS LINE_NAME
		FROM
			ORDERSHEET_MST OM
			LEFT JOIN LINE_INFO LI ON
			OM.LINE_IDX = LI.IDX
			LEFT JOIN SHOP_PRODUCT PR ON
			OM.IDX = PR.ORDERSHEET_IDX
			LEFT JOIN PRODUCT_GRID PG ON
			PR.IDX = PG.PRODUCT_IDX
		WHERE
			PG.PAGE_IDX = ? AND
			PR.FILTER_LN = TRUE AND
			PR.DEL_FLG = FALSE
		GROUP BY
			LI.LINE_NAME
	";
	
	$db->query($select_filter_line_sql,array($page_idx));
	
	foreach($db->fetch() as $data) {
		$filter_ln[] = array(
			'line_idx'		=>$data['LINE_IDX'],
			'line_name'		=>$data['LINE_NAME']
		);
	}
	
	return $filter_ln;
}

function getFILTER_gp($db,$page_idx) {
	$filter_gp = array();
	
	$select_filter_graphic_sql = "
		SELECT
			DISTINCT OM.GRAPHIC		AS GRAPHIC
		FROM
			ORDERSHEET_MST OM
			LEFT JOIN SHOP_PRODUCT PR ON
			OM.IDX = PR.ORDERSHEET_IDX
			LEFT JOIN PRODUCT_GRID PG ON
			PR.IDX = PG.PRODUCT_IDX
		WHERE
			PG.PAGE_IDX = ? AND
			PR.FILTER_GP = TRUE AND
			PR.DEL_FLG = FALSE
	";
	
	$db->query($select_filter_graphic_sql,array($page_idx));
	
	foreach($db->fetch() as $data) {
		$filter_gp[] = array(
			'graphic'	=>$data['GRAPHIC']
		);
	}
	
	return $filter_gp;
}

function checkPAGE($db,$page_idx,$preview_flg) {
	$check_result = false;
	
	$where = "
		IDX = ".$page_idx."
	";
	if ($preview_flg == null) {
		$where .= "
			AND (
				(NOW() BETWEEN DISPLAY_START_DATE AND DISPLAY_END_DATE) AND
				DISPLAY_FLG = TRUE
			)
		";
	}
	
	$cnt_page = $db->count("PAGE_PRODUCT",$where);
	if ($cnt_page > 0) {
		$check_result = true;
	}
	
	return $check_result;
}

/* 3-1. 상품 진열 페이지 - 필터 검색조건 설정 */
function getSQL_filter($filter_param) {
	$sql_filter = "";
	
	if ($filter_param != null) {
		/* PARAM::컬러 필터 */
		$filter_cl = $filter_param['filter_cl'];
		if ($filter_cl != null) {
			$sql_filter .= "
				AND (
					PR.FILTER_CL REGEXP '".implode("|",$filter_cl)."'
				)
			";
		}
		
		/* PARAM::핏 필터 */
		$filter_ft = $filter_param['filter_ft'];
		if ($filter_ft != null) {
			$sql_filter .= "
				AND (
					OM.FIT REGEXP '".implode("|",$filter_ft)."'
				)
			";
		}
		
		/* PARAM::그래픽 필터 */
		$filter_gp = $filter_param['filter_gp'];
		if ($filter_gp != null) {
			$sql_filter .= "
				AND (
					OM.GRAPHIC REGEXP '".implode("|",$filter_gp)."'
				)
			";
		}
		
		/* PARAM::라인 필터 */
		$filter_ln = $filter_param['filter_ln'];
		if ($filter_ln != null) {
			$sql_filter .= "
				AND (
					OM.LINE_IDX REGEXP '".implode("|",$filter_ln)."'
				)
			";
		}
		
		/* PARAM::사이즈 필터 */
		$filter_sz = $filter_param['filter_sz'];
		if ($filter_sz != null) {
			$sql_filter .= "
				AND (
					PR.FILTER_SZ REGEXP '".implode("|",$filter_sz)."'
				)
			";
		}
	}
	
	return $sql_filter;
}

/* 3-2. 상품 진열 페이지 - 주문 수량 검색조건 설정 */
function getSQL_order($country,$order_param) {
	$sql_order	= " PG.DISPLAY_NUM ASC ";
	
	if (isset($order_param) != null) {
		switch ($order_param) {
			case "POP" :
				$sql_order = "
					ORDER_QTY DESC
				";
				break;
			
			case "NEW" :
				$sql_order = "
					PR.CREATE_DATE DESC
				";
				break;
			
			case "MIN" :
				$sql_order = "
					PR.SALES_PRICE_".$country." ASC
				";
				break;
				
			case "MAX" :
				$sql_order = "
					PR.SALES_PRICE_".$country." DESC
				";
				break;
		}
	}
	
	return $sql_order;
}

function getPRODUCT_img($db,$product_idx) {
	$product_img = array();
	
	$img_type = "";
	$p_img_type = 'P';
	$o_img_type = 'O';
	
	$cnt_thmb = $db->count("PRODUCT_IMG","PRODUCT_IDX = ".$product_idx." AND IMG_TYPE LIKE 'T%'");
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

function getPRODUCT_color($db,$style_code) {
	$product_color = array();
	
	return $product_color;
}
