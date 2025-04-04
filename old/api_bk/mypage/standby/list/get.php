<?php
/*
 +=============================================================================
 | 
 | 마이페이지_스탠바이 - 스탠바이 리스트 조회
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2023.01.15
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/
include_once("/var/www/www/api/common/common.php");
$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$status_code = null;
if (isset($_POST['status_code'])) {
	$status_code = $_POST['status_code'];
}

if ($country == null || $member_idx == 0) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	
	return $json_result;
}

if($country != null && $member_idx > 0){
	$select_standby_sql = "
		SELECT
			PS.IDX					AS STANDBY_IDX,
			PS.THUMBNAIL_LOCATION	AS THUMBNAIL_LOCATION,
			PS.TITLE				AS TITLE,
			DATE_FORMAT(
				PS.ENTRY_START_DATE,'%Y.%m.%d %H:%i'
			)						AS ENTRY_START_DATE,
			DATE_FORMAT(
				PS.ENTRY_END_DATE,'%Y.%m.%d %H:%i'
			)						AS ENTRY_END_DATE,
			CASE
				WHEN
					PS.ENTRY_START_DATE > NOW()
					THEN 
						'Comming soon'
				WHEN
					PS.ENTRY_END_DATE < NOW()
					THEN
						'종료'
				ELSE
					'진행 중'
			END						AS ENTRY_STATUS
		FROM
			PAGE_STANDBY PS
		WHERE
			PS.COUNTRY = '".$country."' AND
			PS.DISPLAY_FLG = TRUE AND
			PS.DEL_FLG = FALSE
		ORDER BY
			PS.IDX DESC
	";
	
	$db->query($select_standby_sql);
	
	foreach($db->fetch() as $standby_data) {
		$now = strtotime(date('Y-m-d H:i:s'));
		
		$entry_status = "";
		$entry_start_date = $standby_data['ENTRY_START_DATE'];
		$entry_end_date = $standby_data['ENTRY_END_DATE'];
		
		if (strtotime($entry_start_date) >= $now) {
			$entry_status = "Coming soon";
		} else if (strtotime($entry_end_date) < $now) {
			$entry_status = "종료";
		} else if (strtotime($entry_start_date) <= $now && strtotime($entry_end_date) >= $now) {
			$entry_status = "진행 중";
		}
		
		$json_result['data'][] = array(
			'standby_idx'			=>$standby_data['STANDBY_IDX'],
			'thumbnail_location'	=>$standby_data['THUMBNAIL_LOCATION'],
			'title'					=>$standby_data['TITLE'],
			'entry_status'			=>$standby_data['ENTRY_STATUS'],
			'entry_start_date'		=>$standby_data['ENTRY_START_DATE'],
			'entry_end_date'		=>$standby_data['ENTRY_END_DATE']
		);
	}
}

?>