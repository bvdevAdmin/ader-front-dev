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

$member_level = 0;
if (isset($_SESSION['LEVEL_IDX'])) {
	$member_level = $_SESSION['LEVEL_IDX'];
}

/* 상품 진열 페이지 - 진열 상품 리스트 조회 */
if (isset($_SERVER['HTTP_COUNTRY']) && isset($page_idx)) {
	$page_menu = array();
	
	$page_product = array();
	
	/* 1. 상품 진열 페이지 - 상품 페이지 체크 처리 */
	$check_result = checkPage($db,$page_idx,$member_level);
	if ($check_result) {
		/* 2. 상품 진열 페이지 - 메뉴 정보 조회 */
		if (isset($depth) && isset($header_idx)) {
			$page_menu = getPage_menu($db,$header_idx);
		}
		
		/* 4. 상품 진열 페이지 - 상품 페이지 정보 조회 */
		$table = "
			PRODUCT_GRID PG
			
			LEFT JOIN SHOP_PRODUCT PR ON
			PG.PRODUCT_IDX = PR.IDX
			
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
			PG.PRODUCT_IDX = J_ST.PRODUCT_IDX

			LEFT JOIN ASSET_CONTENTS AC ON
			PG.CONTENTS_IDX = AC.IDX

			LEFT JOIN GRID_ASSET GA ON
			AC.ASSET_IDX = GA.IDX
		";
			
		$where = "
			PG.PAGE_IDX = ? AND
			PG.DEL_FLG = FALSE AND
		";
		
		// 조건 분기: 필터 소트가 있는 경우는 상품만 반환한다
		if ((isset($param_filter) && !empty($param_filter)) || (isset($param_sort) && !empty($param_sort))) {
			$where .= "
				PG.PRODUCT_IDX > 0 AND
				PR.SALE_FLG = TRUE AND
				PR.DEL_FLG = FALSE
			";
		} else {
			$where .= "
			(
				(PG.CONTENTS_IDX > 0 AND AC.DEL_FLG = FALSE)
				OR
				(PG.PRODUCT_IDX > 0 AND
				PR.SALE_FLG = TRUE AND
				PR.DEL_FLG = FALSE)
			)
			";
		}
			
		$param_bind = array($_SERVER['HTTP_COUNTRY'],$member_idx,$page_idx);
			
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

		$select_page_product_sql = "
			SELECT
				PG.DISPLAY_NUM				AS DISPLAY_NUM,
				PG.TYPE						AS GRID_TYPE,
				PG.SIZE						AS GRID_SIZE,
				PG.BACKGROUND_COLOR			AS BACKGROUND_COLOR,
				PG.PRODUCT_IDX				AS PRODUCT_IDX,

				PG.CONTENTS_IDX				AS CONTENTS_IDX,
				PG.GRID_SIZE					AS GRID_SIZE,
				GA.ASSET_TYPE					AS ASSET_TYPE,
				PG.CONTENTS_IDX					AS CONTENTS_IDX,
				IFNULL(
					AC.CONTENTS_LOCATION,''
				)								AS CONTENTS_LOCATION,
				
				PR.PRODUCT_TYPE				AS PRODUCT_TYPE,
				PR.SET_TYPE					AS SET_TYPE,
				PR.PRODUCT_NAME				AS PRODUCT_NAME,
				
				PR.COLOR					AS COLOR,
				
				PR.PRICE_KR					AS PRICE_KR,
				PR.DISCOUNT_KR				AS DISCOUNT_KR,
				PR.SALES_PRICE_KR			AS SALES_PRICE_KR,
				
				PR.PRICE_EN					AS PRICE_EN,
				PR.DISCOUNT_EN				AS DISCOUNT_EN,
				PR.SALES_PRICE_EN			AS SALES_PRICE_EN,
				
				IFNULL(
					J_WL.CNT_WISH,0
				)							AS CNT_WISH,
				IFNULL(
					J_ST.ORDER_QTY,0
				)							AS ORDER_QTY
			FROM
				".$table."
			WHERE
				".$where."
			ORDER BY
				".$order."
		";
		
		if ($last_idx > 0) {
			$select_page_product_sql .= " LIMIT ?,12 ";
			
			array_push($param_bind,$last_idx);
		} else {
			$select_page_product_sql .= " LIMIT 0,12 ";
		}
		
		$db->query($select_page_product_sql,$param_bind);

		
		$param_idx_B	= array();
		$param_idx_S	= array();
		
		$display_num = $last_idx; // 이전 페이지까지의 마지막 순번
		foreach($db->fetch() as $data) {
			$display_num++; // 순번 증가
			$grid_type		= $data['GRID_TYPE'];
			$product_idx	= $data['PRODUCT_IDX'];
			$price			= 0;
			$discount		= 0;
			$sales_price	= 0;
			
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
			if($data['GRID_TYPE'] =="PRD"){
				$price			= number_format($data['PRICE_'.$_SERVER['HTTP_COUNTRY']]);
				$discount		= $data['DISCOUNT_'.$_SERVER['HTTP_COUNTRY']];
				$sales_price	= number_format($data['SALES_PRICE_'.$_SERVER['HTTP_COUNTRY']]);
		
				if ($_SERVER['HTTP_COUNTRY'] == "EN") {
					$price			= number_format($data['PRICE_'.$_SERVER['HTTP_COUNTRY']],1);
					$sales_price	= number_format($data['SALES_PRICE_'.$_SERVER['HTTP_COUNTRY']],1);
				}

				$product_img	= $product_idx ? getProduct_img($db,$product_idx) : [];
			}
			$stock_status = null;
			$soldout_cnt = 0;
			$stock_close_cnt = 0;

			$page_product[] = array(
				'display_num'		=>$display_num,
				'grid_type'			=>$data['GRID_TYPE'],
				'grid_size'			=>$data['GRID_SIZE'],
				'background_color'	=>$data['BACKGROUND_COLOR'],
				'product_idx'		=>$data['PRODUCT_IDX'],
				'contents_idx'		=>$data['CONTENTS_IDX'],
				'asset_type'		=>$data['ASSET_TYPE'],

				'product_type'		=>$data['PRODUCT_TYPE'],
				'product_name'		=>$data['PRODUCT_NAME'],
				'color'				=>$data['COLOR'],
				
				'price'				=>$price,
				'discount'			=>$discount,
				'sales_price'		=>$sales_price,
				
				'product_img'		=>$product_img,
				'contents_location'			=>$data['CONTENTS_LOCATION'],
				
				'stock_status'		=>$stock_status,
				
				'whish_flg'			=>$wish_flg
			);
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
	} else {
		$json_result['code'] = 402;
		$json_result['msg'] = getMsgToMsgCode($db, $_SERVER['HTTP_COUNTRY'], 'MSG_B_ERR_0089', array());
		
		echo json_encode($json_result);
		exit;
	}
	
	$json_result['data'] = array(
		'menu_info'		=>$page_menu,
		'grid_info'		=>$page_product
	);
} else {
	$json_result['code'] = 402;
	$json_result['msg'] = getMsgToMsgCode($db, $_SERVER['HTTP_COUNTRY'], 'MSG_B_ERR_0095', array());
	
	echo json_encode($json_result);
	exit;
}

/* 1. 상품 진열 페이지 - 상품 페이지 체크 처리 */
function checkPage($db,$page_idx,$member_level) {
	$check_result = false;
	
	$cnt_page = $db->count(
		"PAGE_PRODUCT",
		"
			IDX = ? AND
			(
				FIND_IN_SET(0,DISPLAY_MEMBER_LEVEL) OR
				FIND_IN_SET(?,DISPLAY_MEMBER_LEVEL)
			) AND
			DISPLAY_FLG = TRUE AND
			(
				ALWAYS_FLG = TRUE OR
				NOW() BETWEEN DISPLAY_START_DATE AND DISPLAY_END_DATE
			)
		",
		array($page_idx,$member_level)
	);

	if ($cnt_page > 0) {
		$check_result = true;
	}

	return $check_result;
}

function getPage_menu($db,$header_idx) {
	$page_menu = array();
	
	$menu_top		= array();
	$menu_middle	= array();
	$menu_bottom	= array();

	$menu_slide		= array();
	
	$cnt_top = $db->count("LANDING_HEADER","IDX = (SELECT PARENT_IDX FROM LANDING_HEADER WHERE IDX = ?) AND A1_FLG = TRUE AND DEL_FLG = FALSE",array($header_idx));
	if ($cnt_top > 0) {
		/* 상품 진열 페이지 - 상단 메뉴 */
		$menu_top = getMenu_top($db,$header_idx);
	}
	
	$cnt_middle = $db->count("LANDING_HEADER","PARENT_IDX = (SELECT PARENT_IDX FROM LANDING_HEADER WHERE IDX = ?) AND A1_FLG = TRUE AND DEL_FLG = FALSE",array($header_idx));
	if ($cnt_middle > 0) {
		/* 상품 진열 페이지 - 중앙 메뉴 */
		$menu_middle = getMenu_middle($db,$header_idx);
	}

	$cnt_bottom = $db->count("LANDING_HEADER","PARENT_IDX = ? AND A1_FLG = TRUE AND DEL_FLG = FALSE",array($header_idx));
	if ($cnt_bottom > 0) {
		/* 상품 진열 페이지 - 하단 메뉴 */
		$menu_bottom = getMenu_bottom($db,$header_idx);
	}
	
	$cnt_header = $db->count("LANDING_HEADER","IDX = ? AND SLIDE_FLG = TRUE",array($header_idx));
	if ($cnt_header > 0) {
		$cnt_slide = $db->count("HEADER_SLIDE","PARENT_IDX = ? AND DEL_FLG = FALSE",array($header_idx));
		if ($cnt_slide > 0) {
			/* 상품 진열 페이지 - 슬라이드 메뉴 */
			$menu_slide = getMenu_slide($db,$header_idx);
		}
	}
	
	$page_menu = array(
		'menu_top'			=>$menu_top,
		'menu_middle'		=>$menu_middle,
		'menu_bottom'		=>$menu_bottom,
		'menu_slide'		=>$menu_slide
	);
	
	return $page_menu;
}

/* 상품 페이지 - 상단 메뉴 */
function getMenu_top($db,$header_idx) {
	$menu_top = array();
	
	$select_menu_top_sql = "
		(
			SELECT
				TP.IDX				AS TOP_IDX,
				TP.DEPTH			AS DEPTH,
				TP.HEADER_TITLE		AS TOP_TITLE,
				TP.EXT_FLG			AS EXT_FLG,
				IFNULL(
					TP.HEADER_LINK,''
				)					AS TOP_LINK
			FROM
				LANDING_HEADER TP
			WHERE
				TP.IDX = (
					SELECT
						S_TP.PARENT_IDX
					FROM
						LANDING_HEADER S_TP
					WHERE
						S_TP.IDX = ?
				) AND
				 TP.DEPTH > 0
		) UNION (
			SELECT
				TP.IDX				AS TOP_IDX,
				TP.DEPTH			AS DEPTH,
				TP.HEADER_TITLE		AS TOP_TITLE,
				TP.EXT_FLG			AS EXT_FLG,
				IFNULL(
					TP.HEADER_LINK,''
				)					AS TOP_LINK
			FROM
				LANDING_HEADER TP
			WHERE
				TP.IDX = ? AND
				TP.DEPTH > 0
		)
	";
	
	$db->query($select_menu_top_sql,array($header_idx,$header_idx));
	
	foreach($db->fetch() as $data) {
		$header_link = $data['TOP_LINK'];
		if (strlen($data['TOP_LINK']) > 0) {
		if ($data['EXT_FLG'] == true) {
				$header_link = "http://".$header_link;
			} else {
				$header_link = $header_link."&depth=".$data['DEPTH']."&header_idx=".$data['TOP_IDX'];
			}
		}
		
		$menu_top[] = array(
			'menu_title'	=>$data['TOP_TITLE'],
			'menu_link'		=>$header_link
		);
	}
	
	return $menu_top;
}

/* 상품 페이지 - 중앙 메뉴 */
function getMenu_middle($db,$header_idx) {
	$menu_middle = array();
	
	$select_menu_middle_sql = "
		SELECT
			MD.IDX				AS MIDDLE_IDX,
			MD.DEPTH			AS DEPTH,
			MD.HEADER_TITLE		AS MIDDLE_TITLE,
			MD.EXT_FLG			AS EXT_FLG,
			IFNULL(
				MD.HEADER_LINK,''
			)					AS MIDDLE_LINK
		FROM
			LANDING_HEADER MD
		WHERE
			MD.PARENT_IDX = (
				SELECT
					S_MD.PARENT_IDX
				FROM
					LANDING_HEADER S_MD
				WHERE
					S_MD.IDX = ?
			)
		ORDER BY
			MD.DISPLAY_NUM ASC
	";
	
	$db->query($select_menu_middle_sql,array($header_idx));
	
	foreach($db->fetch() as $key => $data) {
		$selected = false;
		if ($header_idx == $data['MIDDLE_IDX']) {
			$selected = true;
		}
		
		$header_link = $data['MIDDLE_LINK'];
		if (strlen($header_link) > 0) {
			if ($data['EXT_FLG'] == true) {
				$header_link = "http://".$header_link;
			} else {
				$header_link .= $header_link."&depth=".$data['DEPTH']."&header_idx=".$data['MIDDLE_IDX'];
			}
		}
		
		$menu_middle[] = array(
			'menu_title'	=>$data['MIDDLE_TITLE'],
			'menu_link'		=>$header_link,
			'selected'		=>$selected
		);
	}
	
	return $menu_middle;
}

/* 상품 페이지 - 하단 메뉴 */
function getMenu_bottom($db,$header_idx) {
	$menu_bottom = array();
	
	$select_menu_bottom_sql = "
		SELECT
			BT.IDX				AS BOTTOM_IDX,
			BT.DEPTH			AS DEPTH,
			BT.HEADER_TITLE		AS BOTTOM_TITLE,
			BT.EXT_FLG			AS EXT_FLG,
			IFNULL(
				BT.HEADER_LINK,''
			)					AS BOTTOM_LINK
		FROM
			LANDING_HEADER BT
		WHERE
			BT.PARENT_IDX = ?
		ORDER BY
			BT.DISPLAY_NUM ASC
	";

	$db->query($select_menu_bottom_sql,array($header_idx));

	foreach($db->fetch() as $key => $data) {
		$header_link = $data['BOTTOM_LINK'];
		if (strlen($header_link) > 0) {
			if ($data['EXT_FLG'] == true) {
				$header_link = "http://".$header_link;
			} else {
				$header_link .= $header_link."&depth=".$data['DEPTH']."&header_idx=".$data['BOTTOM_IDX'];
			}
		}
		
		$menu_bottom[] = array(
			'menu_title'	=>$data['BOTTOM_TITLE'],
			'menu_link'		=>$header_link
		);
	}
	
	return $menu_bottom;
}

function getMenu_slide($db,$header_idx) {
	$menu_slide = array();
	
	$select_menu_slide_sql ="
		SELECT
			HS.IDX				AS SLIDE_IDX,
			HS.SLIDE_TITLE		AS SLIDE_TITLE,
			HS.IMG_LOCATION		AS IMG_LOCATION,
			
			HS.EXT_FLG			AS EXT_FLG,
			IFNULL(
				HS.SLIDE_LINK,''
			)					AS SLIDE_LINK
		FROM
			HEADER_SLIDE HS
		WHERE
			HS.PARENT_IDX	= ? AND
			HS.DEL_FLG		= FALSE
		ORDER BY
			HS.DISPLAY_NUM ASC
	";
	
	$db->query($select_menu_slide_sql,array($header_idx));
	
	foreach($db->fetch() as $data) {
		$img_location = "/default/default_img.jpg";
		if ($data['IMG_LOCATION'] != null) {
			$img_location = $data['IMG_LOCATION'];
		}

		$slide_link = $data['SLIDE_LINK'];
		if (strlen($slide_link) > 0) {
			if ($data['EXT_FLG'] == true) {
				$slide_link = "http://".$slide_link;
			} else {
				$slide_link .= $slide_link;
			}
		}
		
		$menu_slide[] = array(
			'slide_title'	=>$data['SLIDE_TITLE'],
			'img_location'	=>$img_location,
			'slide_link'	=>$slide_link
		);
	}
	
	return $menu_slide;
}

function setMenu_table($menu_type) {
	$menu_table = null;
	
	switch ($menu_type) {
		case "HL1" :
			$menu_table = array(
				'parent'	=>"MENU_SEGMENT",
				'menu'		=>"MENU_HL_1",
				'child'		=>"MENU_HL_2"
			);
			
			break;
		
		case "HL2" :
			$menu_table = array(
				'parent'	=>"MENU_HL_1",
				'menu'		=>"MENU_HL_2",
				'child'		=>"MENU_HL_3"
			);
			
			break;
		
		case "HL3" :
			$menu_table = array(
				'parent'	=>"MENU_HL_2",
				'menu'		=>"MENU_HL_3",
				'child'		=>null
			);
			
			break;
	}
	
	return $menu_table;
}

/* 4-2. 상품 진열 페이지 - 정렬 검색조건 설정 */
function setSQL_order($param) {
	$order	= " PG.DISPLAY_NUM ASC ";
	
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