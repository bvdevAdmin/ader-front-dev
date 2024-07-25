<?php
/*
 +=============================================================================
 | 
 | 마이페이지 정보 취득
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2023.01.09
 | 최종 수정    : 양한빈
 | 최종 수정일	: 2024.05.07
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

$member_idx = 0;
if(isset($_SESSION['MEMBER_IDX'])){
	$member_idx = $_SESSION['MEMBER_IDX'];
}

if(!isset($country) || $member_idx == 0){
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	
	echo json_encode($json_result);
	exit;
}

if (isset($country) && $member_idx > 0 && isset($list_type)) {
	$where = " RE.COUNTRY = '".$country."' AND RE.MEMBER_IDX = ".$member_idx." AND RE.PARENT_IDX = 0 ";
	
	switch ($list_type) {
		case 'apply':
			$where .= "
				AND (RE.DEL_FLG = FALSE AND RE.REORDER_STATUS = FALSE)
			";
			
			break;
		
		case 'alarm':
			$where .= "
				AND (RE.DEL_FLG = FALSE AND RE.REORDER_STATUS = TRUE)
			";
			
			break;
		
		case 'cancel':
			$where .= "
				AND (RE.DEL_FLG = TRUE)
			";
			
			break;
	}
	
	$json_result = array(
		'total' => $db->count("PRODUCT_REORDER RE",$where),
		'page' => $page
	);
	
	$select_product_reorder_sql = "
		SELECT
			RE.IDX							AS REORDER_IDX,
			(
				SELECT
					S_PI.IMG_LOCATION
				FROM
					PRODUCT_IMG S_PI
				WHERE
					S_PI.PRODUCT_IDX = PR.IDX AND
					S_PI.DEL_FLG = FALSE AND
					S_PI.IMG_TYPE = 'P' AND
					S_PI.IMG_SIZE = 'S'
				ORDER BY
					S_PI.IDX ASC
				LIMIT
					0,1
			)	 							AS IMG_LOCATION,
			RE.PRODUCT_TYPE					AS PRODUCT_TYPE,
			RE.PARENT_IDX					AS PARENT_IDX,
			RE.PRODUCT_NAME					AS PRODUCT_NAME,
			PR.COLOR						AS COLOR,
			PR.COLOR_RGB					AS COLOR_RGB,
			IFNULL(RE.OPTION_NAME,'Set')	AS OPTION_NAME,
			PR.SALES_PRICE_".$country."		AS SALES_PRICE,
			DATE_FORMAT(
				RE.UPDATE_DATE,
				'%Y.%m.%d'
			)								AS UPDATE_DATE
		FROM
			PRODUCT_REORDER RE
			LEFT JOIN SHOP_PRODUCT PR ON
			RE.PRODUCT_IDX = PR.IDX
		WHERE
			".$where."
		ORDER BY
			RE.IDX DESC
	";
	
	if($rows != null && $page != null){
		$limit_start = (intval($page)-1)*$rows;
		$select_product_reorder_sql .= " LIMIT ".$limit_start.",".$rows;
	}

	$db->query($select_product_reorder_sql);

	foreach($db->fetch() as $data){
		$reorder_idx = $data['REORDER_IDX'];
		$product_type = $data['PRODUCT_TYPE'];
		
		$set_product_info = array();
		if (!empty($reorder_idx) && $product_type == "S") {
			$select_set_product_sql = "
				SELECT
					RE.PARENT_IDX					AS PARENT_IDX,
					(
						SELECT
							S_PI.IMG_LOCATION
						FROM
							PRODUCT_IMG S_PI
						WHERE
							S_PI.PRODUCT_IDX = PR.IDX AND
							S_PI.DEL_FLG = FALSE AND
							S_PI.IMG_TYPE = 'P' AND
							S_PI.IMG_SIZE = 'S'
						ORDER BY
							S_PI.IDX ASC
						LIMIT
							0,1
					)	 							AS IMG_LOCATION,
					RE.PRODUCT_NAME					AS PRODUCT_NAME,
					PR.COLOR						AS COLOR,
					PR.COLOR_RGB					AS COLOR_RGB,
					DATE_FORMAT(
						RE.UPDATE_DATE,
						'%Y.%m.%d'
					)								AS UPDATE_DATE
				FROM
					PRODUCT_REORDER RE
					LEFT JOIN SHOP_PRODUCT PR ON
					RE.PRODUCT_IDX = PR.IDX
				WHERE
					RE.PARENT_IDX = ".$reorder_idx."
			";
			
			$db->query($select_set_product_sql);
			
			foreach($db->fetch() as $set_data) {
				$set_product_info[] = array(
					'parent_idx'		=>$set_data['PARENT_IDX'],
					'img_location'		=>$set_data['IMG_LOCATION'],
					'product_name'		=>$set_data['PRODUCT_NAME'],
					'color'				=>$set_data['COLOR'],
					'color_rgb'			=>$set_data['COLOR_RGB'],
					'update_date'		=>$set_data['UPDATE_DATE']
				);
			}
		}
		
		$json_result['data'][] = array(
			'reorder_idx'		=>$data['REORDER_IDX'],
			'product_type'		=>$data['PRODUCT_TYPE'],
			'img_location'		=>$data['IMG_LOCATION'],
			'product_name'		=>$data['PRODUCT_NAME'],
			'color'				=>$data['COLOR'],
			'color_rgb'			=>$data['COLOR_RGB'],
			'option_name'		=>$data['OPTION_NAME'],
			'sales_price_kr'	=>number_format($data['SALES_PRICE']),
			'update_date'		=>$data['UPDATE_DATE'],
			
			'set_product_info'	=>$set_product_info
		);
	}
}
