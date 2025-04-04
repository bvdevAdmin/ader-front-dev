<?php
/*
 +=============================================================================
 | 
 | 마이페이지 마일리지 현황정보 취득 // member/mileage/get
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2023.01.11
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

if (isset($_SERVER['HTTP_COUNTRY']) && isset($_SESSION['MEMBER_IDX'])) {
	$select_mileage_info_sql = "
		SELECT
			MI.MILEAGE_BALANCE			AS MILEAGE_BALANCE,
			MI.MILEAGE_USABLE_INC		AS MILEAGE_USABLE_INC,
			MI.MILEAGE_USABLE_DEC		AS MILEAGE_USABLE_DEC,
			MI.MILEAGE_UNUSABLE			AS MILEAGE_UNUSABLE
		FROM
			V_MILEAGE MI
			
			LEFT JOIN (
				SELECT
					S_MI.COUNTRY		AS COUNTRY,
					S_MI.MEMBER_IDX		AS MEMBER_IDX,
					SUM(
						S_MI.MILEAGE_USABLE_INC
					)					AS MILEAGE_USABLE_INC
				FROM
					MILEAGE_INFO S_MI
				WHERE
					S_MI.MILEAGE_USABLE_DATE > NOW()
				GROUP BY
					S_MI.COUNTRY,S_MI.MEMBER_IDX
			) AS J_MI ON
			MI.COUNTRY = J_MI.COUNTRY AND
			MI.MEMBER_IDX = J_MI.MEMBER_IDX
		WHERE
			MI.COUNTRY = ? AND
			MI.MEMBER_IDX = ?
	";
	
	$param_bind = array($_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX']);
	
	$db->query($select_mileage_info_sql,$param_bind);

	foreach($db->fetch() as $data){
		$json_result['data'] = array(
			'mileage_balance'		=>number_format($data['MILEAGE_BALANCE']),
			'mileage_inc'			=>number_format($data['MILEAGE_USABLE_INC']),
			'mileage_dec'			=>number_format($data['MILEAGE_USABLE_DEC']),
			'mileage_unusable'		=>number_format($data['MILEAGE_UNUSABLE']),
		);
	}
} else {
	$json_result['code'] = 401;
    $json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0018',array());
	
	echo json_encode($json_result);
	exit;
}

?>