<?php
/*
 +=============================================================================
 | 
 | 공통 - 관련 상품 리스트 조회
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.10.25
 | 최종 수정  : 양한빈 Bean
 | 최종 수정일	: 2024.04.30
 | 버전		: 1.1
 | 설명		: 
 | 
 +=============================================================================
*/

include_once $_CONFIG['PATH']['API'].'member/order/order-common.php';
include_once $_CONFIG['PATH']['API'].'member/order/order-pg.php';

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

if ($country != null && $member_idx != null) {
	$member_info = getMemberInfo($db,$country,$member_idx);
	
	$page_info = getRecommendPageInfo($db,$country,$member_idx);
	
	$recommend_product = array();
	for ($i=0; $i<count($page_info); $i++) {
		$option_info = $page_info[$i]['option_info'];
		$product_info = $page_info[$i]['product_info'];
		
		$check_result = checkRecommendOption($option_info,$member_info);
		
		if ($check_result == true) {
			for ($j=0; $j<count($product_info); $j++) {
				$json_result['data'][] = array(
					'product_idx'		=>$product_info[$j]['product_idx'],
					'product_type'		=>$product_info[$j]['product_type'],
					'product_img'		=>$product_info[$j]['product_img'],
					'product_name'		=>$product_info[$j]['product_name'],
					'color'				=>$product_info[$j]['color'],
					'price'				=>number_format($product_info[$j]['price']),
					'discount'			=>$product_info[$j]['discount_price'],
					'sales_price'		=>number_format($product_info[$j]['sales_price']),
					
					'product_color'		=>$product_info[$j]['product_color'],
					'product_size'		=>$product_info[$j]['product_size'],
					'whish_flg'			=>$product_info[$j]['whish_flg']
				);
			}
		}
	}
}

function getMemberInfo($db,$country,$member_idx) {
	$member_info = array();
	
	$select_member_custom_sql = "
		SELECT
			MB.IDX					AS MEMBER_IDX,
			MB.MEMBER_GENDER		AS MEMBER_GENDER,
			(
				SELECT
					IFNULL(
						SUM(S_OI.PRICE_TOTAL),0
					) - 
					IFNULL(
						SUM(S_OC.PRICE_CANCEL),0
					) - 
					IFNULL(
						SUM(S_OF.PRICE_CANCEL),0
					)
				FROM
					ORDER_INFO S_OI
					LEFT JOIN ORDER_CANCEL S_OC ON
					S_OI.ORDER_CODE = S_OC.ORDER_CODE
					LEFT JOIN ORDER_EXCHANGE S_OE ON
					S_OI.ORDER_CODE = S_OE.ORDER_CODE
					LEFT JOIN ORDER_REFUND S_OF ON
					S_OI.ORDER_CODE = S_OF.ORDER_CODE
				WHERE
					S_OI.COUNTRY = '".$country."' AND
					S_OI.MEMBER_IDX = ".$member_idx."
			)						AS ORDER_PRICE,
			MC.UPPER_SIZE_IDX		AS UPPER_SIZE_IDX,
			MC.LOWER_SIZE_IDX		AS LOWER_SIZE_IDX,
			MC.SHOES_SIZE_IDX		AS SHOES_SIZE_IDX
		FROM
			MEMBER_".$country." MB
			LEFT JOIN MEMBER_CUSTOM MC ON
			MB.COUNTRY = MC.COUNTRY AND
			MB.IDX = MC.MEMBER_IDX
		WHERE
			MB.COUNTRY = '".$country."' AND
			MB.IDX = ".$member_idx."
	";
	
	$db->query($select_member_custom_sql);
	
	foreach($db->fetch() as $member_data) {
		$order_cnt = $db->count("ORDER_INFO","COUNTRY = '".$country."' AND MEMBER_IDX = ".$member_idx." AND ORDER_STATUS NOT REGEXP 'OC|OE|OR'");
		
		$member_info = array(
			'GENDER'				=>$member_data['MEMBER_GENDER'],
			'ORDER_CNT'				=>$order_cnt,
			'ORDER_PRICE'			=>$member_data['ORDER_PRICE'],
			'upper_size_idx'		=>$member_data['UPPER_SIZE_IDX'],
			'lower_size_idx'		=>$member_data['LOWER_SIZE_IDX'],
			'shoes_size_idx'		=>$member_data['SHOES_SIZE_IDX']
		);
	}
	
	return $member_info;
}

function getRecommendPageInfo($db,$country,$member_idx) {
	$page_info = array();
	
	$select_page_recommend_sql = "
		SELECT
			PR.IDX					AS PAGE_IDX,
			PR.RECOMMEND_IDX		AS RECOMMEND_IDX
		FROM
			PAGE_RECOMMEND PR
		WHERE
			PR.ACTIVE_FLG = TRUE AND
			PR.DEL_FLG = FALSE
		ORDER BY
			PR.IDX DESC
	";
	
	$db->query($select_page_recommend_sql);
	
	foreach($db->fetch() as $page_data) {
		$page_idx = $page_data['PAGE_IDX'];
		$recommend_idx = $page_data['RECOMMEND_IDX'];
		
		$product_info = array();
		if (!empty($page_idx)) {
			$select_recommend_product_sql = "
				SELECT
					RP.PRODUCT_IDX				AS PRODUCT_IDX,
					PR.PRODUCT_TYPE				AS PRODUCT_TYPE,
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
					PR.COLOR					AS COLOR,
					PR.COLOR_RGB				AS COLOR_RGB,
					PR.PRICE_".$country."		AS PRICE,
					PR.DISCOUNT_".$country."	AS DISCOUNT_PRICE,
					PR.SALES_PRICE_".$country."	AS SALES_PRICE
				FROM
					RECOMMEND_PRODUCT RP
					LEFT JOIN SHOP_PRODUCT PR ON
					RP.PRODUCT_IDX = PR.IDX
				WHERE
					RP.PAGE_IDX = ".$page_idx."
				AND
					PR.DEL_FLG = FALSE
				ORDER BY
					RP.DISPLAY_NUM ASC
			";
			
			$db->query($select_recommend_product_sql);
			
			foreach($db->fetch() as $product_data) {
				$product_idx = $product_data['PRODUCT_IDX'];
				
				$whish_flg = false;
				
				if ($member_idx > 0) {
					$whish_cnt = $db->count("WHISH_LIST"," MEMBER_IDX = ".$member_idx." AND PRODUCT_IDX = ".$product_idx." AND DEL_FLG = FALSE ");
					
					if ($whish_cnt > 0) {
						$whish_flg = true;
					}
				}
				
				$product_color = getProductColor($db,$product_idx);
					
				$product_size = getProductSize($db,$product_data['PRODUCT_TYPE'],$product_data['SET_TYPE'],$product_idx);
				
				$product_info[] = array(
					'product_idx'		=>$product_data['PRODUCT_IDX'],
					'product_type'		=>$product_data['PRODUCT_TYPE'],
					'set_type'			=>$product_data['SET_TYPE'],
					'product_img'		=>$product_data['PRODUCT_IMG'],
					'product_name'		=>$product_data['PRODUCT_NAME'],
					'color'				=>$product_data['COLOR'],
					'color_rgb'			=>$product_data['COLOR_RGB'],
					'price'				=>$product_data['PRICE'],
					'discount_price'	=>$product_data['DISCOUNT_PRICE'],
					'sales_price'		=>$product_data['SALES_PRICE'],
					
					'product_color'		=>$product_color,
					'product_size'		=>$product_size,
					'whish_flg'			=>$whish_flg
				);
			}
		}
		
		$option_info = array();
		if (!empty($recommend_idx)) {
			$select_recommend_option_sql = "
				SELECT
					RO.OPTION_NAME			AS OPTION_NAME,
					RO.OPTION_TYPE			AS OPTION_TYPE,
					RO.OPTION_CONDITION		AS OPTION_CONDITION,
					RO.OPTION_VALUE			AS OPTION_VALUE
				FROM
					RECOMMEND_OPTION RO
				WHERE
					RO.IDX IN (".$recommend_idx.")
			";
			
			$db->query($select_recommend_option_sql);
			
			foreach($db->fetch() as $option_data) {
				$option_info[] = array(
					'option_name'			=>$option_data['OPTION_NAME'],
					'option_type'			=>$option_data['OPTION_TYPE'],
					'option_condition'		=>$option_data['OPTION_CONDITION'],
					'option_value'			=>$option_data['OPTION_VALUE']
				);
			}
		}
		
		$page_info[] = array(
			'page_idx'			=>$page_data['PAGE_IDX'],
			'product_info'		=>$product_info,
			'option_info'		=>$option_info
		);
	}
	
	return $page_info;
}

function checkRecommendOption($option_info,$member_info) {
	$check_result = false;
	
	$check_cnt = 0;
	
	for ($i=0; $i<count($option_info); $i++) {
		$option_name = $option_info[$i]['option_name'];
		$option_condition = $option_info[$i]['option_condition'];
		$option_value = $option_info[$i]['option_value'];
		
		switch ($option_condition) {
			case "EQUAL" :
				if ($option_value == $member_info[$option_name]) {
					$check_cnt++;
				}
				
				break;
			
			case "MORE" :
				if ($option_value <= $member_info[$option_name]) {
					$check_cnt++;
				}
				
				break;
			
			case "LESS" :
				if ($option_value >= $member_info[$option_name]) {
					$check_cnt++;
				}
				
				break;
			
			case "OVER" :
				if ($option_value < $member_info[$option_name]) {
					$check_cnt++;
				}
				
				break;
			
			case "UNDER" :
				if ($option_value > $member_info[$option_name]) {
					$check_cnt++;
				}
				
				break;
		}
	}
	
	if ($check_cnt == count($option_info)) {
		$check_result = true;
	}
	
	return $check_result;
}

?>