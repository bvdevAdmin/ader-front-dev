<?php
/*
 +=============================================================================
 | 
 | 문의 카테고리 및 템플릿 조회
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2023.04.23
 | 최종 수정일	: 
 | 버전		: 1.1
 | 설명		: 
 | 
 +=============================================================================
*/

if (isset($_SERVER['HTTP_COUNTRY']) && isset($_SESSION['MEMBER_IDX'])) {
	$select_question_category_sql = "
		SELECT
			QC.IDX					AS CATEGORY_IDX,
			QC.CATEGORY_NAME_KR		AS CATEGORY_NAME_KR,
			QC.CATEGORY_NAME_EN		AS CATEGORY_NAME_EN,
			
			QT.TEMPLATE_KR			AS TEMPLATE_KR,
			QT.TEMPLATE_EN			AS TEMPLATE_EN
		FROM
			QUESTION_CATEGORY QC
			
			LEFT JOIN QUESTION_TEMPLATE QT ON
			QC.IDX = QT.CATEGORY_IDX
		WHERE
			QC.DEL_FLG = FALSE AND
			QT.DEL_FLG = FALSE
		ORDER BY
			QC.DISPLAY_NUM ASC
	";
	
	$db->query($select_question_category_sql);
	
	foreach($db->fetch() as $data) {
		$json_result['data'][] = array(
			'category_idx'		=>$data['CATEGORY_IDX'],
			'category_name'		=>$data['CATEGORY_NAME_'.$country],
			'template'			=>$data['TEMPLATE_'.$country]
		);
	}
} else {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0018',array());
	
	echo json_encode($json_result);
	exit;
}

?>