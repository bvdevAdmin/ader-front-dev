<?php
/*
 +=============================================================================
 | 
 | 게시물_룩북 - 룩북 프로젝트 리스트 조회
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
include_once("/var/www/www/api/common.php");
$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
} else if ($_POST['country']) {
	$country = $_POST['country'];
}

/*$project_idx = 0;
if (isset($_POST['project_idx'])) {
	$project_idx = $_POST['project_idx'];
}

if ($project_idx == 0) {
	$json_result['code'] = 301;
	$json_result['msg'] = "부적절한 경로로 접근하셨습니다. 조회하려는 게시물을 확인해주세요.";
	
	echo json_encode($json_result);
	exit;
}*/

$page_cnt = $db->count("COLLECTION_PROJECT","COUNTRY = '".$country."'");

if ($page_cnt > 0) {
	$select_collection_project_sql = "
		SELECT
			CP.IDX					AS PROJECT_IDX,
			CP.PROJECT_NAME			AS PROJECT_NAME,
			CP.PROJECT_DESC			AS PROJECT_DESC,
			CP.PROJECT_TITLE		AS PROJECT_TITLE,
			CP.THUMB_LOCATION		AS THUMB_LOCATION
		FROM
			COLLECTION_PROJECT CP
		WHERE
			CP.COUNTRY = '".$country."'
		AND
			DEL_FLG = FALSE
		ORDER BY
			CP.DISPLAY_NUM DESC
	";
	
	$db->query($select_collection_project_sql);
	
	foreach($db->fetch() as $project_data) {
		$json_result['data'][] = array(
			'project_idx'		=>$project_data['PROJECT_IDX'],
			'project_name'		=>$project_data['PROJECT_NAME'],
			'project_desc'		=>$project_data['PROJECT_DESC'],
			'project_title'		=>$project_data['PROJECT_TITLE'],
			'thumb_location'	=>$project_data['THUMB_LOCATION']
		);
	}
} else {
	$json_result['code'] = 301;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0094', array());
	
	echo json_encode($json_result);
	exit;
}

?>