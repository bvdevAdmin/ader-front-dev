<?php
/*
 +=============================================================================
 | 
 | 법적 고지사항
 | -------
 |
 | 최초 작성	: 윤재은
 | 최초 작성일	: 2023.02.28
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 |						
 | 
 +=============================================================================
*/

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
} else if (isset($_SERVER['HTTP_COUNTRY'])) {
	$country = $_SERVER['HTTP_COUNTRY'];
}

if (isset($country)) {
	$select_policy_info_sql = "
		SELECT 
			PI.POLICY_TYPE		AS POLICY_TYPE,
			PI.POLICY_TXT 		AS POLICY_TXT
		FROM 
			POLICY_INFO PI
		WHERE 
			PI.COUNTRY = ? AND 
			PI.POLICY_TYPE = 'TRM'
	";

	$db->query($select_policy_info_sql,array($country));
	
	foreach($db->fetch() as $data) {
		$json_result['data'] = array(
			'policy_type'		=>$data['POLICY_TYPE'],
			'policy_txt'		=>$data['POLICY_TXT']
		);
	}
}

?>