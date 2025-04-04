<?php
/*
 +=============================================================================
 | 
 | 퀵뷰 - 위시리스트
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

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

if ($member_idx > 0) {
	$select_whish_sql = "
		SELECT
			PR.IDX				AS PRODUCT_IDX,
			(
				SELECT
					S_PI.IMG_LOCATION
				FROM
					PRODUCT_IMG S_PI
				WHERE
					S_PI.PRODUCT_IDX = PR.IDX AND
					S_PI.IMG_TYPE = 'P' AND
					S_PI.IMG_SIZE = 'S'
				ORDER BY
					S_PI.IDX ASC
				LIMIT
					0,1
			)					AS IMG_LOCATION,
			PR.PRODUCT_NAME		AS PRODUCT_NAME
		FROM
			WHISH_LIST WL
			LEFT JOIN SHOP_PRODUCT PR ON
			WL.PRODUCT_IDX = PR.IDX
		WHERE
			WL.MEMBER_IDX = ".$member_idx." AND
			WL.DEL_FLG = FALSE AND
			PR.DEL_FLG = FALSE
		ORDER BY
			WL.IDX DESC
	";
	
	$db->query($select_whish_sql);
	
	foreach($db->fetch() as $whish_data) {
		$json_result['data'][] = array(
			'product_link'		=>"/product/detail?product_idx=".$whish_data['PRODUCT_IDX'],
			'img_location'		=>$whish_data['IMG_LOCATION'],
			'product_name'		=>$whish_data['PRODUCT_NAME']
		);
	}
}

?>