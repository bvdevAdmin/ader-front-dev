<?php
/*
 +=============================================================================
 | 
 | 마이페이지 블루마크 - 블루마크 인증
 | -------
 |
 | 최초 작성	: 윤재은
 | 최초 작성일	: 2023.01.09
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/
include_once("/var/www/www/api/common.php");
$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$bluemark_idx = 0;
if (isset($_POST['bluemark_idx'])) {
	$bluemark_idx = $_POST['bluemark_idx'];
}

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}
else{
	if(isset($_POST['country'])){
        $country = $_POST['country'];
	}
}

if ($member_idx == 0) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	
	echo json_encode($json_result);
	exit;
}

if ($member_idx > 0 && $bluemark_idx > 0) {
	$bluemark_cnt = $db->count("BLUEMARK_INFO","IDX = ".$bluemark_idx." AND MEMBER_IDX = ".$member_idx);
	
	if ($bluemark_cnt > 0) {
		$select_bluemark_sql = "
			SELECT
				BI.IDX				AS BLUEMARK_IDX,
				PR.PRODUCT_NAME		AS PRODUCT_NAME,
				PR.COLOR			AS COLOR,
				REPLACE(
					BL.MEMBER_ID,
					SUBSTR(
						BI.MEMBER_ID,
						5,
						LENGTH(BI.MEMBER_ID)
					),
					'*******'
				)					AS MEMBER_ID,
				DATE_FORMAT(
					BL.REG_DATE,
					'%Y.%m.%d'
				)					AS REG_DATE,
				UPPER(
					BI.SERIAL_CODE
				)					AS SERIAL_CODE
			FROM
				BLUEMARK_INFO BI
				LEFT JOIN BLUEMARK_LOG BL ON
				BI.IDX = BL.BLUEMARK_IDX
				LEFT JOIN SHOP_PRODUCT PR ON
				BI.PRODUCT_IDX = PR.IDX
			WHERE
				BI.IDX = ".$bluemark_idx." AND
				BI.MEMBER_IDX = ".$member_idx." AND
				BI.DEL_FLG = FALSE AND
				BL.ACTIVE_FLG = TRUE
		";
		
		$db->query($select_bluemark_sql);
		
		foreach($db->fetch() as $bluemark_data) {
			$json_result['data'] = array(
				'bluemark_idx'	=>$bluemark_data['BLUEMARK_IDX'],
				'product_name'	=>$bluemark_data['PRODUCT_NAME'],
				'color'			=>$bluemark_data['COLOR'],
				'member_id'		=>$bluemark_data['MEMBER_ID'],
				'reg_date'	=>$bluemark_data['REG_DATE'],
				'serial_code'	=>$bluemark_data['SERIAL_CODE']
			);
		}
	} else {
		$json_result['code'] = 401;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0036', array());
		
		echo json_encode($json_result);
		exit;
	}
}
?>