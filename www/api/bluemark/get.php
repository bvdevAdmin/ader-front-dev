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

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

if (isset($_SERVER['HTTP_COUNTRY']) && $member_idx > 0 && isset($bluemark_idx)) {
	$cnt_bluemark = $db->count("BLUEMARK_INFO","IDX = ? AND MEMBER_IDX = ?",array($bluemark_idx,$member_idx));	
	if ($cnt_bluemark > 0) {
		$select_bluemark_info_sql = "
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
				)					AS SERIAL_CODE,
				BL.PURCHASE_MALL	AS PURCHASE_MALL
			FROM
				BLUEMARK_INFO BI
				
				LEFT JOIN BLUEMARK_LOG BL ON
				BI.IDX = BL.BLUEMARK_IDX
				
				LEFT JOIN SHOP_PRODUCT PR ON
				BI.PRODUCT_IDX = PR.IDX
			WHERE
				BI.IDX = ? AND
				BI.COUNTRY = ? AND
				BI.MEMBER_IDX = ? AND
				BI.DEL_FLG = FALSE AND
				BL.ACTIVE_FLG = TRUE
		";
		
		$db->query($select_bluemark_info_sql,array($bluemark_idx,$_SERVER['HTTP_COUNTRY'],$member_idx));
		
		foreach($db->fetch() as $data) {
			$json_result['data'] = array(
				'bluemark_idx'		=>$data['BLUEMARK_IDX'],
				'product_name'		=>$data['PRODUCT_NAME'],
				'color'				=>$data['COLOR'],
				'member_id'			=>$data['MEMBER_ID'],
				'reg_date'			=>$data['REG_DATE'],
				'serial_code'		=>$data['SERIAL_CODE'],
				'purchase_mall'		=>$data['PURCHASE_MALL']
			);
		}
	} else {
		$json_result['code'] = 401;
		$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0036',array());
		
		echo json_encode($json_result);
		exit;
	}
} else {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $_SERVER['HTTP_COUNTRY'], 'MSG_B_ERR_0018', array());
	
	echo json_encode($json_result);
	exit;
}

?>