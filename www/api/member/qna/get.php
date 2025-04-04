<?php
/*
 +=============================================================================
 | 
 | 1:1 문의내역 개별 조회
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
	$cnt_qa = $db->count("BOARD_QUESTION","IDX = ? AND COUNTRY = ? AND MEMBER_IDX = ? AND DEL_FLG = FALSE",array($board_idx,$_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX']));
	if ($cnt_qa > 0) {
		$select_board_question_sql = "
			SELECT
				BQ.IDX					AS BOARD_IDX,
				BQ.CATEGORY_IDX			AS CATEGORY_IDX,
				QC.CATEGORY_NAME_KR		AS CATEGORY_KR,
				QC.CATEGORY_NAME_EN		AS CATEGORY_EN,
				BQ.BOARD_TITLE			AS BOARD_TITLE,
				BQ.BOARD_CONTENTS		AS BOARD_CONTENTS,
				BQ.ANSWER_FLG			AS ANSWER_FLG,
				DATE_FORMAT(
					BQ.CREATE_DATE,
					'%Y.%m.%d %h:%i'
				)						AS CREATE_DATE
			FROM
				BOARD_QUESTION BQ

				LEFT JOIN QUESTION_CATEGORY QC ON
				BQ.CATEGORY_IDX = QC.IDX
			WHERE
				BQ.IDX = ? AND
				BQ.COUNTRY = ? AND 
				BQ.MEMBER_IDX = ? AND
				BQ.DEL_FLG = FALSE
		";

		$db->query($select_board_question_sql,array($board_idx,$_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX']));

		foreach($db->fetch() as $data){
			$board_img		= array();
			$board_answer	= null;

			$cnt_img = $db->count("BOARD_IMAGE","BOARD_IDX = ? AND DEL_FLG = FALSE",array($board_idx));
			if ($cnt_img > 0) {
				$board_img = getBoard_img($db,$board_idx);
			}

			if ($data['ANSWER_FLG'] == true) {
				$board_answer = getBoard_answer($db,$board_idx);
			}
					
			$json_result['data'] = array(
				'board_idx'				=>$data['BOARD_IDX'],
				
				'category_idx'			=>$data['CATEGORY_IDX'],
				'question_category'		=>$data['CATEGORY_'.$_SERVER['HTTP_COUNTRY']],
				'question_title'		=>$data['BOARD_TITLE'],
				'question_contents'		=>$data['BOARD_CONTENTS'],

				'answer_flg'			=>$data['ANSWER_FLG'],
				'question_date'			=>$data['CREATE_DATE'],
				
				'board_img'				=>$board_img,
				'board_answer'			=>$board_answer
			);
		}
	} else {
		$json_result['code'] = 300;
		$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_F_ERR_0055',array());
		
		echo json_encode($json_result);
		exit;
	}
} else {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0018',array());
	
	echo json_encode($json_result);
	exit;
}

function getBoard_img($db,$board_idx) {
	$board_img = null;

	$select_board_img_sql = "
		SELECT
			BI.IDX				AS IMG_IDX,
			BI.IMG_LOCATION		AS IMG_LOCATION
		FROM
			BOARD_IMAGE BI
		WHERE
			BI.BOARD_IDX = ? AND
			BI.DEL_FLG = FALSE
		ORDER BY
			BI.IDX ASC
	";

	$db->query($select_board_img_sql,array($board_idx));

	foreach($db->fetch() as $data) {
		$board_img[] = array(
			'img_idx'			=>$data['IMG_IDX'],
			'img_location'		=>$data['IMG_LOCATION']
		);
	}

	return $board_img;
}

function getBoard_answer($db,$board_idx) {
	$board_answer = null;

	$select_board_answer_sql = "
		SELECT
			BA.BOARD_CONTENTS		AS BOARD_CONTENTS,
			DATE_FORMAT(
				BA.UPDATE_DATE,
				'%Y.%m.%d %h:%i'
			)						AS UPDATE_DATE
		FROM
			BOARD_ANSWER BA
		WHERE
			BA.QUESTION_IDX = ?
	";

	$db->query($select_board_answer_sql,array($board_idx));

	foreach($db->fetch() as $data) {
		$board_answer = array(
			'answer_contents'	=>$data['BOARD_CONTENTS'],
			'answer_date'		=>$data['UPDATE_DATE']
		);
	}

	return $board_answer;
}

?>