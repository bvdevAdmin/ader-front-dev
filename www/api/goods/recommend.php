<?php
/*
 +=============================================================================
 | 
 | 상품 목록 - 추천 상품 조회
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

if (isset($_SERVER['HTTP_COUNTRY']) && isset($_SESSION['MEMBER_IDX'])) {
	$recommend_member	= getRecommend_member($db);
	
	$recommend_page		= getRecommend_page($db);
	
	$recommend_option	= getRecommend_option($db);
	
	$recommend_product = array();
	
	$page_idx = checkRecommend($recommend_member,$recommend_page,$recommend_option);
	if (count($page_idx) > 0) {
		$recommend_product = getRecommend_product($db,$page_idx);
	}
	
	$json_result['data'] = $recommend_product;
}

function getRecommend_member($db) {
	$recommend_member = array();
	
	$select_member_custom_sql = "
		SELECT
			MC.MEMBER_GENDER		AS GENDER,
			MC.UPPER_SIZE_IDX		AS UPPER_SIZE_IDX,
			MC.LOWER_SIZE_IDX		AS LOWER_SIZE_IDX,
			MC.SHOES_SIZE_IDX		AS SHOES_SIZE_IDX,
			
			IFNULL(
				J_OI.CNT_ORDER,0
			)						AS CNT_ORDER,
			IFNULL(
				J_PG.PRICE_ORDER,0
			)						AS PRICE_ORDER
		FROM
			MEMBER MB
			
			LEFT JOIN MEMBER_CUSTOM MC ON
			MC.COUNTRY		= ? AND
			MC.MEMBER_IDX	= MB.IDX
			
			LEFT JOIN (
				SELECT
					S_OI.MEMBER_IDX			AS MEMBER_IDX,
					COUNT(S_OI.MEMBER_IDX)	AS CNT_ORDER
				FROM
					ORDER_INFO S_OI
					
					LEFT JOIN V_ORDER_P_CNT V_OP ON
					S_OI.ORDER_CODE = V_OP.ORDER_CODE
				WHERE
					S_OI.COUNTRY = ? AND
					V_OP.CNT_OP > 0
				GROUP BY
					S_OI.MEMBER_IDX
			) AS J_OI ON
			MB.IDX = J_OI.MEMBER_IDX
			
			LEFT JOIN (
				SELECT
					S_OI.MEMBER_IDX			AS MEMBER_IDX,
					IFNULL(
						SUM(S_OI.PRICE_TOTAL),0
					) - 
					IFNULL(
						SUM(S_OC.PRICE_CANCEL),0
					) - 
					IFNULL(
						SUM(S_OF.PRICE_CANCEL),0
					)						AS PRICE_ORDER
				FROM
					ORDER_INFO S_OI
					
					LEFT JOIN ORDER_CANCEL S_OC ON
					S_OI.ORDER_CODE = S_OC.ORDER_CODE
					
					LEFT JOIN ORDER_REFUND S_OF ON
					S_OI.ORDER_CODE = S_OF.ORDER_CODE
				WHERE
					S_OI.COUNTRY = ?
				GROUP BY
					S_OI.MEMBER_IDX
			) AS J_PG ON
			MB.IDX = J_PG.MEMBER_IDX
		WHERE
			MB.IDX = ?
	";
	
	$db->query($select_member_custom_sql,array($_SERVER['HTTP_COUNTRY'],$_SERVER['HTTP_COUNTRY'],$_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX']));
	
	foreach($db->fetch() as $data) {
		$upper_size_idx = array();
		if ($data['UPPER_SIZE_IDX'] != null && strlen($data['UPPER_SIZE_IDX']) > 0) {
			$upper_size_idx = explode(",",$data['UPPER_SIZE_IDX']);
		}
		
		$lower_size_idx = array();
		if ($data['LOWER_SIZE_IDX'] != null && strlen($data['LOWER_SIZE_IDX']) > 0) {
			$lower_size_idx = explode(",",$data['LOWER_SIZE_IDX']);
		}
		
		$shoes_size_idx = array();
		if ($data['SHOES_SIZE_IDX'] != null && strlen($data['SHOES_SIZE_IDX']) > 0) {
			$shoes_size_idx = explode(",",$data['SHOES_SIZE_IDX']);
		}
		
		$recommend_member = array(
			'gender'			=>$data['GENDER'],
			'upper_size_idx'	=>$upper_size_idx,
			'lower_size_idx'	=>$lower_size_idx,
			'shoes_size_idx'	=>$shoes_size_idx,
			
			'order_cnt'			=>$data['CNT_ORDER'],
			'order_price'		=>$data['PRICE_ORDER']
		);
	}
	
	return $recommend_member;
}

function getRecommend_page($db) {
	$recommend_page = array();
	
	$select_page_recommend_sql = "
		SELECT
			RE.IDX				AS PAGE_IDX,
			RE.RECOMMEND_IDX	AS RECOMMEND_IDX,
			RE.UPPER_SIZE_IDX	AS UPPER_SIZE_IDX,
			RE.LOWER_SIZE_IDX	AS LOWER_SIZE_IDX,
			RE.SHOES_SIZE_IDX	AS SHOES_SIZE_IDX
		FROM
			PAGE_RECOMMEND RE
		WHERE
			RE.COUNTRY = ? AND
			RE.ACTIVE_FLG = TRUE AND
			RE.DEL_FLG = FALSE
	";
	
	$db->query($select_page_recommend_sql,array($_SERVER['HTTP_COUNTRY']));
	
	foreach($db->fetch() as $data) {
		$option_idx = array();
		if ($data['RECOMMEND_IDX'] != null && strlen($data['RECOMMEND_IDX']) > 0) {
			$option_idx = explode(",",$data['RECOMMEND_IDX']);
		}
		
		$upper_size_idx = array();
		if ($data['UPPER_SIZE_IDX'] != null && strlen($data['UPPER_SIZE_IDX']) > 0) {
			$upper_size_idx = explode(",",$data['UPPER_SIZE_IDX']);
		}
		
		$lower_size_idx = array();
		if ($data['LOWER_SIZE_IDX'] != null && strlen($data['LOWER_SIZE_IDX']) > 0) {
			$lower_size_idx = explode(",",$data['LOWER_SIZE_IDX']);
		}
		
		$shoes_size_idx = array();
		if ($data['SHOES_SIZE_IDX'] != null && strlen($data['SHOES_SIZE_IDX']) > 0) {
			$shoes_size_idx = explode(",",$data['SHOES_SIZE_IDX']);
		}
		
		$recommend_page[] = array(
			'page_idx'			=>$data['PAGE_IDX'],
			'option_idx'		=>$option_idx,
			'upper_size_idx'	=>$upper_size_idx,
			'lower_size_idx'	=>$lower_size_idx,
			'shoes_size_idx'	=>$shoes_size_idx
		);
	}
	
	return $recommend_page;
}

function getRecommend_option($db) {
	$recommend_option = array();
	
	$select_recommend_option_sql = "
		SELECT
			RO.IDX				AS OPTION_IDX,
			RO.OPTION_NAME		AS OPTION_NAME,
			RO.OPTION_TYPE		AS OPTION_TYPE,
			RO.OPTION_CONDITION	AS OPTION_CONDITION,
			RO.OPTION_VALUE		AS OPTION_VALUE
		FROM
			RECOMMEND_OPTION RO
		ORDER BY
			RO.IDX ASC
	";
	
	$db->query($select_recommend_option_sql);
	
	foreach($db->fetch() as $data) {
		$recommend_option[$data['OPTION_IDX']] = array(
			'option_idx'		=>$data['OPTION_IDX'],
			'option_name'		=>$data['OPTION_NAME'],
			'option_type'		=>$data['OPTION_TYPE'],
			'option_condition'	=>$data['OPTION_CONDITION'],
			'option_value'		=>$data['OPTION_VALUE']
		);
	}
	
	return $recommend_option;
}

function checkRecommend($member,$page,$option) {
	$check_result = array();
	
	foreach($page as $tmp_page) {
		$cnt_option = 0;
		
		$tmp_option = $tmp_page['option_idx'];
		if ($tmp_option != null && count($tmp_option) > 0) {
			foreach($tmp_option as $r_option) {
				$option_name = strtolower($option[$r_option]['option_name']);
				
				switch ($option[$r_option]['option_condition']) {
					case "EQUAL" :
						if ($member[$option_name] == $option[$r_option]['option_value']) {
							$cnt_option++;
						}
						
						break;
					
					case "MORE" :
						if ($member[$option_name] >= $option[$r_option]['option_value']) {
							$cnt_option++;
						}
						
						break;
					
					case "LESS" :
						if ($member[$option_name] <= $option[$r_option]['option_value']) {
							$cnt_option++;
						}
						
						break;
					
					case "OVER" :
						if ($member[$option_name] > $option[$r_option]['option_value']) {
							$cnt_option++;
						}
						
						break;
					
					case "UNDER" :
						if ($member[$option_name] < $option[$r_option]['option_value']) {
							$cnt_option++;
						}
						
						break;
				}
			}
		}
		
		$size_flg = false;
		
		$m_upper = $member['upper_size_idx'];
		$m_lower = $member['lower_size_idx'];
		$m_shoes = $member['shoes_size_idx'];
		
		if (count($m_upper) > 0 || count($m_lower) > 0 || count($m_shoes) > 0) {
			$size_flg = true;
		}
		
		$cnt_size = 0;
		
		$upper_size_idx = $tmp_page['upper_size_idx'];
		if ((is_array($m_upper) && count($m_upper) > 0) && (is_array($upper_size_idx) && count($upper_size_idx))) {
			if (array_intersect($m_upper,$upper_size_idx)) {
				$cnt_size++;
			}
		}
		
		$lower_size_idx = $tmp_page['lower_size_idx'];
		if ((is_array($m_lower) && count($m_lower) > 0) && (is_array($lower_size_idx) && count($lower_size_idx))) {
			if (array_intersect($m_lower,$lower_size_idx)) {
				$cnt_size++;
			}
		}
		
		$shoes_size_idx = $tmp_page['shoes_size_idx'];
		if ((is_array($m_shoes) && count($m_shoes) > 0) && (is_array($shoes_size_idx) && count($shoes_size_idx))) {
			$cnt_size++;
		}
		
		if ($cnt_option == count($tmp_option)) {
			if ($size_flg == true) {
				if ($cnt_size > 0) {
					array_push($check_result,$tmp_page['page_idx']);
				}
			} else {
				array_push($check_result,$tmp_page['page_idx']);
			}
		}
	}
	
	return $check_result;
}

function getRecommend_product($db,$page_idx) {
	$recommend_product = array();
	
	$param_bind = array($_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX']);
	$param_bind = array_merge($param_bind,$page_idx);
	
	$select_recommend_product_sql = "
		SELECT
			RP.PRODUCT_IDX				AS PRODUCT_IDX,
			PR.STYLE_CODE				AS STYLE_CODE,
			PR.PRODUCT_TYPE				AS PRODUCT_TYPE,
			J_PI.IMG_LOCATION			AS IMG_LOCATION,
			PR.PRODUCT_NAME				AS PRODUCT_NAME,
			PR.COLOR					AS COLOR,
			PR.COLOR_RGB				AS COLOR_RGB,
			
			PR.PRICE_KR					AS PRICE_KR,
			PR.DISCOUNT_KR				AS DISCOUNT_KR,
			PR.SALES_PRICE_KR			AS SALES_PRICE_KR,
			
			PR.PRICE_EN					AS PRICE_EN,
			PR.DISCOUNT_EN				AS DISCOUNT_EN,
			PR.SALES_PRICE_EN			AS SALES_PRICE_EN,
			
			J_WL.CNT_WISH				AS CNT_WISH
		FROM
			RECOMMEND_PRODUCT RP
			
			LEFT JOIN SHOP_PRODUCT PR ON
			RP.PRODUCT_IDX = PR.IDX
			
			LEFT JOIN (
				SELECT
					S_PI.PRODUCT_IDX	AS PRODUCT_IDX,
					S_PI.IMG_LOCATION	AS IMG_LOCATION
				FROM
					PRODUCT_IMG S_PI
				WHERE
					S_PI.IMG_TYPE = 'P' AND
					S_PI.IMG_SIZE = 'M' AND
					S_PI.DEL_FLG = FALSE
				GROUP BY
					S_PI.PRODUCT_IDX
			) AS J_PI ON
			PR.IDX = J_PI.PRODUCT_IDX
			
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
		WHERE
			RP.PAGE_IDX IN (".implode(',',array_fill(0,count($page_idx),'?')).") AND
			PR.DEL_FLG = FALSE
		ORDER BY
			PR.IDX DESC
	";
	
	$db->query($select_recommend_product_sql,$param_bind);
	
	$param_idx_B	= array();
	$param_idx_S	= array();
	
	foreach($db->fetch() as $data) {
		switch ($data['PRODUCT_TYPE']) {
			case "B" :
				array_push($param_idx_B,$data['PRODUCT_IDX']);
				
				break;
			
			case "S" :
				array_push($param_idx_S,$data['PRODUCT_IDX']);
				
				break;
		}
		
		$wish_flg = false;
		if ($data['CNT_WISH'] > 0) {
			$wish_flg = true;
		}
		
		$discount		= $data['DISCOUNT_'.$_SERVER['HTTP_COUNTRY']];
		$price			= number_format($data['PRICE_'.$_SERVER['HTTP_COUNTRY']]);
		$sales_price	= number_format($data['SALES_PRICE_'.$_SERVER['HTTP_COUNTRY']]);
		
		if ($_SERVER['HTTP_COUNTRY'] == "EN") {
			$price			= number_format($data['PRICE_'.$_SERVER['HTTP_COUNTRY']],1);
			$sales_price	= number_format($data['SALES_PRICE_'.$_SERVER['HTTP_COUNTRY']],1);
		}
		
		$recommend_product[] = array(
			'product_idx'		=>$data['PRODUCT_IDX'],
			'style_code'		=>$data['STYLE_CODE'],
			'product_type'		=>$data['PRODUCT_TYPE'],
			'product_img'		=>$data['IMG_LOCATION'],
			'product_name'		=>$data['PRODUCT_NAME'],
			'color'				=>$data['COLOR'],
			'color_rgb'			=>$data['COLOR_RGB'],
			'price'				=>$price,
			'discount'			=>$discount,
			'sales_price'		=>$sales_price,
			
			'whish_flg'			=>$wish_flg
		);
	}
	
	$product_color = array();
	if (count($param_idx_B) > 0 || count($param_idx_S) > 0) {
		$product_color	= getProduct_color($db,$_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX'],array_merge($param_idx_B,$param_idx_S));
	}
	
	$product_size_B = array();
	if (count($param_idx_B) > 0) {
		$product_size_B	= getProduct_size_B($db,$param_idx_B);
	}
	
	$product_size_S = array();
	if (count($param_idx_S) > 0) {
		$product_size_S = getProduct_size_S($db,$param_idx_S);
	}
	
	foreach($recommend_product as $key => $product) {
		$param_idx = $product['product_idx'];
		
		if (count($product_color) > 0 && isset($product_color[$param_idx])) {
			$recommend_product[$key]['product_color'] = $product_color[$param_idx];
		}
		
		if (count($product_size_B) > 0 && isset($product_size_B[$param_idx])) {
			$recommend_product[$key]['product_size'] = $product_size_B[$param_idx];
		}
		
		if (count($product_size_S) > 0 && isset($product_size_S[$param_idx])) {
			$recommend_product[$key]['product_size'] = $product_size_S[$param_idx];
		}
	}
	
	return $recommend_product;
}

?>