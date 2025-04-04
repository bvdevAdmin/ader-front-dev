<?php
/*
 +=============================================================================
 | 
 | 바우처 등록 // '/var/www/www/api/mypage/voucher/issue/add.php'
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2022.01.10
 | 최종 수정    : 양한빈
 | 최종 수정일	: 2024.05.07
 | 버전		: 1.0
 | 설명		: 
 |            
 | 
 +=============================================================================
*/

if (isset($_SERVER['HTTP_COUNTRY']) && isset($_SESSION['MEMBER_IDX'])) {
	if (isset($voucher_issue_code)) {
		/* 1. 바우처 존재 여부 체크 */
		$cnt_voucher = $db->count(
			"
				VOUCHER_ISSUE VI
				
				LEFT JOIN VOUCHER_MST VM ON
				VI.VOUCHER_IDX = VM.IDX
			","
				VM.IDX IS NOT NULL AND
				VM.VOUCHER_TYPE			= 'OFF' AND
				VI.COUNTRY				= ? AND
				VI.MEMBER_IDX			= 0 AND
				VI.VOUCHER_ISSUE_CODE	= ?
			",
			array($_SERVER['HTTP_COUNTRY'],$voucher_issue_code)
		);
		
		/* 2. 동일 바우처 중복지급 여부 체크 */
		if ($cnt_voucher > 0) {
			$select_verify_sql = "
				SELECT 
					VI.IDX					AS ISSUE_IDX,
					VI.MEMBER_IDX			AS MEMBER_IDX,
					VM.MEMBER_LEVEL			AS MEMBER_LEVEL,
					VI.VOUCHER_ADD_DATE		AS VOUCHER_ADD_DATE,
					CASE
						WHEN 
							NOW() BETWEEN VM.VOUCHER_START_DATE AND VM.VOUCHER_END_DATE
							THEN
								TRUE
						ELSE
							FALSE
					END						AS USABLE_FLG
				FROM
					VOUCHER_ISSUE VI
					
					LEFT JOIN VOUCHER_MST VM ON
					VI.VOUCHER_IDX = VM.IDX
				WHERE
					VI.VOUCHER_ISSUE_CODE = ?
			";
			
			$db->query($select_verify_sql,array($voucher_issue_code));
			
			$error_msg = null;
			
			foreach($db->fetch() as $data){
				if($data['VOUCHER_ADD_DATE'] != NULL || $data['MEMBER_IDX'] > 0) {
					$json_result['code'] = 301;
					$json_result['msg'] = getMsgToMsgCode($db, $_SERVER['HTTP_COUNTRY'], 'MSG_B_ERR_0060', array());
					
					echo json_encode($json_result);
					exit;
				}
				
				if ($data['USABLE_FLG'] == FALSE) {
					$json_result['code'] = 301;
					$json_result['msg'] = getMsgToMsgCode($db, $_SERVER['HTTP_COUNTRY'], 'MSG_B_ERR_0021', array());
					
					echo json_encode($json_result);
					exit;
				}
				
				$update_voucher_issue_sql = "
					UPDATE 
						VOUCHER_ISSUE VI, 
						VOUCHER_MST VM
					SET       
						VI.MEMBER_IDX			= ?,
						VI.MEMBER_ID			= ?,
						VI.VOUCHER_ADD_DATE		= NOW(),
						VI.USABLE_START_DATE	= 
						CASE
							WHEN 
								VM.VOUCHER_DATE_TYPE = 'F' 
								THEN 
									VM.VOUCHER_START_DATE
							WHEN 
								VM.VOUCHER_DATE_TYPE = 'P' 
								THEN 
									NOW() 
						END,
						VI.USABLE_END_DATE		= 
						CASE
							WHEN 
								VM.VOUCHER_DATE_TYPE = 'F' 
								THEN 
									VM.VOUCHER_END_DATE
							WHEN 
								VM.VOUCHER_DATE_TYPE = 'P' 
								THEN 
									NOW() + INTERVAL VM.VOUCHER_DATE_PARAM DAY  
						END,
						VI.UPDATE_DATE			= NOW(),
						VI.UPDATER				= ?
					WHERE  
						VI.VOUCHER_IDX = VM.IDX AND
						VI.VOUCHER_ISSUE_CODE = ?
				";
				
				$db->query($update_voucher_issue_sql,array($_SESSION['MEMBER_IDX'],$_SESSION['MEMBER_IDX'],$_SESSION['MEMBER_IDX'],$voucher_issue_code));
				
				$json_result['data'] = array(
					'update_cnt'		=>$db->affectedRows()
				);
			}
		} else {
			$json_result['code'] = 301;
			$json_result['msg'] = getMsgToMsgCode($db, $_SERVER['HTTP_COUNTRY'], 'MSG_B_ERR_0078', array());
			
			echo json_encode($json_result);
			exit;
		}
	}
} else {
$json_result['code'] = 401;
$json_result['msg'] = getMsgToMsgCode($db, $_SERVER['HTTP_COUNTRY'], 'MSG_B_ERR_0018', array());

echo json_encode($json_result);
exit;
}

?>