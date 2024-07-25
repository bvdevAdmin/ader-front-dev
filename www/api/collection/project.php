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

if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
} 
else if ($_SERVER['HTTP_COUNTRY']) {
	$country = $_SERVER['HTTP_COUNTRY'];
}

if ($db->count('COLLECTION_PROJECT','COUNTRY = ?',array($country)) > 0) {
	$db->query('
		SELECT
			CP.IDX					AS PROJECT_IDX,
			CP.PROJECT_NAME			AS PROJECT_NAME,
			CP.PROJECT_DESC			AS PROJECT_DESC,
			CP.PROJECT_TITLE		AS PROJECT_TITLE,
			CP.THUMB_LOCATION		AS THUMB_LOCATION
		FROM
			COLLECTION_PROJECT CP
		WHERE
			CP.COUNTRY = ?
		AND
			DEL_FLG = FALSE
		ORDER BY
			CP.DISPLAY_NUM DESC
	',array($country));
	
	foreach($db->fetch() as $project_data) {
		$json_result['data'][] = array(
			'project_idx'		=>$project_data['PROJECT_IDX'],
			'project_name'		=>$project_data['PROJECT_NAME'],
			'project_desc'		=>$project_data['PROJECT_DESC'],
			'project_title'		=>$project_data['PROJECT_TITLE'],
			'thumb_location'	=>$project_data['THUMB_LOCATION']
		);
	}
} 
else {
    $code = 301;
    $msg = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0094', array());
}
