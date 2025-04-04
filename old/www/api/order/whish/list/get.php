<?php
/*
 +=============================================================================
 | 
 | 찜한 상품 리스트 - 상품 리스트 조회
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.10.13
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

include_once(dir_f_api."/common.php");

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

if ($member_idx == 0 || $country == null) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	
	echo json_encode($json_result);
	exit;
} else {
	$select_whish_sql = "
		SELECT
			WL.IDX						AS WHISH_IDX,
			WL.PRODUCT_IDX				AS PRODUCT_IDX,
			PR.PRODUCT_TYPE				AS PRODUCT_TYPE,
			PR.PRODUCT_CODE				AS PRODUCT_CODE,
			PR.STYLE_CODE				AS STYLE_CODE,
			IFNULL(
				PR.SET_TYPE,'BS'
			)							AS SET_TYPE,
			J_PI.IMG_LOCATION			AS PRODUCT_IMG,
			WL.PRODUCT_NAME				AS PRODUCT_NAME,
			PR.PRICE_".$country."		AS PRICE,
			PR.DISCOUNT_".$country."	AS DISCOUNT,
			PR.SALES_PRICE_".$country."	AS SALES_PRICE,
			PR.COLOR					AS COLOR,
			PR.COLOR_RGB				AS COLOR_RGB,
			WL.OPTION_IDX				AS OPTION_IDX,
			WL.OPTION_NAME				AS OPTION_NAME,
			WL.PRODUCT_QTY				AS PRODUCT_QTY
		FROM
			WHISH_LIST WL
			LEFT JOIN SHOP_PRODUCT PR ON
			WL.PRODUCT_IDX = PR.IDX
			
			LEFT JOIN (
				SELECT
					S_PI.PRODUCT_IDX	AS PRODUCT_IDX,
					S_PI.IMG_LOCATION	AS IMG_LOCATION
				FROM
					PRODUCT_IMG S_PI
				WHERE
					S_PI.IMG_TYPE = 'P' AND
					S_PI.IMG_SIZE = 'S'
				GROUP BY
					S_PI.PRODUCT_IDX
				ORDER BY
					S_PI.IDX ASC
			) AS J_PI ON
			WL.PRODUCT_IDX = J_PI.PRODUCT_IDX
		WHERE
			WL.COUNTRY = '".$country."' AND
			WL.MEMBER_IDX = ? AND
			WL.DEL_FLG = FALSE AND
			PR.SALE_FLG = TRUE AND
			PR.DEL_FLG = FALSE
		ORDER BY
			WL.IDX DESC
	";
	
	$db->query($select_whish_sql,array($member_idx));
	
	$param_color	= array();
	$param_size		= array();
	
	$param_B		= array();
	$param_S		= array();
	
	$wish_product	= array();
	
	foreach($db->fetch() as $data) {
		array_push($param_color,"'".$data['STYLE_CODE']."'");
		
		/*
		if ($data['PRODUCT_TYPE'] == "B") {
			array_push($param_B,$data['PRODUCT_IDX']);
		} else if ($data['PRODUCT_TYPE'] == "S") {
			if ($data['SET_TYPE'] != null) {
				array_push($param_S,$data['PRODUCT_IDX']);
			}
		}
		
		$param_size = array(
			'param_B'		=>$param_B,
			'param_S'		=>$param_S
		);
		*/
		
		$product_size	= getProductSize($db,$data['PRODUCT_TYPE'],$data['SET_TYPE'],$data['PRODUCT_IDX']);
		
		$wish_product[] = array(
			'wish_idx'			=>$data['WHISH_IDX'],
			'product_idx'		=>$data['PRODUCT_IDX'],
			'style_code'		=>$data['STYLE_CODE'],
			'product_type'		=>$data['PRODUCT_TYPE'],
			'set_type'			=>$data['SET_TYPE'],
			'product_img'		=>$data['PRODUCT_IMG'],
			'product_name'		=>$data['PRODUCT_NAME'],
			'price'				=>number_format($data['PRICE']),
			'discount'			=>$data['DISCOUNT'],
			'sales_price'		=>number_format($data['SALES_PRICE']),
			'color'				=>$data['COLOR'],
			'color_rgb'			=>$data['COLOR_RGB'],
			'option_idx'		=>$data['OPTION_IDX'],
			'option_name'		=>$data['OPTION_NAME'],
			'product_qty'		=>$data['PRODUCT_QTY'],
			
			'product_size'		=>$product_size,
			'set_type'			=>$data['SET_TYPE']
		);
	}
	
	if ($wish_product != null) {
		$product_color	= getWISH_color($db,$param_color,$member_idx);
		
		//$product_size	= getWISH_SIZE($db,$param_size);
		
		for ($i=0; $i<count($wish_product); $i++) {
			$wish = $wish_product[$i];
			
			$tmp_style_code		= $wish['style_code'];
			$tmp_product_idx	= $wish['product_idx'];
			/*
			$stock_status = null;
			$soldout_cnt = 0;
			$stock_close_cnt = 0;
			
			$tmp_product_size = $product_size[$wish['product_idx']];
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
			*/
			
			$wish_product[$i]['product_color']	= $product_color[$tmp_style_code];
			//$wish_product[$i]['product_size']	= $tmp_product_size;
			//$wish_product[$i]['stock_status']	= $stock_status;
		}
	}
	
	$json_result['data'] = $wish_product;
}

function getWISH_color($db,$param_code,$member_idx) {
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

/* 일반/세트 상품 사이즈별 재고정보 조회 */
function getWISH_SIZE($db,$param) {
	$product_size = array();
	
	$param_B = $param['param_B'];
	if (count($param_B) > 0) {
		$product_size_B = getWISH_SIZE_B($db,$param_B);
	}
	
	$param_S = $param['param_S'];
	if (count($param_S) > 0) {
		$product_size_S = getWISH_SIZE_S($db,$param_S);
	}
	
	return $product_size;
}

/* 일반상품 사이즈별 재고정보 조회 */
function getWISH_SIZE_B($db,$param) {
	$product_size_B = array();
	
	$select_product_sql = "
		SELECT
			PR.IDX						AS PRODUCT_IDX,
			PR.PRODUCT_CODE				AS PRODUCT_CODE,
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
			PR.IDX IN (".implode(",",$param).") AND
			PR.SALE_FLG = TRUE
	";
	
	$db->query($select_product_sql);
	
	foreach($db->fetch() as $data) {
		$option_idx = $data['OPTION_IDX'];
		
		$size_type = setSizeType($data['OPTION_NAME']);
		
		$sold_out_qty	= $data['SOLD_OUT_QTY'];
		$stock_standby	= $data['STOCK_STANDBY'];
		
		$limit_qty		= $data['LIMIT_QTY'];
		
		$stock_status = calcStockQty($sold_out_qty,$stock_standby,$limit_qty);
		
		$product_size_B[$data['PRODUCT_CODE']] = array(
			'product_idx'		=>$data['PRODUCT_IDX'],
			'option_idx'		=>$data['OPTION_IDX'],
			'option_name'		=>$data['OPTION_NAME'],
			
			'size_type'			=>$size_type,
			'stock_status'		=>$stock_status
		);
	}
	
	return $product_size_B;
}

function getWISH_SIZE_S($db,$param) {
	$product_size_S = array();
	
	$select_set_name_sql = "
		SELECT
			PR.PRODUCT_CODE				AS PRODUCT_CODE,
			DISTINCT PR.PRODUCT_NAME	AS PRODUCT_NAME
		FROM
			SET_PRODUCT SP
			LEFT JOIN SHOP_PRODUCT PR ON
			SP.PRODUCT_IDX = PR.IDX
		WHERE
			SP.SET_PRODUCT_IDX IN (".implode(",",$param).")
	";
	
	$db->query($select_set_name_sql);
	
	foreach($db->fetch() as $data) {
		$product_name = $data['PRODUCT_NAME'];
		
		$set_option_info = array();
		
		if ($set_type == "SZ") {
			/* 사이즈 세트 옵션정보 조회 */
			$set_option = getSET_SZ($db,$product_idx,$product_name);
		} else if ($set_type == "CL") {
			/* 컬러 세트 옵션정보 조회 */
			$set_option = getSET_CL($db,$product_idx,$product_name);
		}
		
		$product_size_S[$data['PRODUCT_CODE']] = array(
			'product_name'		=>$product_name,
			'set_option_info'	=>$set_option
		);
	}
	
	return $product_size_S;
}

?>