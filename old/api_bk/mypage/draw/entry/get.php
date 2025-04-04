<?php
/*
 +=============================================================================
 | 
 | 마이페이지_드로우 - 응모한 드로우 리스트 정보 조회
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
$entry_status = null;
if (isset($_POST['entry_status'])) {
	$entry_status = $_POST['entry_status'];
}

if ($country == null || $member_idx == 0) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	
	return $json_result;
}
else{
	$entry_status_sql = "";
	if ($entry_status != null && $entry_status != "ALL") {
		switch ($entry_status) {
			//드로우 진행중
			case "ONG" :
				$entry_status_sql = "
					AND (
						(
							PD.ENTRY_START_DATE <= NOW() AND
							PD.ENTRY_END_DATE > NOW()
						) OR (
							PD.ANNOUNCE_DATE > NOW()
						)
					)
				";
				break;
			
			//드로우 당첨
			case "PRZ" :
				$entry_status_sql = "
					AND (
						PD.ANNOUNCE_DATE < NOW() AND
						ED.PRIZE_FLG = TRUE
					)
				";
				break;
			
			//드로우 미당첨
			case "NWN" :
				$entry_status_sql = "
					AND (
						PD.ANNOUNCE_DATE < NOW() AND
						ED.PRIZE_FLG = FALSE
					)
				";
				break;
		}
	}
	$select_entry_sql = "
		SELECT
			ED.IDX					AS ENTRY_IDX,
			ED.DRAW_IDX				AS DRAW_IDX,
			(
				SELECT
					S_PI.IMG_LOCATION
				FROM
					PRODUCT_IMG S_PI
				WHERE
					S_PI.IMG_TYPE = 'P' AND
					S_PI.IMG_SIZE = 'S'
				ORDER BY
					IDX ASC
				LIMIT
					0,1
			)						AS IMG_LOCATION,
			PD.PRODUCT_NAME			AS PRODUCT_NAME,
			PD.SALES_PRICE			AS SALES_PRICE,
			OM.COLOR				AS COLOR,
			OM.COLOR_RGB			AS COLOR_RGB,
			OO.OPTION_NAME			AS OPTION_NAME,
			DATE_FORMAT(
				PD.ENTRY_START_DATE,'%Y.%m.%d %H:%i'
			)						AS ENTRY_START_DATE,
			DATE_FORMAT(
				PD.ENTRY_END_DATE,'%Y.%m.%d %H:%i'
			)						AS ENTRY_END_DATE,
			DATE_FORMAT(
				PD.ANNOUNCE_DATE,'%Y.%m.%d %H:%i'
			)						AS ANNOUNCE_DATE,
			DATE_FORMAT(
				PD.PURCHASE_START_DATE,'%Y.%m.%d %H:%i'
			)						AS PURCHASE_START_DATE,
			DATE_FORMAT(
				PD.PURCHASE_END_DATE,'%Y.%m.%d %H:%i'
			)						AS PURCHASE_END_DATE,
			DATE_FORMAT(
				ED.CREATE_DATE,'%Y.%m.%d %H:%i'
			)						AS APPLY_DATE,
			PD.DISPLAY_FLG			AS DISPLAY_FLG,
			ED.PRIZE_FLG			AS PRIZE_FLG,
			ED.PURCHASE_FLG			AS PURCHASE_FLG,

			CASE
				WHEN
					PD.ENTRY_END_DATE < NOW() AND
					PD.ANNOUNCE_DATE > NOW()
					THEN 
						'당첨대기'
				WHEN
					PD.ENTRY_END_DATE < NOW() AND
					PD.ANNOUNCE_DATE <= NOW() AND
					ED.PRIZE_FLG = TRUE
					THEN
						'당첨'
				WHEN
					PD.ENTRY_END_DATE < NOW() AND
					PD.ANNOUNCE_DATE <= NOW() AND
					ED.PRIZE_FLG = FALSE
					THEN
						'미당첨'
				WHEN
					PD.ENTRY_END_DATE > NOW()
					THEN
						'진행 중' 
				ELSE
					NULL
			END						AS DRAW_STATUS,

			CASE
				WHEN
					PD.PURCHASE_END_DATE < NOW()
					THEN 
						'드로우 종료'
				WHEN
					PD.PURCHASE_END_DATE > NOW() AND
					ED.PRIZE_FLG = TRUE AND
					ED.PURCHASE_FLG = FALSE
					THEN
						'구매하기'
				WHEN
					PD.PURCHASE_END_DATE > NOW() AND
					ED.PRIZE_FLG = TRUE AND
					ED.PURCHASE_FLG = TRUE
					THEN
						'구매종료'
				WHEN
					PD.PURCHASE_END_DATE > NOW() AND
					ED.PRIZE_FLG = FALSE
					THEN '드로우 종료' 
				ELSE
					NULL
			END						AS PURCHASE_STATUS
			
		FROM
			PAGE_DRAW PD
			LEFT JOIN ENTRY_DRAW ED ON
			PD.IDX = ED.DRAW_IDX
			LEFT JOIN SHOP_PRODUCT PR ON
			ED.PRODUCT_IDX = PR.IDX
			LEFT JOIN ORDERSHEET_MST OM ON
			PR.ORDERSHEET_IDX = OM.IDX
			LEFT JOIN ORDERSHEET_OPTION OO ON
			ED.OPTION_IDX = OO.IDX
		WHERE
			ED.COUNTRY = '".$country."' AND
			ED.MEMBER_IDX = ".$member_idx."
			".$entry_status_sql."
	";
	
	$db->query($select_entry_sql);
	
	foreach($db->fetch() as $entry_data) {
		$json_result['data'][] = array(
			'entry_idx'				=>$entry_data['ENTRY_IDX'],
			'draw_idx'				=>$entry_data['DRAW_IDX'],
			'img_location'			=>$entry_data['IMG_LOCATION'],
			'product_name'			=>$entry_data['PRODUCT_NAME'],
			'sales_price'			=>number_format($entry_data['SALES_PRICE']),
			'color'					=>$entry_data['COLOR'],
			'color_rgb'				=>$entry_data['COLOR_RGB'],
			'option_name'			=>$entry_data['OPTION_NAME'],
			'display_flg'			=>$entry_data['DISPLAY_FLG'],
			'entry_start_date'		=>$entry_data['ENTRY_START_DATE'],
			'entry_end_date'		=>$entry_data['ENTRY_END_DATE'],
			'announce_date'			=>$entry_data['ANNOUNCE_DATE'],
			'purchase_start_date'	=>$entry_data['PURCHASE_START_DATE'],
			'purchase_end_date'		=>$entry_data['PURCHASE_END_DATE'],
			'apply_date'			=>$entry_data['APPLY_DATE'],
			'draw_status'			=>$entry_data['DRAW_STATUS'],
			'purchase_status'		=>$entry_data['PURCHASE_STATUS']
		);
	}
}
?>