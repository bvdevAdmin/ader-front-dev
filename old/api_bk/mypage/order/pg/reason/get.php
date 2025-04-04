<?php
/*
 +=============================================================================
 | 
 | 마이페이지_취소/교환/환불 상품 리스트 조회
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2023.04.10
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

$order_status = null;
if (isset($_POST['order_status'])) {
	$order_status = $_POST['order_status'];
}

$depth1_idx = 0;
if (isset($_POST['depth1_idx'])) {
	$depth1_idx = $_POST['depth1_idx'];
}

if ($country != null && $order_status != null) {
	$reason_depth1 = array();
	$reason_depth2 = array();
	
	if ($depth1_idx == 0) {
		$select_reason_depth1_sql = "
			SELECT
				DP1.IDX				AS DEPTH1_IDX,
				DP1.REASON_TXT		AS REASON_TXT,
				DP1.PG_FLG			AS PG_FLG
			FROM
				REASON_DEPTH_1 DP1
			WHERE
				DP1.COUNTRY = '".$country."' AND
				DP1.REASON_TYPE = '".$order_status."' AND
				DP1.DEL_FLG = FALSE
			ORDER BY
				DP1.DISPLAY_NUM ASC
		";
		
		$db->query($select_reason_depth1_sql);
		
		foreach($db->fetch() as $depth1_data) {
			$reason_depth1[] = array(
				'depth1_idx'		=>$depth1_data['DEPTH1_IDX'],
				'reason_txt'		=>$depth1_data['REASON_TXT']
			);
		}
	}
	
	$where = "";
	if ($depth1_idx > 0) {
		$where = " DP2.DEPTH_1_IDX = ".$depth1_idx." AND ";
	} else {
		$where = " DP2.DEPTH_1_IDX = ".$reason_depth1[0]['depth1_idx']." AND ";
	}
	
	$select_reason_depth2_sql = "
		SELECT
			DP2.IDX					AS DEPTH2_IDX,
			DP2.REASON_TXT			AS REASON_TXT,
			DP2.PG_FLG				AS PG_FLG
		FROM
			REASON_DEPTH_2 DP2
		WHERE
			DP2.COUNTRY = '".$country."' AND
			".$where."
			DP2.REASON_TYPE = '".$order_status."' AND
			DP2.DEL_FLG = FALSE
		ORDER BY
			DP2.DISPLAY_NUM ASC
	";
	
	$db->query($select_reason_depth2_sql);
	
	foreach($db->fetch() as $depth2_data) {
		$reason_depth2[] = array(
			'depth2_idx'		=>$depth2_data['DEPTH2_IDX'],
			'reason_txt'		=>$depth2_data['REASON_TXT']
		);
	}
	
	$json_result['data'] = array(
		'reason_depth1'		=>$reason_depth1,
		'reason_depth2'		=>$reason_depth2
	);
}

?>