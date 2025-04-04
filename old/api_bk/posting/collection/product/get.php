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

$project_idx = 0;
if (isset($_POST['project_idx'])) {
	$project_idx = $_POST['project_idx'];
}

if ($project_idx == 0) {
	$json_result['code'] = 301;
	$json_result['msg'] = "부적절한 경로로 접근하셨습니다. 조회하려는 프로젝트의 이미지를 확인해주세요.";
	
	return $json_result;
}

if ($project_idx > 0) {
	$select_collection_product_sql = "
		SELECT
			CP.IDX				AS C_PRODUCT_IDX,
			CI.IMG_URL			AS IMG_URL,
			CP.RELEVANT_FLG		AS RELEVANT_FLG
		FROM
			COLLECTION_PRODUCT CP
			LEFT JOIN COLLECTION_IMG CI ON
			CP.IDX = CI.C_PRODUCT_IDX
		WHERE
			CP.PROJECT_IDX = ".$project_idx."
		GROUP BY
			CP.DISPLAY_NUM
	";

	$db->query($select_collection_product_sql);
	
	$c_product_info = array();
	foreach($db->fetch() as $c_product_data) {
		$json_result['data'][] = array(
			'c_product_idx'		=>$c_product_data['C_PRODUCT_IDX'],
			'img_url'			=>$c_product_data['IMG_URL'],
			'relevant_flg'		=>$c_product_data['RELEVANT_FLG']
		);
	}
}

?>