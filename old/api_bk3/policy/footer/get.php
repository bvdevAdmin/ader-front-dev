<?php
/*
 +=============================================================================
 | 
 | Footer 정보
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
} else {
	$country = $_POST['country'];
}

if (isset($country)) {
	$select_policy_sql = "
		SELECT 
			PI.DEVICE_TYPE		AS DEVICE_TYPE,
			PI.POLICY_TYPE		AS POLICY_TYPE,
			PI.POLICY_TXT		AS POLICY_TXT
		FROM 
			POLICY_INFO PI
		WHERE 
			PI.COUNTRY = '".$country."' AND
			(
				PI.POLICY_TYPE = 'ABT' OR 
				PI.POLICY_TYPE = 'INF'
			)
	";
	
	$db->query($select_policy_sql);

	foreach($db->fetch() as $policy_data) {
		$json_result['data'][] = array(
			'device_type'		=>$policy_data['DEVICE_TYPE'],
			'policy_type'		=>$policy_data['POLICY_TYPE'],
			'policy_txt'		=>$policy_data['POLICY_TXT']
		);
	}
}

?>