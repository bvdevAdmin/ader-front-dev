<?php
/*
 +=============================================================================
 | 
 | 마이페이지 정보 취득
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2023.01.09
 | 최종 수정	: 양한빈
 | 최종 수정일	: 2024.05.07
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

if (isset($_SERVER['HTTP_COUNTRY']) && isset($_SESSION['MEMBER_IDX']) && isset($list_type)) {
	$table = "
		REORDER_INFO RI
		
		LEFT JOIN SHOP_PRODUCT PR ON
		RI.PRODUCT_IDX = PR.IDX
		
		LEFT JOIN SHOP_OPTION OO ON
		RI.OPTION_IDX = OO.IDX
		
		LEFT JOIN (
			SELECT
				S_PI.PRODUCT_IDX		AS PRODUCT_IDX,
				S_PI.IMG_LOCATION		AS IMG_LOCATION
			FROM
				PRODUCT_IMG S_PI
			WHERE
				S_PI.IMG_TYPE = 'P' AND
				S_PI.IMG_SIZE = 'S' AND
				S_PI.DEL_FLG = FALSE
			GROUP BY
				S_PI.PRODUCT_IDX
		) AS J_PI ON
		RI.PRODUCT_IDX = J_PI.PRODUCT_IDX
	";
	
	$where = "
		RI.COUNTRY = ? AND
		RI.MEMBER_IDX = ? AND
		RI.PARENT_IDX = 0
	";
	
	$param_bind = array($_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX']);
	
	switch ($list_type) {
		case 'apply':
			$where .= "
				AND (
					(RI.NOTICE_FLG = FALSE OR (RI.NOTICE_FLG = TRUE AND NOW() < RI.NOTICE_DATE)) AND
					RI.DEL_FLG = FALSE
				)
			";
			
			break;
		
		case 'alarm':
			$where .= "
				AND (
					(RI.NOTICE_FLG = TRUE AND NOW() >= RI.NOTICE_DATE) AND
					RI.DEL_FLG = FALSE
				)
			";
			
			break;
		
		case 'cancel':
			$where .= " AND (RI.DEL_FLG = TRUE) ";
			
			break;
	}
	
	$json_result = array(
		'total'		=>$db->count($table,$where,$param_bind),
		'page'		=>$page
	);
	
	$select_reorder_info_sql = "
		SELECT
			RI.IDX					AS REORDER_IDX,
			PR.IDX					AS PRODUCT_IDX,
			PR.PRODUCT_TYPE			AS PRODUCT_TYPE,
			PR.PRODUCT_NAME			AS PRODUCT_NAME,
			J_PI.IMG_LOCATION		AS IMG_LOCATION,
			PR.COLOR				AS COLOR,
			PR.COLOR_RGB			AS COLOR_RGB,
			IFNULL(
				OO.OPTION_NAME,'Set'
			)						AS OPTION_NAME,
			
			PR.PRICE_KR				AS PRICE_KR,
			PR.DISCOUNT_KR			AS DISCOUNT_KR,
			PR.SALES_PRICE_KR		AS SALES_PRICE_KR,
			
			PR.PRICE_KR				AS PRICE_EN,
			PR.DISCOUNT_KR			AS DISCOUNT_EN,
			PR.SALES_PRICE_KR		AS SALES_PRICE_EN,
			
			DATE_FORMAT(
				RI.CREATE_DATE,
				'%Y.%m.%d'
			)						AS CREATE_DATE
		FROM
			".$table."
		WHERE
			".$where."
		ORDER BY
			RI.IDX DESC
	";
	
	if($rows != null && $page != null){
		$limit_start = (intval($page)-1)*$rows;
		
		$select_reorder_info_sql .= " LIMIT ?,? ";
		
		array_push($param_bind,$limit_start);
		array_push($param_bind,$rows);
	}

	$db->query($select_reorder_info_sql,$param_bind);

	foreach($db->fetch() as $data){
		$json_result['data'][] = array(
			'reorder_idx'		=>$data['REORDER_IDX'],
			'product_idx'		=>$data['PRODUCT_IDX'],
			'product_type'		=>$data['PRODUCT_TYPE'],
			'img_location'		=>$data['IMG_LOCATION'],
			'product_name'		=>$data['PRODUCT_NAME'],
			'color'				=>$data['COLOR'],
			'color_rgb'			=>$data['COLOR_RGB'],
			'option_name'		=>$data['OPTION_NAME'],
			'price'				=>number_format($data['PRICE_'.$country]),
			'discount'			=>$data['PRICE_'.$country],
			'sales_price_kr'	=>number_format($data['SALES_PRICE_'.$country]),
			'create_date'		=>$data['CREATE_DATE']
		);
	}
} else {
	$json_result['code'] = 401;
    $json_result['msg'] = getMsgToMsgCode($db, $_SERVER['HTTP_COUNTRY'], 'MSG_B_ERR_0018', array());
	
	echo json_encode($json_result);
	exit;	
}

?>