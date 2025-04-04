<?php
/*
 +=============================================================================
 | 
 | 위시 리스트 - 위시리스트 상품 조회
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.10.13
 | 최종 작성   : 양한빈
 | 최종 수정일	: 2024.04.30
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

if (isset($_SERVER['HTTP_COUNTRY']) && isset($_SESSION['MEMBER_IDX'])) {
	$table = "
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
				S_PI.IMG_SIZE = 'M'
			GROUP BY
				S_PI.PRODUCT_IDX
		) AS J_PI ON
		PR.IDX = J_PI.PRODUCT_IDX
	";
	
	$where = "
		WL.COUNTRY = ? AND
		WL.MEMBER_IDX = ? AND
		
		WL.DEL_FLG = FALSE AND
		PR.SALE_FLG = TRUE AND
		PR.DEL_FLG = FALSE
	";
	
	$param_bind = array($_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX']);
	
	$select_wish_list_sql = "
		SELECT
			WL.IDX						AS WHISH_IDX,
			WL.PRODUCT_IDX				AS PRODUCT_IDX,
			PR.PRODUCT_TYPE				AS PRODUCT_TYPE,
			PR.SET_TYPE					AS SET_TYPE,
			J_PI.IMG_LOCATION			AS IMG_LOCATION,
			WL.PRODUCT_NAME				AS PRODUCT_NAME,
			
			PR.PRICE_KR					AS PRICE_KR,
			PR.DISCOUNT_KR				AS DISCOUNT_KR,
			PR.SALES_PRICE_KR			AS SALES_PRICE_KR,

			PR.PRICE_EN					AS PRICE_EN,
			PR.DISCOUNT_EN				AS DISCOUNT_EN,
			PR.SALES_PRICE_EN			AS SALES_PRICE_EN,
			
			PR.COLOR					AS COLOR,
			PR.COLOR_RGB				AS COLOR_RGB,
			
			WL.OPTION_IDX				AS OPTION_IDX,
			WL.OPTION_NAME				AS OPTION_NAME
		FROM
			".$table."
		WHERE
			".$where."
		ORDER BY
			WL.IDX DESC
	";

	$display_num = 0;

	if (isset($last_idx) && $last_idx > 0) {
		$select_wish_list_sql .= " LIMIT ?,12 ";
		
		array_push($param_bind,$last_idx);

		$display_num = $last_idx;
	} else {
		$select_wish_list_sql .= " LIMIT 0,12 ";
	}
	
	$db->query($select_wish_list_sql,$param_bind);
	
	$param_idx_B	= array();
	$param_idx_S	= array();
	
	$wish_info = array();
	
	foreach($db->fetch() as $data) {
		$display_num++;
		
		switch ($data['PRODUCT_TYPE']) {
			case "B" :
				array_push($param_idx_B,$data['PRODUCT_IDX']);
				
				break;
			
			case "S" :
				array_push($param_idx_S,$data['PRODUCT_IDX']);
				
				break;
		}

		$price			= number_format($data['PRICE_'.$_SERVER['HTTP_COUNTRY']]);
		$discount		= $data['DISCOUNT_'.$_SERVER['HTTP_COUNTRY']];
		$sales_price	= number_format($data['SALES_PRICE_'.$_SERVER['HTTP_COUNTRY']]);

		if ($_SERVER['HTTP_COUNTRY'] == "EN") {
			$price			= number_format($data['PRICE_'.$_SERVER['HTTP_COUNTRY']],1);
			$sales_price	= number_format($data['SALES_PRICE_'.$_SERVER['HTTP_COUNTRY']],1);
		}
		
		$wish_info[] = array(
			'wish_idx'			=>$data['WHISH_IDX'],
			'display_num'		=>$display_num,
			'product_idx'		=>$data['PRODUCT_IDX'],
			'product_type'		=>$data['PRODUCT_TYPE'],
			'set_type'			=>$data['SET_TYPE'],
			'img_location'		=>$data['IMG_LOCATION'],
			'product_name'		=>$data['PRODUCT_NAME'],
			
			'price'				=>$price,
			'discount'			=>$discount,
			'sales_price'		=>$sales_price,

			'color'				=>$data['COLOR'],
			'color_rgb'			=>$data['COLOR_RGB'],
			'option_idx'		=>$data['OPTION_IDX'],
			'option_name'		=>$data['OPTION_NAME'],
			'set_type'			=>$data['SET_TYPE']
		);
	}
	
	if (count($wish_info) > 0) {
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
		
		foreach($wish_info as $key => $grid) {
			$param_idx = $grid['product_idx'];
			
			if (count($product_color) > 0 && isset($product_color[$param_idx])) {
				$wish_info[$key]['product_color'] = $product_color[$param_idx];
			}
			
			if (count($product_size_B) > 0 && isset($product_size_B[$param_idx])) {
				$wish_info[$key]['product_size'] = $product_size_B[$param_idx];
			}
			
			if (count($product_size_S) > 0 && isset($product_size_S[$param_idx])) {
				$wish_info[$key]['product_size'] = $product_size_S[$param_idx];
			}
		}
	}
	
	$json_result['data'] = $wish_info;
} else {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0018',array());
	
	echo json_encode($json_result);
	exit;
}

?>