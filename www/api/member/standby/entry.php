<?php
/*
 +=============================================================================
 | 
 | 마이페이지_스탠바이 - 응모한 스탠바이 리스트 정보 조회 // /var/www/www/api/mypage/standby/entry/get.php
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2023.01.15
 | 최종 수정    : 양한빈
 | 최종 수정일	: 2024.05.07
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

if (!isset($country) || $member_idx == 0) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	
	echo json_encode($json_result);
	exit;
}

if(isset($country) && $member_idx > 0){
	$select_entry_sql = "
		SELECT
			ES.IDX					AS ENTRY_IDX,
			PS.IDX					AS STANDBY_IDX,
			PS.THUMBNAIL_LOCATION	AS THUMBNAIL_LOCATION,
			PS.TITLE				AS TITLE,
			DATE_FORMAT(
				PS.ENTRY_START_DATE,'%Y.%m.%d %H:%i'
			)						AS ENTRY_START_DATE,
			DATE_FORMAT(
				PS.ENTRY_END_DATE,'%Y.%m.%d %H:%i'
			)						AS ENTRY_END_DATE,
			DATE_FORMAT(
				PS.PURCHASE_START_DATE,'%Y.%m.%d %H:%i'
			)						AS PURCHASE_START_DATE,
			DATE_FORMAT(
				PS.PURCHASE_END_DATE,'%Y.%m.%d %H:%i'
			)						AS PURCHASE_END_DATE,
			DATE_FORMAT(
				PS.ORDER_LINK_DATE,'%Y.%m.%d %H:%i'
			)						AS ORDER_LINK_DATE,
			DATE_FORMAT(
				ES.CREATE_DATE,'%Y.%m.%d %H:%i'
			)						AS APPLY_DATE,
			PS.DISPLAY_FLG			AS DISPLAY_FLG,
			ES.PURCHASE_FLG			AS PURCHASE_FLG,

			CASE
				WHEN
					PS.PURCHASE_END_DATE < NOW()
					THEN 
						'구매 종료'
				WHEN
					PS.PURCHASE_END_DATE > NOW() AND
					PS.PURCHASE_START_DATE > NOW()
					THEN
						'구매대기'
				WHEN
					PS.PURCHASE_END_DATE > NOW() AND
					PS.PURCHASE_START_DATE < NOW()
					THEN
						'구매 진행중'
				ELSE NULL
			END						AS PURCHASE_STATUS,
			ES.ORDER_IDX			AS ORDER_IDX
		FROM
			PAGE_STANDBY PS
			LEFT JOIN ENTRY_STANDBY ES ON
			PS.IDX = ES.STANDBY_IDX
		WHERE
			ES.COUNTRY = '".$country."' AND
			ES.MEMBER_IDX = ".$member_idx." AND
			ES.DEL_FLG = FALSE
		ORDER BY 
			ES.CREATE_DATE DESC
	";
	
	$db->query($select_entry_sql);
	
	foreach($db->fetch() as $entry_data) {
		$get_standby_product_sql = "
			SELECT
				PR.IDX					AS PRODUCT_IDX,
				PR.PRODUCT_CODE			AS PRODUCT_CODE,
				PR.PRODUCT_NAME			AS PRODUCT_NAME,
				PR.COLOR				AS COLOR,
				PR.SALES_PRICE_KR		AS SALES_PRICE_KR,
				PR.SALES_PRICE_EN		AS SALES_PRICE_EN,
				PR.SALES_PRICE_CN		AS SALES_PRICE_CN,
				(
					SELECT
						S_PI.IMG_LOCATION
					FROM
						PRODUCT_IMG S_PI
					WHERE
						S_PI.PRODUCT_IDX = PR.IDX AND
						S_PI.IMG_TYPE = 'P' AND
						S_PI.IMG_SIZE = 'S'
					LIMIT
						0,1
				)						AS IMG_LOCATION
			FROM
				STANDBY_PRODUCT SP	LEFT JOIN
				SHOP_PRODUCT PR
			ON
				SP.PRODUCT_IDX = PR.IDX
			WHERE
				SP.STANDBY_IDX = ".$entry_data['STANDBY_IDX']."
			AND
				PR.DEL_FLG = FALSE
		";
		
		$db->query($get_standby_product_sql);
		
		$standby_product_info = array();
		
		foreach($db->fetch() as $product_data){
			$product_option_info = array();
			if($entry_data['ORDER_IDX'] != null){
				$get_order_product_sql = "
					SELECT
						DISTINCT OP.OPTION_NAME
					FROM
						ORDER_INFO OI LEFT JOIN
						ORDER_PRODUCT OP
					ON
						OI.IDX = OP.ORDER_IDX
					WHERE
						OI.IDX = ".$entry_data['ORDER_IDX']."
					AND
						OP.PRODUCT_CODE = ".$product_data['PRODUCT_CODE']."
					GROUP BY
						OP.OPTION_NAME
				";
				$db->query($get_order_product_sql);
				
				foreach($db->fetch() as $option_data){
					$product_option_info[] = array(
						'OPTION_NAME'	=> $option_data['OPTION_NAME']
					);
				}
			}
			
			$img_location = '/images/default_product_img.jpg';
			if ($product_data['IMG_LOCATION'] != null) {
				$img_location = $product_data['IMG_LOCATION'];
			}
			
			$standby_product_info[] = array(
				'product_idx' 		=> $product_data['PRODUCT_IDX'],
				'product_name' 		=> $product_data['PRODUCT_NAME'],
				'img_location' 		=> $img_location ,
				'color'				=> $product_data['COLOR'],
				'sales_price_kr'	=> number_format($product_data['SALES_PRICE_KR']),
				'sales_price_en'	=> number_format($product_data['SALES_PRICE_EN']),
				'sales_price_cn'	=> number_format($product_data['SALES_PRICE_CN']),
				'product_option'	=> $product_option_info,
				'status'			=> count($product_option_info)>0?'구매완료':'미구매'
			);
		}
		
		$json_result['data'][] = array(
			'entry_idx'				=>$entry_data['ENTRY_IDX'],
			'standby_idx'			=>$entry_data['STANDBY_IDX'],
			'thumbnail_location'	=>$entry_data['THUMBNAIL_LOCATION'],
			'title'					=>$entry_data['TITLE'],
			'entry_start_date'		=>$entry_data['ENTRY_START_DATE'],
			'entry_end_date'		=>$entry_data['ENTRY_END_DATE'],
			'purchase_start_date'	=>$entry_data['PURCHASE_START_DATE'],
			'purchase_end_date'		=>$entry_data['PURCHASE_END_DATE'],
			'order_link_date'		=>$entry_data['ORDER_LINK_DATE'],
			'apply_date'			=>$entry_data['APPLY_DATE'],
			'display_flg'			=>$entry_data['DISPLAY_FLG'],
			'purchase_flg'			=>$entry_data['PURCHASE_FLG'],
			'purchase_status'		=>$entry_data['PURCHASE_STATUS'],
			'order_idx' 			=>$entry_data['ORDER_IDX']==null?'NPC':$entry_data['ORDER_IDX'],
			'standby_product_info'	=>$standby_product_info
		);
	}
}

?>