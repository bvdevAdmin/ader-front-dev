<?php
/*
 +=============================================================================
 | 
 | 최근본 상품 리스트 - 상품 리스트 조회
 | -------
 |
 | 최초 작성	: 양한빈
 | 최초 작성일	: 2024.01.20
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/
$db->query('	
	SELECT
			A.GOODS_NO					AS PRODUCT_IDX,
			PR.PRODUCT_TYPE				AS PRODUCT_TYPE,
			IFNULL(
				PR.SET_TYPE,"BS"
			)							AS SET_TYPE,
			(
				SELECT
					S_PI.IMG_LOCATION
				FROM
					PRODUCT_IMG S_PI
				WHERE
					S_PI.PRODUCT_IDX = A.GOODS_NO AND
					S_PI.IMG_TYPE = "P" AND
					S_PI.IMG_SIZE = "M"
				ORDER BY
					S_PI.IDX ASC
				LIMIT
					0,1
			)							AS PRODUCT_IMG,
			PR.PRODUCT_NAME				AS PRODUCT_NAME,
			PR.PRICE_KR, PR.PRICE_EN, PR.PRICE_CN, 
			PR.DISCOUNT_KR, PR.DISCOUNT_EN, PR.DISCOUNT_CN, 
			PR.SALES_PRICE_KR, PR.SALES_PRICE_EN, PR.SALES_PRICE_CN,
			PR.COLOR, PR.COLOR_RGB 
		FROM
			SHOP_GOODS_RECENTLYVIEW A
		LEFT JOIN SHOP_PRODUCT PR ON
			A.GOODS_NO = PR.IDX
		LEFT JOIN ORDERSHEET_MST OM ON
			PR.ORDERSHEET_IDX = OM.IDX
	WHERE
		A.ID = ? 
		AND DATEDIFF(NOW(),A.REG_DATE) < ?
		AND PR.DEL_FLG = FALSE
	ORDER BY
		A.REG_DATE DESC
',array($_SESSION['MEMBER_ID'],RECENTLY_EXPIRE_DATE));

foreach($db->fetch() as $data) {
	$json_result['data'][] = array(
		'product_idx'		=>$data['PRODUCT_IDX'],
		'product_type'		=>$data['PRODUCT_TYPE'],
		'set_type'			=>$data['SET_TYPE'],
		'product_img'		=>$data['PRODUCT_IMG'],
		'product_name'		=>$data['PRODUCT_NAME'],
		'price'				=>number_format($data['PRICE_'.COUNTRY]),
		'discount'			=>$data['DISCOUNT_'.COUNTRY],
		'sales_price'		=>number_format($data['SALES_PRICE_'.COUNTRY]),
		'color'				=>$data['COLOR'],
		'color_rgb'			=>$data['COLOR_RGB'],
		//'option_idx'		=>$data['OPTION_IDX'],
		//'option_name'		=>$data['OPTION_NAME'],
		//'product_qty'		=>$data['PRODUCT_QTY'],
		'product_color'		=> getProductColor($db,$data['PRODUCT_IDX']),
		'product_size'		=> getProductSize($db,$data['PRODUCT_TYPE'],$data['SET_TYPE'],$data['PRODUCT_IDX']),
		'set_type'			=>$data['SET_TYPE']
	);
}
