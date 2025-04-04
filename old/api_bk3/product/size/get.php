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

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
} else if (isset($_SERVER['HTTP_COUNTRY'])) {
	$country = $_SERVER['HTTP_COUNTRY'];
}

$size_type = null;
if (isset($_POST['size_type'])) {
	$size_type = $_POST['size_type'];
}

if (isset($country) && isset($product_type) && isset($product_idx)) {
	$ext_file_name = "";
	if ($size_type == "M") {
		$ext_file_name = "_mo";
	}
	
	$param_product_idx	= array();
	$set_product_name	= array();
	
	$param_product = setParamProduct($db,$product_type,$product_idx);
	if ($param_product != null) {
		$param_product_idx	= $param_product['param_product_idx'];
		$set_product_name	= $param_product['set_product_name'];
	}
	
	$size_guide_info = array();
	
	/* 사이즈 가이드 이미지/옵션 정보 조회처리 */
	if (count($param_product_idx) > 0) {
		for ($i=0; $i<count($param_product_idx); $i++) {
			$product_name = null;
			if ($product_type == "S") {
				$product_name = $set_product_name[$i];
			}
			
			$img_file_name	= "";
			$model			= "";
			$model_wear		= "";
			$svg_web		= "";
			$svg_mob		= "";
			$dimensions		= "";
			
			/* 옵션 사이즈 자율입력 체크 */
			$option_size_txt = getOptionSizeTxt($db,$product_idx);
			if ($option_size_txt != null && isset($option_size_txt['option_size_txt_'.$country])) {
				$size_guide_info[] = array(
					'product_idx'		=>$param_product_idx[$i],
					'product_name'		=>$product_name,
					'option_size_txt'	=>$option_size_txt['option_size_txt_'.$country]
				);
			} else {
				/* 사이즈 가이드 이미지/사이즈 텍스트 조회처리 */
				$size_info		= getProductSizeGuide($db,$country,$param_product_idx[$i]);
				/* 옵션 이름/옵션 사이즈 조회처리 */
				$option_info	= getProductSizeOption($db,$param_product_idx[$i]);
				
				if ($size_info != null && count($option_info) > 0) {
					if (isset($size_info['img_file_name'])) {
						$img_file_name	= "/size/".$size_info['img_file_name'].$ext_file_name.".svg";
					}
					
					if (isset($size_info['model'])) {
						$model			= $size_info['model'];
					}
					
					if (isset($size_info['model_wear'])) {
						$model_wear		= $size_info['model_wear'];
					}
					
					if (isset($size_info['svg_web'])) {
						$svg_web		= $size_info['svg_web'];
					}
					
					if (isset($size_info['svg_mob'])) {
						$svg_mob		= $size_info['svg_mob'];
					}
					
					$dimensions = setProductDimensions($size_info,$option_info);
				}
				
				$size_guide_info[] = array(
					'product_idx'		=>$param_product_idx[$i],
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
	
	$json_result['data'] = $size_guide_info;
}

function setParamProduct($db,$product_type,$product_idx) {
	$param_product = null;
	
	$param_product_idx = array();
	$set_product_name = array();
	
	if ($product_type == "B") {
		array_push($param_product_idx,$product_idx);
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
				SP.SET_PRODUCT_IDX = ".$product_idx."
			GROUP BY
				PR.STYLE_CODE
		";
		
		$db->query($select_set_product_sql);
		
		foreach($db->fetch() as $data) {
			array_push($param_product_idx,$data['PRODUCT_IDX']);
			array_push($set_product_name,$data['PRODUCT_NAME']);
		}
	}
	
	if (
		($product_type == "B" && count($param_product_idx) > 0) ||
		($product_type == "S" && count($param_product_idx) > 0 && count($set_product_name) > 0)
	) {
		$param_product = array(
			'param_product_idx'	=>$param_product_idx,
			'set_product_name'	=>$set_product_name
		);
	}
	
	return $param_product;
}

function getOptionSizeTxt($db,$product_idx) {
	$option_size_txt = null;
	
	$shop_product = $db->get(
		"SHOP_PRODUCT",
		"
			IDX = ? AND
			(
				OPTION_SIZE_TXT_KR IS NOT NULL AND
				OPTION_SIZE_TXT_EN IS NOT NULL AND
				OPTION_SIZE_TXT_CN IS NOT NULL
			)
		",
		array($product_idx)
	);
	
	if ($shop_product != null) {
		$option_size_txt = array(
			'option_size_txt_KR'		=>$shop_product[0]['OPTION_SIZE_TXT_KR'],
			'option_size_txt_EN'		=>$shop_product[0]['OPTION_SIZE_TXT_EN'],
			'option_size_txt_CN'		=>$shop_product[0]['OPTION_SIZE_TXT_CN'],
		);
	}
	
	return $option_size_txt;
}

function getProductSizeGuide($db,$country,$product_idx) {
	$size_info = null;
	
	$select_size_guide_sql = "
		SELECT
			SG.IMG_FILE_NAME	AS IMG_FILE_NAME,
			
			OM.MODEL			AS MODEL,
			OM.MODEL_WEAR		AS MODEL_WEAR,
			
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
			LEFT JOIN ORDERSHEET_MST OM ON
			SG.CATEGORY_TYPE = OM.SIZE_GUIDE_CATEGORY
			
			LEFT JOIN SIZE_GUIDE_SVG SV ON
			SG.CATEGORY_TYPE = SV.SIZE_CATEGORY
		WHERE
			SG.COUNTRY = '".$country."' AND
			OM.IDX = (
				SELECT
					PR.ORDERSHEET_IDX
				FROM
					SHOP_PRODUCT PR
				WHERE
					PR.IDX = ".$product_idx."
			)
	";
	
	$db->query($select_size_guide_sql);
	
	foreach ($db->fetch() as $size_data) {
		$size_info = array(
			'img_file_name'	=>$size_data['IMG_FILE_NAME'],
			
			'model'			=>$size_data['MODEL'],
			'model_wear'	=>$size_data['MODEL_WEAR'],
			
			'size_title_1'	=>$size_data['SIZE_TITLE_1'],
			'size_title_2'	=>$size_data['SIZE_TITLE_2'],
			'size_title_3'	=>$size_data['SIZE_TITLE_3'],
			'size_title_4'	=>$size_data['SIZE_TITLE_4'],
			'size_title_5'	=>$size_data['SIZE_TITLE_5'],
			'size_title_6'	=>$size_data['SIZE_TITLE_6'],
			
			'size_desc_1'	=>$size_data['SIZE_DESC_1'],
			'size_desc_2'	=>$size_data['SIZE_DESC_2'],
			'size_desc_3'	=>$size_data['SIZE_DESC_3'],
			'size_desc_4'	=>$size_data['SIZE_DESC_4'],
			'size_desc_5'	=>$size_data['SIZE_DESC_5'],
			'size_desc_6'	=>$size_data['SIZE_DESC_6'],
			
			'svg_web'		=>$size_data['SVG_WEB'],
			'svg_mob'		=>$size_data['SVG_MOB']
		);
	}
	
	return $size_info;
}

function getProductSizeOption($db,$product_idx) {
	$option_info = array();
	
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
			ORDERSHEET_OPTION OO
			LEFT JOIN SHOP_PRODUCT PR ON
			OO.ORDERSHEET_IDX = PR.ORDERSHEET_IDX
		WHERE
			PR.IDX = ".$product_idx."
		ORDER BY
			OO.IDX
	";
	
	$db->query($select_option_size_sql);
	
	foreach($db->fetch() as $option_data) {
		$option_info[] = array(
			'option_name'		=>$option_data['OPTION_NAME'],
			'option_size_1'		=>$option_data['OPTION_SIZE_1'],
			'option_size_2'		=>$option_data['OPTION_SIZE_2'],
			'option_size_3'		=>$option_data['OPTION_SIZE_3'],
			'option_size_4'		=>$option_data['OPTION_SIZE_4'],
			'option_size_5'		=>$option_data['OPTION_SIZE_5'],
			'option_size_6'		=>$option_data['OPTION_SIZE_6']
		);
	}
	
	return $option_info;
}

function setProductDimensions($size_info,$option_info) {
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

?>