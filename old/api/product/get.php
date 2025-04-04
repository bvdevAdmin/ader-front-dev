<?php
/*
 +=============================================================================
 | 
 | 상품 상세 - 상품 상세 정보 조회
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.10.17
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

include_once(dir_f_api."/common.php");
include_once(dir_f_api."/common/check.php");

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
} else if (isset($_SERVER['HTTP_COUNTRY'])) {
	$country = $_SERVER['HTTP_COUNTRY'];
}

if (isset($country) && isset($product_idx)) {
	$check_result = checkProductSaleFlg($db,"PRD",$product_idx);
	
	$tmp_flg = false;
	if (isset($_POST['tmp_flg'])) {
		$tmp_flg = $_POST['tmp_flg'];
	}
	
	if ($check_result['result'] == false && $tmp_flg == false) {
		$json_result['code'] = 301;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0072', array());
		
		echo json_encode($json_result);
		exit;
	}
	
	$select_product_sql = "
		SELECT
			PR.IDX						AS PRODUCT_IDX,
			PR.ORDERSHEET_IDX			AS ORDERSHEET_IDX,
			PR.PRODUCT_TYPE				AS PRODUCT_TYPE,
			PR.SET_TYPE					AS SET_TYPE,
			PR.PRODUCT_NAME				AS PRODUCT_NAME,
			PR.COLOR					AS COLOR,
			OM.BRAND					AS BRAND,
			PR.PRICE_".$country."		AS PRICE,
			PR.DISCOUNT_".$country."	AS DISCOUNT,
			PR.SALES_PRICE_".$country."	AS SALES_PRICE,
			
			PR.DETAIL_".$country."		AS DETAIL,
			PR.CARE_".$country."		AS CARE,
			PR.MATERIAL_".$country."	AS MATERIAL,
			
			PR.REFUND_MSG_FLG			AS REFUND_MSG_FLG,
			PR.REFUND_MSG_".$country."	AS REFUND_MSG,
			IFNULL(
				PR.REFUND_".$country.",''
			)							AS REFUND,
			
			PR.RELEVANT_IDX				AS RELEVANT_IDX,
			PR.SOLD_OUT_FLG				AS SOLD_OUT_FLG
		FROM
			SHOP_PRODUCT PR
			LEFT JOIN ORDERSHEET_MST OM ON
			PR.ORDERSHEET_IDX = OM.IDX
		WHERE
			PR.IDX = ".$product_idx;
	
	$db->query($select_product_sql);
	
	foreach($db->fetch() as $product_data) {
		$product_idx	= $product_data['PRODUCT_IDX'];
		$ordersheet_idx	= $product_data['ORDERSHEET_IDX'];
		
		$product_type	= $product_data['PRODUCT_TYPE'];
		$set_type		= $product_data['SET_TYPE'];
		
		$img_thumbnail = array();
		$img_main = array();
		
		$option_info = array();
		$stock_status = null;
		
		if (!empty($product_idx) && !empty($ordersheet_idx)) {
			/* 상품 이미지 조회 */
			$product_img = setProductImg($db,$product_idx);
			if ($product_img != null) {
				$img_thumbnail	= $product_img['img_thumb'];
				$img_main		= $product_img['img_main'];
			}
			
			/* 위시리스트 선택 여부 조회 */
			$whish_flg = false;
			if ($member_idx > 0) {
				$whish_cnt = $db->count(
					"WHISH_LIST",
					"
						COUNTRY = '".$country."' AND
						MEMBER_IDX = ".$member_idx." AND
						PRODUCT_IDX = ".$product_idx." AND
						DEL_FLG = FALSE
					"
				);
				
				if ($whish_cnt > 0) {
					$whish_flg = true;
				}
			}
			
			/* 상품 컬러 정보 조회 */
			$product_color = getProductColor($db,$product_idx);
			
			/* 일반상품 옵션정보 조회 */
			if ($product_type == "B") {
				$select_ordersheet_option_sql = "
					SELECT
						OO.IDX				AS OPTION_IDX,
						OO.OPTION_NAME		AS OPTION_NAME
					FROM
						ORDERSHEET_OPTION OO
					WHERE
						OO.ORDERSHEET_IDX = ".$ordersheet_idx."
				";
				
				$db->query($select_ordersheet_option_sql);
				
				foreach($db->fetch() as $option_data) {
					$option_name = $option_data['OPTION_NAME'];
					
					$option_info[] = array(
						'option_idx'		=>$option_data['OPTION_IDX'],
						'option_name'		=>$option_data['OPTION_NAME']
					);
				}
			}
			
			/* 상품 사이즈 정보 조회 */
			$product_size = getProductSize($db,$product_type,$set_type,$product_idx);
			
			$soldout_cnt = 0;
			if (count($product_size) > 0) {
				if ($product_type == "B") {
					
					for ($i=0; $i<count($product_size); $i++) {
						if ($product_size[$i]['stock_status'] == "STSO") {
							$soldout_cnt++;
						}
					}
					
					if (count($product_size) == $soldout_cnt) {
						$stock_status = "STSO";
					}
				} else if ($product_type == "S") {
					for ($i=0; $i<count($product_size); $i++) {
						$set_option_info = $product_size[$i]['set_option_info'];
						
						$tmp_soldout_cnt = 0;
						for ($j=0; $j<count($set_option_info); $j++) {
							if ($set_option_info[$j]['stock_status'] == "STSO") {
								$tmp_soldout_cnt++;
							}
						}
						
						if ($tmp_soldout_cnt == count($set_option_info)) {
							$soldout_cnt++;
						}
					}
					
					if ($soldout_cnt > 0) {
						$stock_status = "STSO";
					}
				}
			}
		}
		
		$json_result['data'] = array(
			'product_idx'		=>$product_data['PRODUCT_IDX'],
			'product_type'		=>$product_data['PRODUCT_TYPE'],
			'set_type'			=>$product_data['SET_TYPE'],
			'img_thumbnail'		=>$img_thumbnail,
			'img_main'			=>$img_main,
			'product_name'		=>$product_data['PRODUCT_NAME'],
			'color'				=>$product_data['COLOR'],
			'brand'				=>$product_data['BRAND'],
			'price'				=>number_format($product_data['PRICE']),
			'discount'			=>$product_data['DISCOUNT'],
			'sales_price'		=>$product_data['SALES_PRICE'],
			'txt_sales_price'	=>number_format($product_data['SALES_PRICE']),
			'material'			=>$product_data['MATERIAL'],
			'detail'			=>$product_data['DETAIL'],
			'care'				=>$product_data['CARE'],
			'refund_msg_flg'	=>$product_data['REFUND_MSG_FLG'],
			'refund_msg'		=>$product_data['REFUND_MSG'],
			'refund'			=>$product_data['REFUND'],
			'relevant_idx'		=>$product_data['RELEVANT_IDX'],
			'sold_out_flg'		=>$product_data['SOLD_OUT_FLG'],
			
			'product_color'		=>$product_color,
			'option_info'		=>$option_info,
			'product_size'		=>$product_size,
			'stock_status'		=>$stock_status,
			'whish_flg'			=>$whish_flg
		);
	}
}

function setProductImg($db,$product_idx) {
	$product_img = null;
	
	$img_thumb	= getProductImg_thumb($db,$product_idx);
	$img_main	= getProductImg_main($db,$product_idx);
	
	if ($img_thumb != null && $img_main != null) {
		$o_cnt = false;
		$p_cnt = false;
		
		for ($i=0; $i<count($img_main); $i++) {
			$img_type = $img_main[$i]['img_type'];
			
			if ($o_cnt == false && $img_type == "O") {
				$o_cnt = true;
				$img_thumb[0]['display_num'] = $img_main[$i]['display_num'];
			}
			
			if ($p_cnt == false && $img_type == "P") {
				$p_cnt = true;
				$img_thumb[1]['display_num'] = $img_main[$i]['display_num'];
			}
		}
	}
	
	$product_img = array(
		'img_thumb'		=>$img_thumb,
		'img_main'		=>$img_main
	);
	
	return $product_img;
}

function getProductImg_thumb($db,$product_idx) {
	$img_thumb = null;
	
	$select_product_img_thumb_sql = "
		(
			SELECT
				S_PI.IMG_LOCATION
			FROM
				PRODUCT_IMG S_PI
			WHERE
				S_PI.PRODUCT_IDX = ? AND
				S_PI.IMG_TYPE = 'O' AND
				S_PI.IMG_SIZE = 'S' AND
				S_PI.DEL_FLG = FALSE
			ORDER BY
				S_PI.IDX DESC
			LIMIT
				0,1
		) UNION (
			SELECT
				S_PI.IMG_LOCATION
			FROM
				PRODUCT_IMG S_PI
			WHERE
				S_PI.PRODUCT_IDX = ? AND
				S_PI.IMG_TYPE = 'P' AND
				S_PI.IMG_SIZE = 'S' AND
				S_PI.DEL_FLG = FALSE
			ORDER BY
				IDX DESC
			LIMIT
				0,1
		)
	";
	
	$db->query($select_product_img_thumb_sql,array($product_idx,$product_idx));
	
	foreach($db->fetch() as $data) {
		$img_thumb[] = array(
			'display_num'	=>0,
			'img_location'	=>$data['IMG_LOCATION']
		);
	}
	
	return $img_thumb;
	
}

function getProductImg_main($db,$product_idx) {
	$img_main = null;
	
	$select_product_img_main_sql = "
		SELECT
			PI.IDX				AS IMG_IDX,
			PI.IMG_TYPE			AS IMG_TYPE,
			PI.IMG_SIZE			AS IMG_SIZE,
			PI.IMG_LOCATION		AS IMG_LOCATION,
			PI.IMG_URL			AS IMG_URL
		FROM
			PRODUCT_IMG PI
		WHERE
			PI.PRODUCT_IDX = ? AND
			PI.IMG_TYPE NOT LIKE 'T%' AND
			PI.IMG_SIZE = 'L' AND
			PI.DEL_FLG = FALSE
		ORDER BY
			PI.IDX ASC
	";
	
	$db->query($select_product_img_main_sql,array($product_idx));
	
	$display_num = 1;
	
	foreach($db->fetch() as $main) {
		$img_main[] = array(
			'display_num'	=>$display_num++,
			'img_idx'		=>$main['IMG_IDX'],
			'img_type'		=>$main['IMG_TYPE'],
			'img_size'		=>$main['IMG_SIZE'],
			'img_location'	=>$main['IMG_LOCATION'],
			'img_url'		=>$main['IMG_URL']
		);
	}
	
	return $img_main;
}

?>