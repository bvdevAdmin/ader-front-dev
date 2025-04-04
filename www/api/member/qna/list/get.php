<?php
/*
 +=============================================================================
 | 
 | 1:1 문의내역 목록 조회
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2023.02.27
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

if (isset($_SERVER['HTTP_COUNTRY']) && isset($_SESSION['MEMBER_IDX'])) {
	$json_result = array(
        'total' => $db->count("BOARD_QUESTION","COUNTRY = ? AND MEMBER_IDX = ?",array($_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX'])),
        'page' => $page
    );

	if (!isset($page)) {
        $page = 1;
    }

    if (!isset($rows)) {
        $rows = 10;
    }
    
    $limit_start = (intval($page) - 1) * $rows;
	
	$selsct_question_board_sql = "
		SELECT
			BQ.IDX					AS BOARD_IDX,
			QC.CATEGORY_NAME_KR		AS CATEGORY_KR,
			QC.CATEGORY_NAME_EN		AS CATEGORY_EN,
			
			BQ.BOARD_TITLE			AS BOARD_TITLE,
			BQ.BOARD_CONTENTS		AS BOARD_CONTENTS,
			BQ.ANSWER_FLG			AS ANSWER_FLG,
			
			DATE_FORMAT(
				BQ.CREATE_DATE,
				'%Y.%m.%d %h:%i'
			)					AS CREATE_DATE
		FROM
			BOARD_QUESTION BQ

			LEFT JOIN QUESTION_CATEGORY QC ON
			BQ.CATEGORY_IDX = QC.IDX
		WHERE
			BQ.COUNTRY = ? AND 
			BQ.MEMBER_IDX = ? AND
			BQ.DEL_FLG = FALSE
		ORDER BY
			BQ.IDX DESC
		LIMIT
			?,?
	";

	$db->query($selsct_question_board_sql,array($_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX'],$limit_start,$rows));

	foreach($db->fetch() as $data) {
		$json_result['data'][] = array(
			'board_idx'				=>$data['BOARD_IDX'],
			'question_category'		=>$data['CATEGORY_'.$_SERVER['HTTP_COUNTRY']],

			'board_title'			=>$data['BOARD_TITLE'],

			'answer_flg'			=>$data['ANSWER_FLG'],
			'create_date'			=>$data['CREATE_DATE']
		);
	}
} else {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0018',array());
	
	echo json_encode($json_result);
	exit;
}
