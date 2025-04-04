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
if (isset($_SESSION['country'])) {
	$country = $_SESSION['COUNTRY'];
} else if (isset($_POST['country'])) {
	$country = $_POST['country'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$member_level = 0;
if (isset($_SESSION['LEVEL_IDX'])) {
	$member_level = $_SESSION['LEVEL_IDX'];
}

/* 상품 진열 페이지 - 진열 상품 리스트 조회 */
if ($country != null && $page_idx != null) {
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
					PR.STYLE_CODE				AS STYLE_CODE,
					PR.PRODUCT_NAME				AS PRODUCT_NAME,
					PR.PRICE_".$country."		AS PRICE,
					PR.DISCOUNT_".$country."	AS DISCOUNT,
					PR.SALES_PRICE_".$country."	AS SALES_PRICE,
					PR.COLOR					AS COLOR,
					
					J_PI_TP.IMG_LOCATION		AS IMG_LOCATION_TP,
					J_PI_P.IMG_LOCATION			AS IMG_LOCATION_P,
					J_PI_TO.IMG_LOCATION		AS IMG_LOCATION_TO,
					J_PI_O.IMG_LOCATION			AS IMT_LOCATION_O,
					
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
							S_PI.PRODUCT_IDX	AS PRODUCT_IDX,
							S_PI.IMG_TYPE		AS IMG_TYPE,
							S_PI.IMG_LOCATION	AS IMG_LOCATION
						FROM
							PRODUCT_IMG S_PI
						WHERE
							S_PI.IMG_SIZE = 'M' AND
							S_PI.IMG_TYPE = 'TP'
						GROUP BY
							S_PI.PRODUCT_IDX
						ORDER BY
							S_PI.IDX ASC
					) J_PI_TP ON
					PG.PRODUCT_IDX = J_PI_TP.PRODUCT_IDX
					
					LEFT JOIN (
						SELECT
							S_PI.PRODUCT_IDX	AS PRODUCT_IDX,
							S_PI.IMG_TYPE		AS IMG_TYPE,
							S_PI.IMG_LOCATION	AS IMG_LOCATION
						FROM
							PRODUCT_IMG S_PI
						WHERE
							S_PI.IMG_SIZE = 'M' AND
							S_PI.IMG_TYPE = 'P'
						GROUP BY
							S_PI.PRODUCT_IDX
						ORDER BY
							S_PI.IDX ASC
					) J_PI_P ON
					PG.PRODUCT_IDX = J_PI_P.PRODUCT_IDX
					
					LEFT JOIN (
						SELECT
							S_PI.PRODUCT_IDX	AS PRODUCT_IDX,
							S_PI.IMG_TYPE		AS IMG_TYPE,
							S_PI.IMG_LOCATION	AS IMG_LOCATION
						FROM
							PRODUCT_IMG S_PI
						WHERE
							S_PI.IMG_SIZE = 'M' AND
							S_PI.IMG_TYPE = 'TO'
						GROUP BY
							S_PI.PRODUCT_IDX
						ORDER BY
							S_PI.IDX ASC
					) J_PI_TO ON
					PG.PRODUCT_IDX = J_PI_TO.PRODUCT_IDX
					
					LEFT JOIN (
						SELECT
							S_PI.PRODUCT_IDX	AS PRODUCT_IDX,
							S_PI.IMG_TYPE		AS IMG_TYPE,
							S_PI.IMG_LOCATION	AS IMG_LOCATION
						FROM
							PRODUCT_IMG S_PI
						WHERE
							S_PI.IMG_SIZE = 'M' AND
							S_PI.IMG_TYPE = 'O'
						GROUP BY
							S_PI.PRODUCT_IDX
						ORDER BY
							S_PI.IDX ASC
					) J_PI_O ON
					PG.PRODUCT_IDX = J_PI_O.PRODUCT_IDX
					
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
			
			$param_idx	= array();
			$param_code	= array();
			$grid_info = null;
			
			foreach($db->fetch() as $data) {
				array_push($param_idx,$data['PRODUCT_IDX']);
				array_push($param_code,"'".$data['STYLE_CODE']."'");
				
				$grid_type		= $data['GRID_TYPE'];
				
				$product_idx	= $data['PRODUCT_IDX'];
				
				$whish_flg = false;
				if ($data['CNT_WISH'] > 0) {
					$whish_flg = true;
				}
				
				$param_img = array(
					'img_location_P'		=>$data['IMG_LOCATION_P'],
					'img_location_TP'		=>$data['IMG_LOCATION_TP'],
					'img_location_O'		=>$data['IMG_LOCATION_O'],
					'img_location_TO'		=>$data['IMG_LOCATION_TO']
				);
				
				$product_img	= getPRODUCT_img($param_img);
				
				$grid_info[] = array(
					'display_num'		=>$data['DISPLAY_NUM'],
					'grid_type'			=>$data['GRID_TYPE'],
					'grid_size'			=>$data['GRID_SIZE'],
					'background_color'	=>$data['BACKGROUND_COLOR'],
					'product_idx'		=>$data['PRODUCT_IDX'],
					'style_code'		=>$data['STYLE_CODE'],
					
					'product_type'		=>$data['PRODUCT_TYPE'],
					'product_name'		=>$data['PRODUCT_NAME'],
					'price'				=>$data['PRICE'],
					'txt_price'			=>number_format($data['PRICE']),
					'discount'			=>$data['DISCOUNT'],
					'sales_price'		=>$data['SALES_PRICE'],
					'txt_sales_price'	=>number_format($data['SALES_PRICE']),
					'color'				=>$data['COLOR'],
					
					'product_img'		=>$product_img,
					
					'whish_flg'			=>$whish_flg
				);
			}
			
			if ($grid_info != null) {
				$product_color	= getPRODUCT_color($db,$param_code,$member_idx);
				
				$product_size	= getPRODUCT_size($db,$param_idx,$member_idx);
				
				for ($i=0; $i<count($grid_info); $i++) {
					$grid = $grid_info[$i];
					
					$tmp_style_code		= $grid['style_code'];
					$tmp_product_idx	= $grid['product_idx'];
					
					$stock_status = null;
					$soldout_cnt = 0;
					$stock_close_cnt = 0;
					
					$tmp_product_size = $product_size[$grid['product_idx']];
					if ($tmp_product_size != null && is_array($tmp_product_size)) {
						$stock_status = null;
						$soldout_cnt = 0;
						$stock_close_cnt = 0;
						
						for ($j=0; $j<count($tmp_product_size); $j++) {
							$tmp_stock_status = $tmp_product_size[$j]['stock_status'];
							if ($tmp_stock_status == "STSO") {
								$soldout_cnt++;
							} else if ($tmp_stock_status == "STCL") {
								$stock_close_cnt++;
							}
						}
						
						if (count($tmp_product_size) == $soldout_cnt) {
							$stock_status = "STSO";
						} else if ($stock_close_cnt > 0) {
							$stock_status = "STCL";
						}
					}
					
					$grid_info[$i]['product_color']	= $product_color[$tmp_style_code];
					$grid_info[$i]['product_size']	= $tmp_product_size;
					$grid_info[$i]['stock_status']	= $stock_status;
				}
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
			PR.FILTER_CL	AS FILTER_CL_IDX,
			PR.FILTER_SZ	AS FILTER_SZ_IDX
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
	
	$filter_cl_idx = "";
	$filter_sz_idx = "";
	
	foreach($db->fetch() as $data) {
		$filter_cl_idx .= $data['FILTER_CL_IDX'];
		$filter_sz_idx .= $data['FILTER_SZ_IDX'];
	}
	
	$param_cl = array();
	if (strlen($filter_cl_idx) > 0) {
		$param_cl = explode(",",$filter_cl_idx);
		$param_cl = array_unique($param_cl);
	}
	
	if (count($param_cl) > 0) {
		$filter_cl = getFILTER_cl($db,$country,$param_cl);
	}
	
	$param_sz = array();
	if (strlen($filter_sz_idx) > 0) {
		$param_sz = explode(",",$filter_sz_idx);
		$param_sz = array_unique($param_sz);
	}
	
	if (count($param_sz) > 0) {
		$param_sz = array_unique($param_sz);
		$filter_sz = getFILTER_sz($db,$country,$param_sz);
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

function getFILTER_cl($db,$country,$param_idx) {
	$filter_cl = array();
	
	$select_filter_color_sql = "
		SELECT
			PF.IDX						AS FILTER_IDX,
			PF.FILTER_NAME_".$country."	AS FILTER_NAME,
			PF.RGB_COLOR				AS RGB_COLOR
		FROM
			PRODUCT_FILTER PF
		WHERE
			PF.IDX IN (".implode(",",$param_idx).") AND
			PF.FILTER_TYPE = 'CL' AND
			PF.DEL_FLG = FALSE
	";
	
	$db->query($select_filter_color_sql);
	
	foreach($db->fetch() as $data) {
		$filter_cl[] = array(
			'filter_idx'		=>$data['FILTER_IDX'],
			'filter_name'		=>$data['FILTER_NAME'],
			'rgb_color'			=>$data['RGB_COLOR'],
		);
	}
	
	return $filter_cl;
}

function getFILTER_sz($db,$country,$param_idx) {
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
	
	$db->query($select_filter_size_sql,array(implode(",",$param_idx)));
	
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

function getPRODUCT_img($param) {
	$img_type_P = "";
	$img_location_P = "";
	if ($param['img_location_P'] != null) {
		$img_type_P = "TP";
		$img_location_P = $param['img_location_P'];
	}
	
	if ($param['img_location_TP'] != null) {
		$img_type_P = "P";
		$img_location_P = $param['img_location_TP'];
	}
	
	$product_p_img[] = array(
		'img_type'		=>$img_type_P,
		'img_location'	=>$img_location_P
	);
	
	$img_type_O = "";
	$img_location_O = "";
	if ($param['img_location_O'] != null) {
		$img_type_O = "TO";
		$img_location_O = $param['img_location_O'];
	}
	
	if ($param['img_location_TO'] != null) {
		$img_type_O = "O";
		$img_location_O = $param['img_location_TO'];
	}
	
	$product_o_img[] = array(
		'img_type'		=>$img_type_O,
		'img_location'	=>$img_location_O
	);
	
	$product_img = array(
		'product_p_img'		=>$product_p_img,
		'product_o_img'		=>$product_o_img
	);
	
	return $product_img;
}

/*
function getPRODUCT_img($db,$product_idx) {
	$product_img = array();
	
	$p_img_type = 'P';
	$o_img_type = 'O';
	
	$cnt_thmb = $db->count("PRODUCT_IMG","PRODUCT_IDX = ".$product_idx." AND IMG_TYPE LIKE 'T%'");
	if ($cnt_thmb > 0) {
		$p_img_type = 'TP';
		$o_img_type = 'TO';
	}
	
	$product_img = getIMG($db,$product_idx,$p_img_type,$o_img_type);
	
	return $product_img;
}
*/

function getIMG($db,$product_idx,$p_img_type,$o_img_type) {
	$product_img = array();
	
	$product_p_img = array();
	$product_o_img = array();
	
	$select_product_img_sql = "
		(
			SELECT
				'P'					AS IMG_TYPE,
				PI.IMG_LOCATION		AS IMG_LOCATION
			FROM
				PRODUCT_IMG PI
			WHERE
				PI.PRODUCT_IDX = ? AND
				PI.IMG_TYPE = ? AND
				PI.IMG_SIZE = 'M'
			ORDER BY
				PI.IDX ASC
		) UNION (
			SELECT
				'O'					AS IMG_TYPE,
				PI.IMG_LOCATION		AS IMG_LOCATION
			FROM
				PRODUCT_IMG PI
			WHERE
				PI.PRODUCT_IDX = ? AND
				PI.IMG_TYPE = ? AND
				PI.IMG_SIZE = 'M'
			ORDER BY
				PI.IDX ASC
		)
	";
	
	$db->query($select_product_img_sql,array($product_idx,$p_img_type,$product_idx,$o_img_type));
	
	foreach($db->fetch() as $data) {
		$img_type = $data['IMG_TYPE'];
		
		if ($img_type == "P") {
			$product_p_img[] = array(
				'img_type'		=>$img_type,
				'img_location'	=>$data['IMG_LOCATION']
			);
		} else if ($img_type == "O") {
			$product_p_img[] = array(
				'img_type'		=>$img_type,
				'img_location'	=>$data['IMG_LOCATION']
			);
		}
	}
	
	$product_img = array(
		'product_p_img'		=>$product_p_img,
		'product_o_img'		=>$product_o_img
	);
	
	return $product_img;
}

function getPRODUCT_color($db,$param_code,$member_idx) {
	$product_color = array();
	
	$select_product_color_sql = "
		SELECT
			PR.IDX				AS PRODUCT_IDX,
			PR.STYLE_CODE		AS STYLE_CODE,
			PR.COLOR			AS COLOR,
			IFNULL(
				PR.COLOR_RGB,
				'#FFFFFF'
			)					AS COLOR_RGB,
			
			J_ST.LIMIT_QTY		AS LIMIT_QTY,
			J_RO.REORDER_QTY	AS REORDER_QTY
		FROM
			SHOP_PRODUCT PR
			
			LEFT JOIN (
				SELECT
					V_ST.PRODUCT_IDX			AS PRODUCT_IDX,
					SUM(V_ST.PURCHASEABLE_QTY)	AS LIMIT_QTY
				FROM
					V_STOCK V_ST
				GROUP BY
					V_ST.PRODUCT_IDX
			) J_ST ON
			PR.IDX = J_ST.PRODUCT_IDX
			
			LEFT JOIN (
				SELECT
					S_RO.PRODUCT_IDX		AS PRODUCT_IDX,
					COUNT(S_RO.PRODUCT_IDX)	AS REORDER_QTY
				FROM
					PRODUCT_REORDER S_RO
				WHERE
					MEMBER_IDX = ?
				GROUP BY
					S_RO.PRODUCT_IDX
			) J_RO ON
			PR.IDX = J_RO.PRODUCT_IDX
		WHERE
			PR.SALE_FLG = TRUE AND
			PR.STYLE_CODE IN (".implode(",",$param_code).")
	";
	
	$db->query($select_product_color_sql,array($member_idx));
	
	foreach($db->fetch() as $data) {
		$stock_status = "STSO";
		
		$limit_qty= $data['LIMIT_QTY'];
		if ($limit_qty > 0) {
			$stock_status = "STIN";	//재고 있음 (Stock in)
		}
		
		$reorder_flg = false;
		$reorder_qty = $data['REORDER_QTY'];
		if ($reorder_qty > 0) {
			$reorder_flg = true;
		}
		
		$product_color[$data['STYLE_CODE']][] = array(
			'product_idx'	=>$data['PRODUCT_IDX'],
			'color'			=>$data['COLOR'],
			'color_rgb'		=>$data['COLOR_RGB'],
			'stock_status'	=>$stock_status,
			'reorder_flg'	=>$reorder_flg
		);
	}
	
	return $product_color;
}

function getPRODUCT_size($db,$param_idx) {
	$product_size = array();
	
	$select_product_size_sql = "
		SELECT
			PR.IDX						AS PRODUCT_IDX,
			PR.SOLD_OUT_QTY				AS SOLD_OUT_QTY,
			OO.IDX						AS OPTION_IDX,
			OO.OPTION_NAME				AS OPTION_NAME,
			
			J_PS.CNT					AS STOCK_STANDBY,
			J_ST.LIMIT_QTY				AS LIMIT_QTY
		FROM
			SHOP_PRODUCT PR
			LEFT JOIN ORDERSHEET_OPTION OO ON
			PR.ORDERSHEET_IDX = OO.ORDERSHEET_IDX
			
			LEFT JOIN (
				SELECT
					S_PS.PRODUCT_IDX	AS PRODUCT_IDX,
					S_PS.OPTION_IDX		AS OPTION_IDX,
					COUNT(S_PS.IDX)		AS CNT
				FROM
					PRODUCT_STOCK S_PS
				WHERE
					S_PS.STOCK_DATE > NOW()
				GROUP BY
					S_PS.PRODUCT_IDX,
					S_PS.OPTION_IDX
			) J_PS ON
			PR.IDX = J_PS.PRODUCT_IDX AND
			OO.IDX = J_PS.OPTION_IDX
			
			LEFT JOIN (
				SELECT
					V_ST.PRODUCT_IDX		AS PRODUCT_IDX,
					V_ST.OPTION_IDX			AS OPTION_IDX,
					V_ST.PURCHASEABLE_QTY	AS LIMIT_QTY
				FROM
					V_STOCK V_ST
				GROUP BY
					V_ST.PRODUCT_IDX,
					V_ST.OPTION_IDX
			) J_ST ON
			PR.IDX = J_ST.PRODUCT_IDX AND
			OO.IDX = J_ST.OPTION_IDX
		WHERE
			PR.IDX IN (".implode(",",$param_idx).") AND
			PR.PRODUCT_TYPE = 'B'
	";
	
	$db->query($select_product_size_sql);
	
	foreach($db->fetch() as $data) {
		$option_idx = $data['OPTION_IDX'];
		
		$size_type = setSizeType($data['OPTION_NAME']);
		
		$sold_out_qty	= $data['SOLD_OUT_QTY'];
		$stock_standby	= $data['STOCK_STANDBY'];
		
		$limit_qty = $data['LIMIT_QTY'];
		
		$stock_status = calcStockQty($sold_out_qty,$stock_standby,$limit_qty);
		
		$product_size[$data['PRODUCT_IDX']][] = array(
			'product_idx'		=>$data['PRODUCT_IDX'],
			'option_idx'		=>$data['OPTION_IDX'],
			'option_name'		=>$data['OPTION_NAME'],
			
			'size_type'			=>$size_type,
			'stock_status'		=>$stock_status
		);
	}
	
	return $product_size;
}

?>