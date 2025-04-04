<?php
/*
 +=============================================================================
 | 
 | 상품 리스트 - 상품 사이즈 정보 조회
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.02.17
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

$size_type = isset($size_type) ? $size_type : null;

if (isset($country) && isset($product_type) && isset($product_idx)) {
	$ext_file_name = "";
	if ($size_type == "M") {
		$ext_file_name = "_mo";
	}
	
	$param_idx	= array();
	$set_name	= array();
	
	$param_product = setParam_product($db,$product_type,$product_idx);
	if ($param_product != null) {
		$param_idx	= $param_product['param_idx'];
		$set_name	= $param_product['set_name'];
	}
	
	$size_guide = array();
	
	/* 사이즈 가이드 이미지/옵션 정보 조회처리 */
	if (count($param_idx) > 0) {
		foreach($param_idx as $key => $tmp_idx) {
			$product_name = null;
			if ($product_type == "S") {
				$product_name = $set_name[$i];
			}
			
			$img_file_name	= "";
			$model			= "";
			$model_wear		= "";
			$svg_web		= "";
			$svg_mob		= "";
			$dimensions		= "";
			
			/* 옵션 사이즈 자율입력 체크 */
			$option_size_txt = getOption_size_txt($db,$country,$tmp_idx);
			if ($option_size_txt != null && strlen($option_size_txt) > 0) {
				$size_guide[] = array(
					'product_idx'		=>$tmp_idx,
					'product_name'		=>$product_name,
					'option_size_txt'	=>$option_size_txt
				);
			} else {
				/* 사이즈 가이드 이미지/사이즈 텍스트 조회처리 */
				$size_guide = getProduct_size_guide($db,$country,$tmp_idx);
				
				/* 옵션 이름/옵션 사이즈 조회처리 */
				$option_size = getProduct_option_size($db,$tmp_idx);
				
				if ($size_guide != null && count($option_size) > 0) {
					if (isset($size_guide['img_file_name'])) {
						$img_file_name	= "/size/".$size_guide['img_file_name'].$ext_file_name.".svg";
					}
					
					if (isset($size_guide['model'])) {
						$model			= $size_guide['model'];
					}
					
					if (isset($size_guide['model_wear'])) {
						$model_wear		= $size_guide['model_wear'];
					}
					
					if (isset($size_guide['svg_web'])) {
						$svg_web		= $size_guide['svg_web'];
					}
					
					if (isset($size_guide['svg_mob'])) {
						$svg_mob		= $size_guide['svg_mob'];
					}
					
					$dimensions = setProduct_dimensions($size_guide,$option_size);
				}
				
				$size_guide[] = array(
					'product_idx'		=>$tmp_idx,
					'product_name'		=>$product_name,
					'img_file_name'		=>$img_file_name,
					'model'				=>$model,
					'model_wear'		=>$model_wear,
					'svg_web'			=>$svg_web,
					'svg_mob'			=>$svg_mob,
					
					'dimensions'		=>$dimensions
				);
			}
		}
	}
	
	$json_result['data'] = $size_guide;
}

function setParam_product($db,$product_type,$product_idx) {
	$param_product = null;
	
	$param_idx	= array();
	$set_name	= array();
	
	if ($product_type == "B") {
		array_push($param_idx,$product_idx);
	} else if ($product_type == "S") {
		$select_set_product_sql = "
			SELECT
				SP.PRODUCT_IDX		AS PRODUCT_IDX,
				PR.PRODUCT_NAME		AS PRODUCT_NAME
			FROM
				SET_PRODUCT SP
				LEFT JOIN SHOP_PRODUCT PR ON
				SP.PRODUCT_IDX = PR.IDX
			WHERE
				SP.SET_PRODUCT_IDX = ?
			GROUP BY
				PR.STYLE_CODE
		";
		
		$db->query($select_set_product_sql,array($product_idx));
		
		foreach($db->fetch() as $data) {
			array_push($param_idx,$data['PRODUCT_IDX']);
			array_push($set_name,$data['PRODUCT_NAME']);
		}
	}
	
	if (
		($product_type == "B" && count($param_idx) > 0) ||
		($product_type == "S" && count($param_idx) > 0 && count($set_name) > 0)
	) {
		$param_product = array(
			'param_idx'		=>$param_idx,
			'set_name'		=>$set_name
		);
	}
	
	return $param_product;
}

function getOption_size_txt($db,$country,$product_idx) {
	$option_size_txt = null;
	
	$shop_product = $db->get(
		"SHOP_PRODUCT",
		"
			IDX = ? AND
			(
				OPTION_SIZE_TXT_KR IS NOT NULL AND
				OPTION_SIZE_TXT_EN IS NOT NULL
			)
		",
		array($product_idx)
	);
	
	if ($shop_product != null) {
		if ($shop_product[0]['OPTION_SIZE_TXT_'.$country] != null) {
			$option_size_txt = $shop_product[0]['OPTION_SIZE_TXT_'.$country];
		}
	}
	
	return $option_size_txt;
}

function getProduct_size_guide($db,$country,$product_idx) {
	$size_info = null;
	
	$select_size_guide_sql = "
		SELECT
			SG.IMG_FILE_NAME	AS IMG_FILE_NAME,
			
			PR.MODEL			AS MODEL,
			PR.MODEL_WEAR		AS MODEL_WEAR,
			
			SG.SIZE_TITLE_1		AS SIZE_TITLE_1,
			SG.SIZE_TITLE_2		AS SIZE_TITLE_2,
			SG.SIZE_TITLE_3		AS SIZE_TITLE_3,
			SG.SIZE_TITLE_4		AS SIZE_TITLE_4,
			SG.SIZE_TITLE_5		AS SIZE_TITLE_5,
			SG.SIZE_TITLE_6		AS SIZE_TITLE_6,
			
			SG.SIZE_DESC_1		AS SIZE_DESC_1,
			SG.SIZE_DESC_2		AS SIZE_DESC_2,
			SG.SIZE_DESC_3		AS SIZE_DESC_3,
			SG.SIZE_DESC_4		AS SIZE_DESC_4,
			SG.SIZE_DESC_5		AS SIZE_DESC_5,
			SG.SIZE_DESC_6		AS SIZE_DESC_6,
			
			SV.SVG_WEB			AS SVG_WEB,
			SV.SVG_MOB			AS SVG_MOB
		FROM
			SIZE_GUIDE SG

			LEFT JOIN SIZE_GUIDE_SVG SV ON
			SG.CATEGORY_TYPE = SV.SIZE_CATEGORY

			LEFT JOIN SHOP_PRODUCT PR ON
			SG.CATEGORY_TYPE = PR.SIZE_GUIDE_CATEGORY
		WHERE
			SG.COUNTRY = ? AND
			PR.IDX = ?
	";
	
	$db->query($select_size_guide_sql,array($country,$product_idx));
	
	foreach ($db->fetch() as $data) {
		$size_info = array(
			'img_file_name'	=>$data['IMG_FILE_NAME'],
			
			'model'			=>$data['MODEL'],
			'model_wear'	=>$data['MODEL_WEAR'],
			
			'size_title_1'	=>$data['SIZE_TITLE_1'],
			'size_title_2'	=>$data['SIZE_TITLE_2'],
			'size_title_3'	=>$data['SIZE_TITLE_3'],
			'size_title_4'	=>$data['SIZE_TITLE_4'],
			'size_title_5'	=>$data['SIZE_TITLE_5'],
			'size_title_6'	=>$data['SIZE_TITLE_6'],
			
			'size_desc_1'	=>$data['SIZE_DESC_1'],
			'size_desc_2'	=>$data['SIZE_DESC_2'],
			'size_desc_3'	=>$data['SIZE_DESC_3'],
			'size_desc_4'	=>$data['SIZE_DESC_4'],
			'size_desc_5'	=>$data['SIZE_DESC_5'],
			'size_desc_6'	=>$data['SIZE_DESC_6'],
			
			'svg_web'		=>$data['SVG_WEB'],
			'svg_mob'		=>$data['SVG_MOB']
		);
	}
	
	return $size_info;
}

function getProduct_option_size($db,$product_idx) {
	$option_size = array();
	
	$select_option_size_sql = "
		SELECT
			OO.OPTION_NAME			AS OPTION_NAME,
			OO.OPTION_SIZE_1		AS OPTION_SIZE_1,
			OO.OPTION_SIZE_2		AS OPTION_SIZE_2,
			OO.OPTION_SIZE_3		AS OPTION_SIZE_3,
			OO.OPTION_SIZE_4		AS OPTION_SIZE_4,
			OO.OPTION_SIZE_5		AS OPTION_SIZE_5,
			OO.OPTION_SIZE_6		AS OPTION_SIZE_6
		FROM
			SHOP_OPTION OO
			
			LEFT JOIN SHOP_PRODUCT PR ON
			OO.PRODUCT_IDX = PR.IDX
		WHERE
			PR.IDX = ?
		ORDER BY
			OO.IDX ASC
	";
	
	$db->query($select_option_size_sql,array($product_idx));
	
	foreach($db->fetch() as $data) {
		$option_size[] = array(
			'option_name'		=>$data['OPTION_NAME'],
			'option_size_1'		=>$data['OPTION_SIZE_1'],
			'option_size_2'		=>$data['OPTION_SIZE_2'],
			'option_size_3'		=>$data['OPTION_SIZE_3'],
			'option_size_4'		=>$data['OPTION_SIZE_4'],
			'option_size_5'		=>$data['OPTION_SIZE_5'],
			'option_size_6'		=>$data['OPTION_SIZE_6']
		);
	}
	
	return $option_size;
}

function setProduct_dimensions($size_info,$option_info) {
	$dimensions = array();
	
	for ($j=0; $j<count($option_info); $j++) {
		$option_size = array();
		
		for ($k=1; $k<=6; $k++) {
			$size_title	= $size_info['size_title_'.$k];
			$size_desc	= $size_info['size_desc_'.$k];
			
			$option_value = $option_info[$j]['option_size_'.$k];
			
			if ($size_title != null && $size_desc != null && $option_value != null) {
				$option_size[] = array(
					'title'		=>$size_title,
					'desc'		=>$size_desc,
					'value'		=>$option_value
				);
			}
		}
		
		$dimensions[$option_info[$j]['option_name']] = $option_size;
	}
	
	return $dimensions;
}
