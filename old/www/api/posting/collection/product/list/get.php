<?php
/*
 +=============================================================================
 | 
 | 게시물_룩북 - 룩북 이미지 리스트 조회
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

include_once(dir_f_api."/common.php");

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
} else if ($_SERVER['HTTP_COUNTRY']) {
	$country = $_SERVER['HTTP_COUNTRY'];
}

$last_idx = 0;
if (isset($_POST['last_idx'])) {
	$last_idx = $_POST['last_idx'];
}

$first_index = intval($last_idx);
$last_index = 0;

if ($project_idx != null) {
	$select_collection_product_sql = "
		SELECT
			CP.IDX					AS C_PRODUCT_IDX,
			CP.RELEVANT_FLG			AS RELEVANT_FLG,
			J_CI.IMG_LOCATION		AS IMG_LOCATION
		FROM
			COLLECTION_PRODUCT CP
			
			LEFT JOIN (
				SELECT
					S_CI.C_PRODUCT_IDX	AS C_PRODUCT_IDX,
					S_CI.IMG_LOCATION	AS IMG_LOCATION
				FROM
					COLLECTION_IMG S_CI
				WHERE
					S_CI.IMG_SIZE = 'M' AND
					S_CI.DEL_FLG = FALSE
			) J_CI ON
			CP.IDX = J_CI.C_PRODUCT_IDX
		WHERE
			CP.PROJECT_IDX = ?
		ORDER BY
			CP.DISPLAY_NUM
	";
	
	if ($last_idx > 0) {
		$select_collection_product_sql .= " LIMIT ".$last_idx.",18 ";
	} else {
		$select_collection_product_sql .= " LIMIT 0,18 ";
	}
	
	$db->query($select_collection_product_sql,array($project_idx));
	
	$c_product_info = array();
	
	foreach($db->fetch() as $idx => $data) {
		$c_product_info[] = array(
			'c_product_idx'		=>$data['C_PRODUCT_IDX'],
			'relevant_flg'		=>$data['RELEVANT_FLG'],
			'img_location'		=>$data['IMG_LOCATION']
		);
		
		$last_index = $idx;
	}
	
	$last_index += $first_index;
	
	$json_result['data']		= $c_product_info;
	$json_result['first_index']	= $first_index;
	$json_result['last_index']	= $last_index;
} else {
	$json_result['code'] = 301;
	$json_result['msg'] = getMsgToMsgCode($db,$country,'MSG_B_ERR_0030', array());
	
	echo json_encode($json_result);
	exit;
}

?>