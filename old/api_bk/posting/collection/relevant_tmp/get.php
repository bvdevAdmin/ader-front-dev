<?php
/*
 +=============================================================================
 | 
 | 게시물_룩북 - 룩북 이미지 개별 조회
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2023.02.10
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

$c_product_idx = 0;
if (isset($_POST['c_product_idx'])) {
	$c_product_idx = $_POST['c_product_idx'];
}

if ($c_product_idx == 0) {
	$json_result['code'] = 301;
	$json_result['msg'] = "부적절한 경로로 접근하셨습니다. 조회하려는 프로젝트를 확인해주세요.";
	
	return $json_result;
}

if ($c_product_idx > 0) {
	$select_relevant_product_sql = "
		SELECT
			PR.IDX				AS PRODUCT_IDX,
			PR.PRODUCT_NAME		AS PRODUCT_NAME,
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
			RP.SOLD_OUT_FLG
		FROM
			COLLECTION_RELEVANT_PRODUCT RP
			LEFT JOIN SHOP_PRODUCT PR ON
			RP.PRODUCT_IDX = PR.IDX
		WHERE
			RP.C_PRODUCT_IDX = ".$c_product_idx." AND
			RP.DISPLAY_FLG = TRUE
		ORDER BY
			RP.DISPLAY_NUM
	";
	
	$db->query($select_relevant_product_sql);
	
	foreach($db->fetch() as $relevant_data) {
		$whish_flg = false;
		if($member_idx > 0){
			$whish_count = $db->count("WHISH_LIST","MEMBER_IDX = ".$member_idx." AND PRODUCT_IDX = ".$relevant_data['PRODUCT_IDX']." AND DEL_FLG = FALSE");
			$whish_flg = $whish_count>0?true:false;
		}
		$json_result['data'][] = array(
			'product_idx'		=>$relevant_data['PRODUCT_IDX'],
			'product_name'		=>$relevant_data['PRODUCT_NAME'],
			'img_location'		=>$relevant_data['IMG_LOCATION'],
			'sold_out_flg'		=>$relevant_data['SOLD_OUT_FLG'],
			'whish_flg'			=>$whish_flg
		);
	}
}

?>