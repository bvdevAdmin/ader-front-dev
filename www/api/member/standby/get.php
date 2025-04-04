<?php
/*
 +=============================================================================
 | 
 | 마이페이지_스탠바이 - 스탠바이 리스트 조회 // '/var/www/www/api/mypage/standby/list/get.php'
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2023.01.15
 | 최종 수정    : 양한빈
 | 최종 수정일	: 2024.05.07
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

if (isset($_SERVER['HTTP_COUNTRY']) && isset($_SESSION['MEMBER_IDX'])) {
	$standby_page	= getStandby_page($db);
	$standby_entry	= getStandby_entry($db);
	
	$json_result['data'] = array(
		'standby_page'		=>$standby_page,
		'standby_entry'		=>$standby_entry
	);
} else {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0018',array());
	
	echo json_encode($json_result);
	exit;
}

function getStandby_page($db) {
	$standby_page = array();
	
	$select_standby_sql = "
		SELECT
			PS.IDX					AS STANDBY_IDX,
			PS.THUMB_LOCATION_W		AS THUMB_LOCATION_W,
			PS.THUMB_LOCATION_M		AS THUMB_LOCATION_M,
			PS.TITLE				AS STANDBY_TITLE,
			
			DATE_FORMAT(
				PS.ENTRY_START_DATE,'%Y-%m-%d %H:%i'
			)						AS ENTRY_START_DATE,
			DATE_FORMAT(
				PS.ENTRY_END_DATE,'%Y-%m-%d %H:%i'
			)						AS ENTRY_END_DATE,
			
			DATE_FORMAT(
				PS.ENTRY_START_DATE,'%Y.%m.%d %H:%i'
			)						AS TXT_ENTRY_START_DATE,
			DATE_FORMAT(
				PS.ENTRY_END_DATE,'%Y.%m.%d %H:%i'
			)						AS TXT_ENTRY_END_DATE,
			CASE
				WHEN
					NOW() <= PS.ENTRY_START_DATE
					THEN 'W'
				
				WHEN
					NOW() >= PS.ENTRY_END_DATE
					THEN 'E'
				
				WHEN
					NOW() BETWEEN ENTRY_START_DATE AND ENTRY_END_DATE
					THEN 'T'
			END                     AS ENTRY_STATUS
		FROM
			PAGE_STANDBY PS
		WHERE
			PS.COUNTRY = ? AND
			PS.DISPLAY_FLG = TRUE AND
			PS.DEL_FLG = FALSE
		ORDER BY
			PS.DISPLAY_NUM ASC
	";
	
	$db->query($select_standby_sql,array($_SERVER['HTTP_COUNTRY']));
	
	foreach($db->fetch() as $data) {
		$entry_status = "";
		
		$check_result = checkEntry_status($data['ENTRY_STATUS']);
		
		$standby_page[] = array(
			'standby_idx'			=>$data['STANDBY_IDX'],
			'thumb_location_W'		=>$data['THUMB_LOCATION_W'],
			'thumb_location_M'		=>$data['THUMB_LOCATION_M'],
			'title'					=>$data['STANDBY_TITLE'],
			'entry_status'			=>$data['ENTRY_STATUS'],
			'txt_entry'				=>$check_result['txt_entry'],
			'entry_start_date'		=>$data['TXT_ENTRY_START_DATE'],
			'entry_end_date'		=>$data['TXT_ENTRY_END_DATE']
		);
	}
	
	return $standby_page;
}

function getStandby_entry($db) {
	$standby_entry = array();
	
	$table = "
		ENTRY_STANDBY ES
		
		LEFT JOIN PAGE_STANDBY PS ON
		ES.STANDBY_IDX = PS.IDX
	";
	
	$select_entry_standby_sql = "
		SELECT
			PS.IDX					AS STANDBY_IDX,
			
			PS.THUMB_LOCATION_W		AS THUMB_LOCATION_W,
			PS.THUMB_LOCATION_M		AS THUMB_LOCATION_M,
			PS.TITLE				AS STANDBY_TITLE,
			
			DATE_FORMAT(
				PS.ENTRY_START_DATE,'%Y-%m-%d %H:%i'
			)						AS ENTRY_START_DATE,
			DATE_FORMAT(
				PS.ENTRY_END_DATE,'%Y-%m-%d %H:%i'
			)						AS ENTRY_END_DATE,
			
			DATE_FORMAT(
				PS.ENTRY_START_DATE,'%Y.%m.%d %H:%i'
			)						AS TXT_ENTRY_START_DATE,
			DATE_FORMAT(
				PS.ENTRY_END_DATE,'%Y.%m.%d %H:%i'
			)						AS TXT_ENTRY_END_DATE,
			CASE
				WHEN
					NOW() <= PS.ENTRY_START_DATE
					THEN 'W'
				
				WHEN
					NOW() >= PS.ENTRY_END_DATE
					THEN 'E'
				
				WHEN
					NOW() BETWEEN ENTRY_START_DATE AND ENTRY_END_DATE
					THEN 'T'
			END                     AS ENTRY_STATUS
		FROM
			".$table."
		WHERE
			ES.COUNTRY = ? AND
			ES.MEMBER_IDX = ? AND
			ES.DEL_FLG = FALSE
		ORDER BY 
			ES.CREATE_DATE DESC
		LIMIT
			0,10
	";
	
	$db->query($select_entry_standby_sql,array($_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX']));
	
	foreach($db->fetch() as $data) {
		$check_result = checkEntry_status($data['ENTRY_STATUS']);
		
		$standby_entry[] = array(
			'standby_idx'			=>$data['STANDBY_IDX'],
			'thumb_location_W'		=>$data['THUMB_LOCATION_W'],
			'thumb_location_M'		=>$data['THUMB_LOCATION_M'],
			'title'					=>$data['STANDBY_TITLE'],
			'entry_status'			=>$check_result['entry_status'],
			'txt_entry'				=>$check_result['txt_entry'],
			'entry_start_date'		=>$data['TXT_ENTRY_START_DATE'],
			'entry_end_date'		=>$data['TXT_ENTRY_END_DATE']
		);
	}
	
	return $standby_entry;
}

function checkEntry_status($entry_status) {
	$txt_entry = "";

	if ($_SERVER['HTTP_COUNTRY'] == "KR") {
		switch ($entry_status) {
			case "W" :
				$txt_entry = "Coming soon";
				
				break;
			
			case "T" :
				$txt_entry = "진행 중";
				
				break;
			
			case "E" :
				$txt_entry = "종료";
				
				break;
		}
	} else if ($_SERVER['HTTP_COUNTRY'] == "EN") {
		switch ($entry_status) {
			case "W" :
				$txt_entry = "Coming soon";
				
				break;
			
			case "T" :
				$txt_entry = "On going";
				
				break;
			
			case "E" :
				$txt_entry = "End";
				
				break;
		}
	}
	
	$check_result = array(
		'entry_status'		=>$entry_status,
		'txt_entry'			=>$txt_entry
	);
	
	return $check_result;
}

?>