<?php
/*
 +=============================================================================
 | 
 | 마이페이지_문의내역 - 문의내역 리스트 조회
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
include_once("/var/www/www/api/common.php");
$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}
else{
	if(isset($_POST['country'])){
        $country = $_POST['country'];
	}
}
$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

if ($country == null || $member_idx == 0) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	
	echo json_encode($json_result);
	exit;
}

$select_inquiry_list_sql = "
	SELECT
		PB.IDX				AS BOARD_IDX,
		PB.CATEGORY			AS BOARD_CATEGORY,
		PB.TITLE			AS BOARD_TITLE,
		PB.CONTENTS			AS BOARD_CONTENTS,
		PB.ANSWER_STATE		AS ANSWER_STATE,
		DATE_FORMAT(
			PB.CREATE_DATE,
			'%y.%m.%d'
		)					AS CREATE_DATE
	FROM
		PAGE_BOARD PB
	WHERE
		PB.BOARD_TYPE = 'ONE' AND
		PB.MEMBER_IDX = ".$member_idx." AND
		PB.DEL_FLG = FALSE
	ORDER BY
		PB.IDX DESC
";

$db->query($select_inquiry_list_sql);

foreach($db->fetch() as $data){
    $board_idx = $data['BOARD_IDX'];
	
	$board_reply_info = array();
	$board_img_info = array();
	
	if (!empty($board_idx)) {
		$select_board_reply_sql = "
			SELECT
				BR.CONTENTS
			FROM
				BOARD_REPLY BR
			WHERE
				BR.BOARD_IDX = ".$board_idx."
		";
		
		$db->query($select_board_reply_sql);
		
		foreach($db->fetch() as $reply_data){
			$board_reply_info[] = array(
				'contents' => $reply_data['CONTENTS']
			);
		}

		$select_board_img_sql = "
			SELECT
				BI.IMG_LOCATION		AS IMG_LOCATION
			FROM
				BOARD_IMAGE BI
			WHERE
				BOARD_IDX = ".$board_idx."
		";
		
		$db->query($select_board_img_sql);
		
		foreach($db->fetch() as $img_data){
			$board_img_info[] = array(
				'img_location' => $img_data['IMG_LOCATION']
			);
		}
	}
	
	$request_flg = false;
	if (count($board_reply_info) > 0) {
		$request_flg = true;
	}
	
	$json_result['data'][] = array(
		'board_idx'				=>$data['BOARD_IDX'],
		'board_category'		=>$data['BOARD_CATEGORY'],
		'txt_board_category'	=>setTxtCategory($data['BOARD_CATEGORY']),
		'board_title'			=>$data['BOARD_TITLE'],
		'board_contents'		=>$data['BOARD_CONTENTS'],
		'answer_state'			=>$data['ANSWER_STATE'],
		'create_date'			=>$data['CREATE_DATE'],
		
		'board_reply_info'		=>$board_reply_info,
		'board_img_info'		=>$board_img_info,
		
		'request_flg'			=>$request_flg
	);
}

function setTxtCategory($param) {
	$txt_category = "";
	
	switch ($param) {
		case 'CAR':
			$txt_category = '취소/환불';
			break;
		
		case 'OAP':
			$txt_category = '주문/결제';
			break;
		
		case 'FAD':
			$txt_category = '출고/배송';
			break;
		
		case 'RAE':
			$txt_category = '반품/교환';
			break;
		
		case 'AFS':
			$txt_category = 'A/S';
			break;
		
		case 'DAE':
			$txt_category = '배송/기타문의';
			break;
		
		case 'RST':
			$txt_category = '재입고';
			break;
		
		case 'PIQ':
			$txt_category = '제품문의';
			break;
		
		case 'BGP':
			$txt_category = '블루마크';
			break;
		
		case 'VUC':
			$txt_category = '바우처';
			break;
		
		case 'OSV':
			$txt_category = '기타서비스';
			break;
	}
	
	return $txt_category;
}