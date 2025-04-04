<?php
/*
 +=============================================================================
 | 
 | A/S신청 리스트 조회
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
include_once("/var/www/www/api/common/common.php");
$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

if ($country != null) {
	$select_as_category_sql = "
		SELECT
			AC.IDX				AS CATEGORY_IDX,
			AC.TXT_CATEGORY		AS TXT_CATEGORY
		FROM
			AS_CATEGORY AC
		WHERE
			AC.COUNTRY = '".$country."'
	";
	
	$db->query($select_as_category_sql);
	
	foreach($db->fetch() as $category_data) {
		$json_result['data'][] = array(
			'category_idx'		=>$category_data['CATEGORY_IDX'],
			'txt_category'		=>$category_data['TXT_CATEGORY']
		);
	}
} else {
	$json_result['code'] = 301;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0029', array());
}

?>