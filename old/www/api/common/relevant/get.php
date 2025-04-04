<?php
/*
 +=============================================================================
 | 
 | 공통 - 관련 상품 리스트 조회
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.10.25
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

include_once(dir_f_api."/common.php");

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$relevant_idx = null;
if (is_array($_POST['relevant_idx'])) {
	$relevant_idx = implode(",",$_POST['relevant_idx']);
} else {
	$relevant_idx = $_POST['relevant_idx'];
}

$country		= $_POST['country'];

if ($relevant_idx != null && $country != null) {
	$select_relevant_product_sql = "
		SELECT
			PR.IDX						AS PRODUCT_IDX,
			PR.PRODUCT_TYPE				AS PRODUCT_TYPE,
			PR.STYLE_CODE				AS STYLE_CODE,
			PR.SET_TYPE					AS SET_TYPE,
			(
				SELECT
					S_PI.IMG_LOCATION
				FROM
					PRODUCT_IMG S_PI
				WHERE
					S_PI.PRODUCT_IDX = PR.IDX AND
					S_PI.IMG_TYPE = 'P' AND
					S_PI.IMG_SIZE = 'M'
				ORDER BY
					S_PI.IDX ASC
				LIMIT
					0,1
			)							AS PRODUCT_IMG,
			PR.PRODUCT_NAME				AS PRODUCT_NAME,
			OM.COLOR					AS COLOR,
			PR.PRICE_".$country."		AS PRICE,
			PR.DISCOUNT_".$country."	AS DISCOUNT,
			PR.SALES_PRICE_".$country."	AS SALES_PRICE,
			OM.COLOR					AS COLOR
		FROM
			SHOP_PRODUCT PR
			LEFT JOIN ORDERSHEET_MST OM ON
			PR.ORDERSHEET_IDX = OM.IDX
		WHERE
			PR.IDX IN (".$relevant_idx.")
	";
	
	$db->query($select_relevant_product_sql);
	
	$param_idx	= array();
	$param_code	= array();
	
	$relevant_product = array();
	
	foreach($db->fetch() as $data) {		
		$product_idx = $data['PRODUCT_IDX'];
		
		array_push($param_idx,$product_idx);
		array_push($param_code,"'".$data['STYLE_CODE']."'");
		
		$whish_flg = false;
		if ($member_idx > 0) {
			$whish_cnt = $db->count("WHISH_LIST"," MEMBER_IDX = ".$member_idx." AND PRODUCT_IDX = ".$product_idx." AND DEL_FLG = FALSE");
			
			if ($whish_cnt > 0) {
				$whish_flg = true;
			}
		}
		
		$relevant_product[] = array(
			'product_idx'		=>$product_idx,
			'style_code'		=>$data['STYLE_CODE'],
			'product_type'		=>$data['PRODUCT_TYPE'],
			'product_img'		=>$data['PRODUCT_IMG'],
			'product_name'		=>$data['PRODUCT_NAME'],
			'color'				=>$data['COLOR'],
			'price'				=>number_format($data['PRICE']),
			'discount'			=>$data['DISCOUNT'],
			'sales_price'		=>number_format($data['SALES_PRICE']),
			
			'whish_flg'			=>$whish_flg
		);
	}
	
	if ($relevant_product != null) {
		$product_color	= getPRODUCT_color($db,$param_code,$member_idx);
		
		$product_size	= getPRODUCT_size($db,$param_idx,$member_idx);
		
		for ($i=0; $i<count($relevant_product); $i++) {
			$relevant = $relevant_product[$i];
			
			$tmp_style_code		= $relevant['style_code'];
			$tmp_product_idx	= $relevant['product_idx'];
			
			$stock_status = null;
			$soldout_cnt = 0;
			$stock_close_cnt = 0;
			
			$tmp_product_size = $product_size[$relevant['product_idx']];
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
			
			$relevant_product[$i]['product_color']	= $product_color[$tmp_style_code];
			$relevant_product[$i]['product_size']	= $tmp_product_size;
			$relevant_product[$i]['stock_status']	= $stock_status;
		}
	}
	
	$json_result['data'] = $relevant_product;
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