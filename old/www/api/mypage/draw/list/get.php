<?php
/*
 +=============================================================================
 | 
 | 마이페이지_드로우 - 응모한 드로우 리스트 조회
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
include_once(dir_f_api."/common.php");
$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
} else {
	if(isset($_POST['country'])){
        $country = $_POST['country'];
	}
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

if (!isset($country) || $member_idx == 0) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	
	echo json_encode($json_result);
	exit;
} else {
	$select_draw_sql = "
		SELECT
			PD.IDX					AS DRAW_IDX,
			(
				SELECT
					S_PI.IMG_LOCATION
				FROM
					PRODUCT_IMG S_PI
				WHERE
					S_PI.PRODUCT_IDX = PR.IDX AND
					S_PI.IMG_TYPE = 'P' AND
					S_PI.IMG_SIZE = 'S'
				LIMIT
					0,1
			)						AS IMG_LOCATION,
			PR.PRODUCT_NAME			AS PRODUCT_NAME,
			OM.COLOR				AS COLOR,
			OM.COLOR_RGB			AS COLOR_RGB,
			DATE_FORMAT(
				PD.ENTRY_START_DATE,'%Y.%m.%d %H:%i'
			)						AS ENTRY_START_DATE,
			DATE_FORMAT(
				PD.ENTRY_END_DATE,'%Y.%m.%d %H:%i'
			)						AS ENTRY_END_DATE,
			CASE
				WHEN
					PD.ENTRY_START_DATE > NOW()
					THEN 
						'Comming soon'
				WHEN
					PD.ENTRY_END_DATE < NOW()
					THEN
						'종료'
				ELSE
					'진행 중'
			END						AS ENTRY_STATUS
		FROM
			PAGE_DRAW PD
			LEFT JOIN SHOP_PRODUCT PR ON
			PD.PRODUCT_IDX = PR.IDX
			LEFT JOIN ORDERSHEET_MST OM ON
			PR.ORDERSHEET_IDX = OM.IDX
		WHERE
			PD.COUNTRY = '".$country."' AND
			PD.DISPLAY_FLG = TRUE AND
			PD.DEL_FLG = FALSE
		ORDER BY
			PD.IDX DESC
	";
	
	$db->query($select_draw_sql);
	
	foreach($db->fetch() as $draw_data) {
		$now = strtotime(date('Y-m-d H:i:s'));
		
		$entry_status = "";
		$entry_start_date = $draw_data['ENTRY_START_DATE'];
		$entry_end_date = $draw_data['ENTRY_END_DATE'];
		
		if (strtotime($entry_start_date) >= $now) {
			$entry_status = "Coming soon";
		} else if (strtotime($entry_end_date) < $now) {
			$entry_status = "종료";
		} else if (strtotime($entry_start_date) <= $now && strtotime($entry_end_date) >= $now) {
			$entry_status = "진행 중";
		}
		
		$json_result['data'][] = array(
			'draw_idx'				=>$draw_data['DRAW_IDX'],
			'img_location'			=>$draw_data['IMG_LOCATION'],
			'product_name'			=>$draw_data['PRODUCT_NAME'],
			'color'					=>$draw_data['COLOR'],
			'color_rgb'				=>$draw_data['COLOR_RGB'],
			'entry_status'			=>$draw_data['ENTRY_STATUS'],
			'entry_start_date'		=>$draw_data['ENTRY_START_DATE'],
			'entry_end_date'		=>$draw_data['ENTRY_END_DATE']
		);
	}
}

?>